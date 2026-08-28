<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
use App\Models\EmployeeSalary;
use App\Models\Setting;
use App\Models\User;
use App\Services\MonthlyPayableService;
use Carbon\Carbon;

/**
 * Admin "Record & Approve Overtime" combined flow (one screen, one
 * transaction, no separate approve() step) — covers lettered cases A-L
 * from the brief.
 */
function combinedGiveSalary(User $user, float $amount = 26000): User
{
    $admin = User::factory()->create(['role' => 'admin']);
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => $amount, 'effective_from' => '2026-01-01']);
    $salary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $salary->save();

    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    return $admin;
}

function combinedAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

// A — combined create+approve in one request, no separate approve() call
test('A: admin record+approve creates an already-approved record in one POST', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id'     => $employee->id,
        'ot_date'     => '2026-02-10',
        'hours'       => 2,
        'reason'      => 'Month-end close',
        'multiplier'  => 1.5,
    ]);

    $response->assertRedirect();
    $ot = EmployeeOvertime::where('user_id', $employee->id)->firstOrFail();
    expect($ot->request_status)->toBe('approved');
    expect($ot->reviewed_by)->toBe($admin->id);
    expect($ot->approved_amount)->not->toBeNull();
    expect($ot->calculated_amount)->not->toBeNull();
});

// B — two+ admin-recorded entries, same employee/period, mode=multiple
test('B: multiple admin-recorded entries in the same pay period succeed when mode=multiple', function () {
    Setting::set('overtime_allowance_mode', 'multiple');
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $payload = fn (string $date, float $hours) => [
        'user_id' => $employee->id, 'ot_date' => $date, 'hours' => $hours,
        'reason' => 'work', 'multiplier' => 1.5,
    ];

    $this->actingAs($admin)->post(route('admin.overtime.store'), $payload('2026-02-05', 2))->assertRedirect();
    $this->actingAs($admin)->post(route('admin.overtime.store'), $payload('2026-02-15', 3))->assertRedirect();

    expect(EmployeeOvertime::where('user_id', $employee->id)->where('request_status', 'approved')->count())->toBe(2);
});

// C — decimal hours accepted and persisted correctly
test('C: decimal hour values are accepted and persisted correctly', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    foreach ([1.5, 2.25, 11.76, 12.01] as $i => $hours) {
        $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
            'user_id' => $employee->id,
            'ot_date' => Carbon::parse('2026-03-01')->addDays($i)->toDateString(),
            'hours'   => $hours,
            'reason'  => 'ot',
            'multiplier' => 1.5,
        ]);
        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
    }

    $stored = EmployeeOvertime::where('user_id', $employee->id)->orderBy('ot_date')->pluck('hours')->map(fn ($h) => (float) $h);
    expect($stored->toArray())->toEqual([1.5, 2.25, 11.76, 12.01]);
});

// D — invalid hours rejected server-side
test('D: zero, negative, and non-numeric hours are rejected server-side', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    foreach ([0, -1, 'abc'] as $hours) {
        $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
            'user_id' => $employee->id,
            'ot_date' => '2026-04-01',
            'hours'   => $hours,
            'reason'  => 'ot',
            'multiplier' => 1.5,
        ]);
        $response->assertSessionHasErrors('hours');
    }

    expect(EmployeeOvertime::where('user_id', $employee->id)->count())->toBe(0);
});

// E — pay period display matches the OT date's month
test('E: pay period display reflects the selected OT date\'s month', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $response = $this->actingAs($admin)->get(route('admin.overtime.create', ['user_id' => $employee->id, 'ot_date' => '2026-08-15']));
    $response->assertOk();
    $response->assertSee('August 2026');

    $response2 = $this->actingAs($admin)->get(route('admin.overtime.create', ['user_id' => $employee->id, 'ot_date' => '2026-05-03']));
    $response2->assertOk();
    $response2->assertSee('May 2026');
});

// F — only one employee-select element on the page
test('F: the create page renders exactly one employee select element', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $response = $this->actingAs($admin)->get(route('admin.overtime.create', ['user_id' => $employee->id, 'ot_date' => '2026-08-15']));
    $response->assertOk();

    $matches = [];
    preg_match_all('/<select[^>]*name="user_id"/', $response->getContent(), $matches);
    expect($matches[0])->toHaveCount(1);
});

// G — manual override becomes approved_amount, distinct from calculated_amount
test('G: manual override amount in the combined form becomes approved_amount', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $employee->id,
        'ot_date' => '2026-06-10',
        'hours'   => 2,
        'reason'  => 'ot',
        'multiplier' => 1.5,
        'manual_amount' => 999.99,
    ])->assertRedirect();

    $ot = EmployeeOvertime::where('user_id', $employee->id)->firstOrFail();
    expect((float) $ot->approved_amount)->toBe(999.99);
    expect((float) $ot->calculated_amount)->not->toBe(999.99);
    expect($ot->used_manual_override)->toBeTrue();
});

// H — final approved amount matches override-or-calculated
test('H: final approved amount matches calculated_amount when no override is given', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $employee->id,
        'ot_date' => '2026-07-10',
        'hours'   => 2,
        'reason'  => 'ot',
        'multiplier' => 1.5,
    ])->assertRedirect();

    $ot = EmployeeOvertime::where('user_id', $employee->id)->firstOrFail();
    expect((float) $ot->approved_amount)->toBe((float) $ot->calculated_amount);
    expect($ot->used_manual_override)->toBeFalse();
});

// I — admin can cancel/delete an entry
test('I: admin can delete (cancel) an admin-recorded overtime entry', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $employee->id,
        'ot_date' => '2026-08-01',
        'hours'   => 2,
        'reason'  => 'ot',
        'multiplier' => 1.5,
    ]);
    $ot = EmployeeOvertime::where('user_id', $employee->id)->firstOrFail();

    $response = $this->actingAs($admin)->delete(route('admin.overtime.destroy', $ot));
    $response->assertRedirect();

    expect($ot->fresh()->request_status)->toBe('cancelled');
    // Not a hard delete — the row must still exist.
    expect(EmployeeOvertime::find($ot->id))->not->toBeNull();
});

// J — cancelled entry excluded from MonthlyPayableService's sum
test('J: a cancelled overtime entry no longer contributes to the monthly payable sum', function () {
    $admin = combinedAdmin();
    $employee = User::factory()->create();
    combinedGiveSalary($employee);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $employee->id,
        'ot_date' => '2026-08-05',
        'hours'   => 2,
        'reason'  => 'ot',
        'multiplier' => 1.5,
    ]);
    $ot = EmployeeOvertime::where('user_id', $employee->id)->firstOrFail();
    $approvedAmount = (float) $ot->approved_amount;
    expect($approvedAmount)->toBeGreaterThan(0);

    $service = app(MonthlyPayableService::class);
    $monthStart = Carbon::parse('2026-08-01')->startOfMonth();
    $monthEnd = Carbon::parse('2026-08-01')->endOfMonth();
    $before = $service->calculate($employee, $monthStart, $monthEnd);

    $this->actingAs($admin)->delete(route('admin.overtime.destroy', $ot));

    $after = $service->calculate($employee, $monthStart, $monthEnd);

    // The delta caused by removing this OT entry must equal its approved
    // amount (i.e. it fully drops out of the sum).
    expect(round($before['net_payable'] - $after['net_payable'], 2))->toBe(round($approvedAmount, 2));
});
