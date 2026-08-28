<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeSalary;
use App\Models\Setting;
use App\Models\User;
use App\Services\MonthlyPayableService;
use Carbon\Carbon;

/**
 * Overtime redesign (Part 4): "pause employee OT requesting" +
 * "admin-granted overtime allowance, single vs multiple per pay period".
 *
 * Covers the 17-case list:
 *   Employee: 1-4
 *   Admin:    5-13
 *   Payroll:  14-17
 *
 * REDESIGN (combined admin record+approve): admin.overtime.store now
 * creates AND approves an admin-recorded entry in one request (see
 * OvertimeService::recordAndApprove()) — it always requires `multiplier`
 * and the resulting record is already `approved`, with no separate
 * admin.overtime.approve call needed or possible for records created this
 * way. Every admin.overtime.store call below now supplies `multiplier`,
 * and the separate approve() calls that used to follow an admin.overtime.
 * store call have been removed accordingly — the intent of each case
 * (multiple-vs-single mode gating, per-period scoping, payroll aggregation)
 * is unchanged, only the mechanics of getting an admin-recorded entry to
 * `approved` changed from two requests to one.
 */
function flagGiveSalary(User $user, float $amount = 26000): void
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
}

// ── Employee: 1 — hidden on dashboard when flag off ─────────────────────────

test('1: Request OT quick action is hidden on the employee dashboard when the flag is off', function () {
    Setting::set('employee_overtime_requests_enabled', false);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertOk()
        ->assertDontSee('Request OT');
});

test('1b: Request OT quick action is visible on the employee dashboard when the flag is on', function () {
    Setting::set('employee_overtime_requests_enabled', true);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertOk()
        ->assertSee('Request OT');
});

// ── Employee: 2 — cannot create via UI/route when flag off (403/redirect) ───

test('2: employee cannot load the OT create page when the flag is off', function () {
    Setting::set('employee_overtime_requests_enabled', false);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.create'))->assertForbidden();
});

// ── Employee: 3 — direct endpoint POST also blocked ─────────────────────────

test('3: direct POST to the employee OT store endpoint is blocked when the flag is off', function () {
    Setting::set('employee_overtime_requests_enabled', false);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'blocked attempt',
    ])->assertForbidden();

    expect(EmployeeOvertime::count())->toBe(0);
});

// ── Employee: 4 — re-enablement requires only the flag flip ─────────────────

test('4: flipping the flag back to true restores the full request/approval flow with zero code changes', function () {
    Setting::set('employee_overtime_requests_enabled', false);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'still blocked',
    ])->assertForbidden();

    Setting::set('employee_overtime_requests_enabled', true);

    $response = $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'now allowed',
    ]);

    $ot = EmployeeOvertime::first();
    $response->assertRedirect(route('employee.overtime.show', $ot));
    expect($ot->origin)->toBe('employee_request');

    // The approval path (untouched by this feature) still works too.
    $manager = User::factory()->create(['role' => 'manager']);
    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5])
        ->assertRedirect();
    expect($ot->refresh()->isApproved())->toBeTrue();
});

// ── Admin: 5-8 — add allowance, select employee, select period, single add works ──

test('5-8: admin can add an overtime allowance for a selected employee and period', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-15', 'hours' => 4, 'reason' => 'allowance grant',
        'multiplier' => 1.5,
    ]);

    $ot = EmployeeOvertime::first();
    $response->assertRedirect(route('admin.overtime.show', $ot));
    expect($ot->user_id)->toBe($user->id);
    expect($ot->origin)->toBe('admin_recorded');
    expect($ot->ot_date->format('Y-m'))->toBe('2026-07');
    expect($ot->isApproved())->toBeTrue();
});

// ── Admin: 9 — multiple allowances in 'multiple' mode all persist ───────────

test('9: multiple admin-recorded allowances in the same period all persist under multiple mode', function () {
    Setting::set('overtime_allowance_mode', 'multiple');
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user);

    foreach (['2026-07-01', '2026-07-10', '2026-07-20'] as $date) {
        $this->actingAs($admin)->post(route('admin.overtime.store'), [
            'user_id' => $user->id, 'ot_date' => $date, 'hours' => 2, 'reason' => "entry {$date}",
            'multiplier' => 1.5,
        ])->assertRedirect();
    }

    expect(EmployeeOvertime::where('user_id', $user->id)->where('origin', 'admin_recorded')->count())->toBe(3);
});

// ── Admin: 10 — second allowance in 'single' mode is rejected ───────────────

test('10: a second admin-recorded allowance in the same period is rejected under single mode', function () {
    Setting::set('overtime_allowance_mode', 'single');
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-01', 'hours' => 2, 'reason' => 'first',
        'multiplier' => 1.5,
    ])->assertRedirect();

    $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-20', 'hours' => 2, 'reason' => 'second attempt',
        'multiplier' => 1.5,
    ]);

    $response->assertSessionHasErrors('ot_date');
    expect(EmployeeOvertime::where('user_id', $user->id)->where('origin', 'admin_recorded')->count())->toBe(1);
});

test('10b: single mode does not block a second allowance in a DIFFERENT pay period', function () {
    Setting::set('overtime_allowance_mode', 'single');
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-01', 'hours' => 2, 'reason' => 'july',
        'multiplier' => 1.5,
    ])->assertRedirect();

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-08-01', 'hours' => 2, 'reason' => 'august',
        'multiplier' => 1.5,
    ])->assertRedirect();

    expect(EmployeeOvertime::where('user_id', $user->id)->where('origin', 'admin_recorded')->count())->toBe(2);
});

// ── Admin: 11 — allowances correctly scoped to the right pay period ─────────

