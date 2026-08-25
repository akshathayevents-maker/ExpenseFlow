<?php

use App\Models\EmployeeLeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveAllocationService;
use Carbon\Carbon;

function edType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true, 'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function edPolicy(User $user, LeaveType $type, User $admin, array $attrs = []): EmployeeLeavePolicy
{
    return EmployeeLeavePolicy::create(array_merge([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'annual_entitlement' => 12, 'allocation_mode' => 'yearly',
        'effective_from' => '2026-01-01', 'is_active' => true, 'created_by' => $admin->id,
    ], $attrs));
}

// A. Existing policy resolves for a date within its own year.
test('an existing policy resolves for a query date within its effective period', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = edType();
    $cl2026 = edPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);

    $result = EmployeeLeavePolicy::currentFor($user, $type, Carbon::parse('2026-06-01'));

    expect($result->id)->toBe($cl2026->id);
});

// B. A future-dated policy must not shadow the currently-active one.
test('a future-dated policy does not affect resolution before its own effective_from', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = edType();
    $cl2026 = edPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);
    edPolicy($user, $type, $admin, ['annual_entitlement' => 15, 'effective_from' => '2027-03-01']);

    $result = EmployeeLeavePolicy::currentFor($user, $type, Carbon::parse('2026-12-01'));

    expect($result->id)->toBe($cl2026->id);
    expect((float) $result->annual_entitlement)->toBe(12.0);
});

// C. Once the future policy's date arrives, it becomes current.
test('a future-dated policy becomes current once its effective_from date arrives', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = edType();
    edPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);
    $cl2027 = edPolicy($user, $type, $admin, ['annual_entitlement' => 15, 'effective_from' => '2027-03-01']);

    $result = EmployeeLeavePolicy::currentFor($user, $type, Carbon::parse('2027-03-01'));

    expect($result->id)->toBe($cl2027->id);
    expect((float) $result->annual_entitlement)->toBe(15.0);
});

// D. Historical query (between the two effective dates) still returns the old policy.
test('a historical query date still returns the policy that was effective then', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = edType();
    $cl2026 = edPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);
    edPolicy($user, $type, $admin, ['annual_entitlement' => 15, 'effective_from' => '2027-03-01']);

    $result = EmployeeLeavePolicy::currentFor($user, $type, Carbon::parse('2026-02-01'));

    expect($result->id)->toBe($cl2026->id);
});

// E. A future policy must not affect allocation generated for a date before its effective_from.
test('generateForUser ignores a future-dated policy when generating allocations for an earlier date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = edType();
    edPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);
    edPolicy($user, $type, $admin, ['annual_entitlement' => 15, 'effective_from' => '2027-03-01']);

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-06-01'));

    expect($created)->toHaveCount(1);
    expect((float) $created[0]->allocated_amount)->toBe(12.0); // the 2026 policy's amount, not the future 15
});

// F. Explicitly disabled policy (is_active=false) must not be usable/accrued,
// independent of its effective_from being on/before the query date.
test('a policy with is_active=false is never selected, regardless of effective_from', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = edType();
    edPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01', 'is_active' => false]);

    expect(EmployeeLeavePolicy::currentFor($user, $type, Carbon::parse('2026-06-01')))->toBeNull();

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-06-01'));
    expect($created)->toHaveCount(0);
});

// G. Inserting a future-dated policy via the admin controller must NOT flip
// is_active on the previous row.
test('assigning a future-dated policy via the admin endpoint never deactivates the previous policy', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = edType();
    $original = edPolicy($employee, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.leave-policies.store', $employee), [
        'leave_type_id' => $type->id, 'annual_entitlement' => 15,
        'allocation_mode' => 'yearly', 'effective_from' => '2027-03-01',
    ])->assertRedirect();

    $original->refresh();
    expect($original->is_active)->toBeTrue();
    expect((float) $original->annual_entitlement)->toBe(12.0);

    // And the gap this whole fix targets: a query for a date between "now"
    // and the future policy's start must still resolve to the original.
    expect(EmployeeLeavePolicy::currentFor($employee, $type, Carbon::parse('2026-12-01'))->id)->toBe($original->id);
});

// H. Duplicate same employee + leave type + effective_from must be rejected.
test('a duplicate policy for the same employee, leave type, and effective_from is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = edType();
    edPolicy($employee, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.leave-policies.store', $employee), [
        'leave_type_id' => $type->id, 'annual_entitlement' => 18,
        'allocation_mode' => 'yearly', 'effective_from' => '2026-01-01',
    ])->assertSessionHasErrors('effective_from');

    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->where('leave_type_id', $type->id)->count())->toBe(1);
});
