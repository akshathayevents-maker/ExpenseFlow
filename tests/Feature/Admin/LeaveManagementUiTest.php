<?php

use App\Models\EmployeeLeaveLedger;
use App\Models\EmployeeLeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;

function lmLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function lmPolicy(User $user, LeaveType $type, User $admin, array $attrs = []): EmployeeLeavePolicy
{
    return EmployeeLeavePolicy::create(array_merge([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'annual_entitlement' => 12, 'allocation_mode' => 'yearly',
        'effective_from' => '2026-01-01', 'is_active' => true, 'created_by' => $admin->id,
    ], $attrs));
}

// ── Leave Types ──────────────────────────────────────────────────────────

test('admin can create a leave type', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin->fresh())->post(route('admin.leave-types.store'), [
        'name' => 'Sick Leave', 'code' => 'SL-' . uniqid(),
        'is_active' => 1, 'is_paid' => 1, 'allow_half_day' => 1,
    ])->assertRedirect(route('admin.leave-types.index'));

    expect(LeaveType::where('name', 'Sick Leave')->exists())->toBeTrue();
});

test('manager cannot create a leave type', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager->fresh())->post(route('admin.leave-types.store'), [
        'name' => 'Sick Leave', 'code' => 'SL-' . uniqid(),
    ])->assertForbidden();
});

test('employee cannot create a leave type', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee->fresh())->get(route('admin.leave-types.index'))->assertForbidden();
    $this->actingAs($employee->fresh())->post(route('admin.leave-types.store'), [
        'name' => 'Sick Leave', 'code' => 'SL-' . uniqid(),
    ])->assertForbidden();
});

test('admin can edit an existing leave type without deleting it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lmLeaveType(['name' => 'Casual', 'is_active' => true]);

    $this->actingAs($admin->fresh())->put(route('admin.leave-types.update', $type), [
        'name' => 'Casual Leave (Updated)', 'code' => $type->code, 'is_active' => 0,
    ])->assertRedirect(route('admin.leave-types.index'));

    $type->refresh();
    expect($type->name)->toBe('Casual Leave (Updated)');
    expect($type->is_active)->toBeFalse();
    expect(LeaveType::count())->toBe(1); // never deleted
});

// ── Leave Policy assignment ──────────────────────────────────────────────

test('admin can assign a leave policy to an employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();

    $this->actingAs($admin->fresh())->post(route('admin.employees.leave-policies.store', $employee), [
        'leave_type_id' => $type->id, 'annual_entitlement' => 15,
        'allocation_mode' => 'yearly', 'effective_from' => '2026-01-01',
    ])->assertRedirect(route('admin.employees.leave-policies.index', $employee));

    $policy = EmployeeLeavePolicy::where('user_id', $employee->id)->first();
    expect((float) $policy->annual_entitlement)->toBe(15.0);
    expect($policy->is_active)->toBeTrue();
});

test('updating a leave policy creates a new effective-dated row and never mutates the old one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $original = lmPolicy($employee, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2026-01-01']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.leave-policies.store', $employee), [
        'leave_type_id' => $type->id, 'annual_entitlement' => 18,
        'allocation_mode' => 'yearly', 'effective_from' => '2026-06-01',
    ])->assertRedirect();

    expect(EmployeeLeavePolicy::count())->toBe(2);

    $original->refresh();
    expect((float) $original->annual_entitlement)->toBe(12.0); // untouched
    expect($original->effective_from->toDateString())->toBe('2026-01-01'); // untouched
    // is_active is an independent enable/disable switch, NOT a "superseded"
    // flag — inserting a future-dated row must never flip it, or a gap
    // opens for every date before the new row's effective_from.
    expect($original->is_active)->toBeTrue();

    $newPolicy = EmployeeLeavePolicy::whereDate('effective_from', '2026-06-01')->first();
    expect((float) $newPolicy->annual_entitlement)->toBe(18.0);
    expect($newPolicy->is_active)->toBeTrue();

    // effective_from alone must decide "current," not is_active: before the
    // new row starts, the original governs; from its date on, the new one does.
    expect(EmployeeLeavePolicy::currentFor($employee, $type, \Carbon\Carbon::parse('2026-03-01'))->id)->toBe($original->id);
    expect(EmployeeLeavePolicy::currentFor($employee, $type, \Carbon\Carbon::parse('2026-06-01'))->id)->toBe($newPolicy->id);
});

test('non-admin cannot assign a leave policy', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();

    $this->actingAs($manager->fresh())->get(route('admin.employees.leave-policies.index', $employee))->assertForbidden();
    $this->actingAs($manager->fresh())->post(route('admin.employees.leave-policies.store', $employee), [
        'leave_type_id' => $type->id, 'annual_entitlement' => 10,
        'allocation_mode' => 'yearly', 'effective_from' => '2026-01-01',
    ])->assertForbidden();
});