test('11: allowances are correctly scoped to the pay period they fall in', function () {
    Setting::set('overtime_allowance_mode', 'single');
    $admin = User::factory()->create(['role' => 'admin']);
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    flagGiveSalary($userA);
    flagGiveSalary($userB);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $userA->id, 'ot_date' => '2026-07-31', 'hours' => 2, 'reason' => 'end of july',
        'multiplier' => 1.5,
    ])->assertRedirect();

    // Different employee, same-ish date — must not be affected by userA's entry.
    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $userB->id, 'ot_date' => '2026-07-31', 'hours' => 2, 'reason' => 'end of july b',
        'multiplier' => 1.5,
    ])->assertRedirect();

    // userA again, next month — must succeed (different period).
    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $userA->id, 'ot_date' => '2026-08-01', 'hours' => 2, 'reason' => 'start of august',
        'multiplier' => 1.5,
    ])->assertRedirect();

    expect(EmployeeOvertime::where('origin', 'admin_recorded')->count())->toBe(3);
});

// ── Admin: 12 — allowance correctly included in payroll calculation ─────────

test('12: an approved admin-granted allowance is included in MonthlyPayableService payroll calculation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user, 26000);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'allowance',
        'multiplier' => 1.5,
    ]);
    $ot = EmployeeOvertime::first();

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe((float) $ot->refresh()->approved_amount);
    expect($result['approved_overtime_amount'])->toBeGreaterThan(0);
});

// ── Admin: 13 — unauthorized cannot add allowances ──────────────────────────

test('13: an employee cannot POST to the admin overtime store endpoint', function () {
    $employee = User::factory()->create();
    $target = User::factory()->create();
    flagGiveSalary($target);

    $this->actingAs($employee)->post(route('admin.overtime.store'), [
        'user_id' => $target->id, 'ot_date' => '2026-07-01', 'hours' => 2, 'reason' => 'x',
    ])->assertForbidden();

    expect(EmployeeOvertime::count())->toBe(0);
});

test('13b: a manager (without recordForOther rights) cannot POST to the admin overtime store endpoint', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $target = User::factory()->create();
    flagGiveSalary($target);

    $this->actingAs($manager)->post(route('admin.overtime.store'), [
        'user_id' => $target->id, 'ot_date' => '2026-07-01', 'hours' => 2, 'reason' => 'x',
    ])->assertForbidden();

    expect(EmployeeOvertime::count())->toBe(0);
});

// ── Payroll: 14 — pre-existing employee-originated approved OT unaffected ───

test('14: pre-existing employee-originated approved OT still contributes to payroll unchanged', function () {
    Setting::set('employee_overtime_requests_enabled', true);
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    flagGiveSalary($user, 26000);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-05', 'hours' => 2, 'reason' => 'regular request',
    ]);
    $ot = EmployeeOvertime::first();
    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5]);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe((float) $ot->refresh()->approved_amount);
});

// ── Payroll: 15 — admin-created allowances included correctly (alongside employee ones) ──

test('15: admin-created allowances and employee-originated OT sum together correctly in payroll', function () {
    Setting::set('employee_overtime_requests_enabled', true);
    Setting::set('overtime_allowance_mode', 'multiple');
    $admin = User::factory()->create(['role' => 'admin']);
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    flagGiveSalary($user, 26000);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-05', 'hours' => 2, 'reason' => 'employee entry',
    ]);
    $employeeOt = EmployeeOvertime::where('origin', 'employee_request')->first();
    $this->actingAs($manager)->patch(route('manager.overtime.approve', $employeeOt), ['multiplier' => 1.5]);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-08-12', 'hours' => 3, 'reason' => 'admin allowance',
        'multiplier' => 1.5,
    ]);
    $adminOt = EmployeeOvertime::where('origin', 'admin_recorded')->first();

    $expected = round((float) $employeeOt->refresh()->approved_amount + (float) $adminOt->refresh()->approved_amount, 2);
    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe($expected);
});

// ── Payroll: 16 — multiple entries aggregate correctly in one month ─────────

test('16: three admin-recorded allowances in one month all aggregate into the payroll figure', function () {
    Setting::set('overtime_allowance_mode', 'multiple');
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user, 26000);

    foreach (['2026-08-02', '2026-08-11', '2026-08-22'] as $date) {
        $this->actingAs($admin)->post(route('admin.overtime.store'), [
            'user_id' => $user->id, 'ot_date' => $date, 'hours' => 2, 'reason' => "entry {$date}",
            'multiplier' => 1.5,
        ]);
    }

    $all = EmployeeOvertime::where('user_id', $user->id)->where('origin', 'admin_recorded')->get();
    expect($all)->toHaveCount(3);

    $expectedTotal = 0.0;
    foreach ($all as $ot) {
        $expectedTotal += (float) $ot->refresh()->approved_amount;
    }

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(round($expectedTotal, 2));
});

// ── Payroll: 17 — single mode's rejection is enforced at creation time ──────

test('17: single mode rejects the second allowance at creation time, never letting it reach payroll', function () {
    Setting::set('overtime_allowance_mode', 'single');
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    flagGiveSalary($user, 26000);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-08-02', 'hours' => 2, 'reason' => 'first',
        'multiplier' => 1.5,
    ]);
    $first = EmployeeOvertime::first();

    $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 5, 'reason' => 'second attempt, should be rejected',
        'multiplier' => 1.5,
    ]);
    $response->assertSessionHasErrors('ot_date');

    expect(EmployeeOvertime::where('user_id', $user->id)->where('origin', 'admin_recorded')->count())->toBe(1);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
    expect($result['approved_overtime_amount'])->toBe((float) $first->refresh()->approved_amount);
});