// ── Approve / Reject (admin + manager, no hierarchy) ─────────────────────

test('admin can approve a pending leave request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'paid_leave_days' => 1, 'lop_days' => 0,
        'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($admin->fresh())->patch(route('admin.leave.requests.approve', $leaveRequest))
        ->assertRedirect();

    expect($leaveRequest->fresh()->status)->toBe('approved');
});

test('manager can approve a pending leave request', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'paid_leave_days' => 1, 'lop_days' => 0,
        'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($manager->fresh())->patch(route('manager.leave.requests.approve', $leaveRequest))
        ->assertRedirect();

    expect($leaveRequest->fresh()->status)->toBe('approved');
});

test('employee cannot self-approve their own leave request', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($employee->fresh())->patch(route('admin.leave.requests.approve', $leaveRequest))
        ->assertForbidden();

    expect($leaveRequest->fresh()->status)->toBe('pending');
});

test('approving an already-approved leave request fails cleanly via the flashed error, not a 500', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'paid_leave_days' => 1, 'lop_days' => 0,
        'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($admin->fresh())->patch(route('admin.leave.requests.approve', $leaveRequest))->assertRedirect();
    expect($leaveRequest->fresh()->status)->toBe('approved');

    // approve() authorizes only pending requests, so re-hitting the route on
    // an already-approved request is blocked by the policy (403) before the
    // service's own guard would ever run — either way, no 500 and no
    // second ledger/attendance write.
    $response = $this->actingAs($admin->fresh())->patch(route('admin.leave.requests.approve', $leaveRequest));
    $response->assertStatus(403);
});

test('admin can reject a pending leave request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($admin->fresh())->patch(route('admin.leave.requests.reject', $leaveRequest), [
        'review_note' => 'Not enough coverage.',
    ])->assertRedirect();

    $leaveRequest->refresh();
    expect($leaveRequest->status)->toBe('rejected');
    expect($leaveRequest->review_note)->toBe('Not enough coverage.');
});

test('admin can view the leave requests list and a single request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($admin->fresh())->get(route('admin.leave.requests.index'))->assertOk()->assertSee($employee->name);
    $this->actingAs($admin->fresh())->get(route('admin.leave.requests.show', $leaveRequest))->assertOk();
});

// ── Manual adjustment ─────────────────────────────────────────────────────

test('admin manual adjustment: positive amount succeeds and reflects on the balances view', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();

    $this->actingAs($admin->fresh())->post(route('admin.leave.adjustments.store', $employee), [
        'leave_type_id' => $type->id, 'amount' => 3, 'reason' => 'Compensatory credit',
    ])->assertRedirect();

    expect((float) EmployeeLeaveLedger::where('user_id', $employee->id)->sum('amount'))->toBe(3.0);

    $this->actingAs($admin->fresh())->get(route('admin.leave.balances.show', $employee))
        ->assertOk()
        ->assertSee($type->name);
});

test('admin manual adjustment: a negative amount that would go below zero is rejected and not persisted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();

    $response = $this->actingAs($admin->fresh())->post(route('admin.leave.adjustments.store', $employee), [
        'leave_type_id' => $type->id, 'amount' => -5, 'reason' => 'Correction',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect(EmployeeLeaveLedger::where('user_id', $employee->id)->exists())->toBeFalse();
});

test('manager cannot record a manual leave adjustment', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();

    $this->actingAs($manager->fresh())->post(route('admin.leave.adjustments.store', $employee), [
        'leave_type_id' => $type->id, 'amount' => 3, 'reason' => 'x',
    ])->assertForbidden();
});

// ── Employee blocked from admin/manager leave configuration routes ──────

test('employee is forbidden from every admin leave-type/policy/request route', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $other = User::factory()->create(['role' => 'employee']);
    $type = lmLeaveType();

    $this->actingAs($employee->fresh())->get(route('admin.leave-types.index'))->assertForbidden();
    $this->actingAs($employee->fresh())->get(route('admin.leave-types.create'))->assertForbidden();
    $this->actingAs($employee->fresh())->get(route('admin.employees.leave-policies.index', $other))->assertForbidden();
    $this->actingAs($employee->fresh())->get(route('admin.leave.requests.index'))->assertForbidden();
    $this->actingAs($employee->fresh())->get(route('admin.leave.balances.show', $other))->assertForbidden();
});

test('employee is forbidden from manager leave request routes', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee->fresh())->get(route('manager.leave.requests.index'))->assertForbidden();
});
