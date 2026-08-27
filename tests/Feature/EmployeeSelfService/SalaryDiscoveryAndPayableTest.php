<?php

use App\Models\EmployeeAdvance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeSalary;
use App\Models\User;
use App\Services\AdvanceEligibilityService;
use App\Services\MonthlyPayableService;
use Carbon\Carbon;

function setSalary(User $employee, User $admin, float $amount, string $effectiveFrom): EmployeeSalary
{
    \Illuminate\Support\Facades\Auth::login($admin);

    return app(\App\Services\EmployeeSalaryService::class)
        ->setSalary($employee, $amount, Carbon::parse($effectiveFrom), $admin);
}

// ── Discoverability: global Employee Salaries list ─────────────────────────

test('admin can open the global employee salaries list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 30000, '2026-01-01');

    $this->actingAs($admin)->get(route('admin.salaries.index'))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee('30,000'); // whole-number monthly amount on the redesigned salary card
});

test('global salaries list shows a no-salary-set indicator for employees without one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.salaries.index'))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee('Salary not configured');
});

test('clicking an employee in the salaries list opens their compensation page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 30000, '2026-01-01');

    $this->actingAs($admin)->get(route('admin.salaries.index'))
        ->assertSee(route('admin.employees.salaries.index', $employee), false);
});

test('non-admin cannot open the global employee salaries list', function () {
    $employee = User::factory()->create();
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($employee->fresh())->get(route('admin.salaries.index'))->assertForbidden();
    $this->actingAs($manager)->get(route('admin.salaries.index'))->assertForbidden();
});

test('admin sidebar Employee Salaries link points to the real salaries list, not the plain employee list', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.salaries.index'), false);
});

// ── Employee detail: Compensation card ──────────────────────────────────

test('employee detail page shows a Compensation card with current salary', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 42000, '2026-01-01');

    $this->actingAs($admin)->get(route('admin.employees.show', $employee))
        ->assertOk()
        ->assertSee('Compensation')
        ->assertSee('42,000.00')
        ->assertSee('Change Salary');
});

test('employee detail page shows a clear call to action when no salary is configured', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.employees.show', $employee))
        ->assertOk()
        ->assertSee('No salary configured')
        ->assertSee('Set Employee Salary');
});

test('employee detail page shows salary history once more than one salary exists', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 30000, '2026-01-01');
    setSalary($employee, $admin, 35000, '2026-06-01');

    $this->actingAs($admin)->get(route('admin.employees.show', $employee))
        ->assertOk()
        ->assertSee('Salary History')
        ->assertSee('30,000.00')
        ->assertSee('35,000.00');
});

// ── Monthly payable: built on existing pieces, not duplicated logic ──────

test('monthly payable resolves salary through currentSalaryAsOf', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 31000, '2026-01-01');

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    expect($result['monthly_salary'])->toBe(31000.0);
});

test('monthly payable throws when the employee has no salary for the period', function () {
    $employee = User::factory()->create();

    expect(fn () => app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    ))->toThrow(DomainException::class);
});

test('monthly payable segments earned salary across a mid-month salary change instead of throwing', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 15000, '2026-01-01');
    setSalary($employee, $admin, 20000, '2026-02-15');

    // Mark every day of February present. Feb 2026 has 4 Sundays (1, 8, 15,
    // 22) — the app's default weekly-off day — so applicable_working_days
    // = 28 − 4 = 24, and Sunday attendance rows don't contribute (they're
    // excluded as non-working days regardless of status).
    for ($d = Carbon::parse('2026-02-01'); $d->lte(Carbon::parse('2026-02-28')); $d->addDay()) {
        \App\Models\EmployeeAttendance::create([
            'user_id' => $employee->id, 'attendance_date' => $d->toDateString(),
            'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    // applicable_working_days = 24.
    // Segment 1 (Feb 1-14, 12 working days) at ₹15,000/mo → 12 × (15000/24) = 7500.00
    // Segment 2 (Feb 15-28, 12 working days) at ₹20,000/mo → 12 × (20000/24) = 10000.00
    expect($result['applicable_working_days'])->toBe(24);
    expect($result['payable_days'])->toBe(24.0);
    expect($result['payable_salary'])->toBe(17500.0);
    expect($result['unavailable_reason'] ?? null)->toBeNull();
});

test('monthly payable single-salary result is unchanged (byte-identical) after the segmentation fix', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 30000, '2026-01-01');

    for ($d = Carbon::parse('2026-02-01'); $d->lte(Carbon::parse('2026-02-10')); $d->addDay()) {
        \App\Models\EmployeeAttendance::create([
            'user_id' => $employee->id, 'attendance_date' => $d->toDateString(),
            'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    // Present marked Feb 1-10, but Feb 1 & 8 are Sundays (excluded), so
    // payable_days = 8 out of 24 applicable working days, ₹30,000/mo.
    expect($result['payable_days'])->toBe(8.0);
    expect($result['payable_salary'])->toBe(round((30000 / 24) * 8, 2));
});

test('monthly payable handles multiple salary changes within one period', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 10000, '2026-01-01');
    setSalary($employee, $admin, 20000, '2026-02-10');
    setSalary($employee, $admin, 30000, '2026-02-20');

    for ($d = Carbon::parse('2026-02-01'); $d->lte(Carbon::parse('2026-02-28')); $d->addDay()) {
        \App\Models\EmployeeAttendance::create([
            'user_id' => $employee->id, 'attendance_date' => $d->toDateString(),
            'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    // applicable_working_days = 24.
    // Segment 1: Feb 1-9 (7 working days, excl. Sundays 1 & 8) @ 10000/24
    // Segment 2: Feb 10-19 (9 working days, excl. Sunday 15) @ 20000/24
    // Segment 3: Feb 20-28 (8 working days, excl. Sunday 22) @ 30000/24
    $expected = round((7 * 10000 / 24) + (9 * 20000 / 24) + (8 * 30000 / 24), 2);
    expect($result['payable_salary'])->toBe($expected);
});

test('monthly payable treats a salary effective exactly on period start as covering the whole period', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 25000, '2026-02-01');

    for ($d = Carbon::parse('2026-02-01'); $d->lte(Carbon::parse('2026-02-28')); $d->addDay()) {
        \App\Models\EmployeeAttendance::create([
            'user_id' => $employee->id, 'attendance_date' => $d->toDateString(),
            'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    expect($result['payable_salary'])->toBe(25000.0);
});

test('monthly payable applies a salary effective on the last day of the period to only that day', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 10000, '2026-01-01');
    setSalary($employee, $admin, 20000, '2026-02-28');

    for ($d = Carbon::parse('2026-02-01'); $d->lte(Carbon::parse('2026-02-28')); $d->addDay()) {
        \App\Models\EmployeeAttendance::create([
            'user_id' => $employee->id, 'attendance_date' => $d->toDateString(),
            'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    // applicable_working_days = 24.
    // Feb 1-27 (23 working days, excl. Sundays 1,8,15,22) @ 10000/24
    // Feb 28 (1 working day, Saturday) @ 20000/24
    $expected = round((23 * 10000 / 24) + (1 * 20000 / 24), 2);
    expect($result['payable_salary'])->toBe($expected);
});

test('monthly payable treats a salary-gap date range as contributing zero to earned salary', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    // First salary row only starts Feb 10 — Feb 1-9 has NO EmployeeSalary
    // row at all (a genuine gap), even though the employee has attendance.
    setSalary($employee, $admin, 28000, '2026-02-10');

    for ($d = Carbon::parse('2026-02-01'); $d->lte(Carbon::parse('2026-02-28')); $d->addDay()) {
        \App\Models\EmployeeAttendance::create([
            'user_id' => $employee->id, 'attendance_date' => $d->toDateString(),
            'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    // Gap days (Feb 1-9) contribute 0 regardless of attendance.
    // applicable_working_days = 24. Feb 10-28 has 17 working days
    // (excluding Sundays 15 & 22) — only those are paid, at 28000/24.
    $expected = round(17 * 28000 / 24, 2);
    expect($result['payable_salary'])->toBe($expected);
    // payable_days still reflects actual attendance/working-days across the
    // whole period (24) — the gap only affects earned SALARY, not the
    // days-worked count.
    expect($result['payable_days'])->toBe(24.0);
});

test('monthly payable adds approved overtime for the period without re-deriving its calculation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 30000, '2026-01-01');

    $ot = new EmployeeOvertime();
    $ot->fill(['user_id' => $employee->id, 'ot_date' => '2026-02-10', 'hours' => 2, 'category' => 'weekday', 'reason' => 'x']);
    $ot->forceFill([
        'origin' => 'admin_recorded', 'created_by' => $admin->id,
        'request_status' => 'approved', 'hourly_rate_snapshot' => 100, 'rate_multiplier' => 1.5, 'calculated_amount' => 300,
        'approved_amount' => 300, 'used_manual_override' => false,
    ]);
    $ot->save();

    $result = app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    );

    expect($result['approved_overtime_amount'])->toBe(300.0);
    expect($result['net_payable'])->toBe(round($result['payable_salary'] + 300, 2));
});

// ── Advance eligibility: superseded by the concrete formula added in a
// later task (see AdvanceEligibilityTest.php for the full behavior) — kept
// here only as a smoke test that current salary/outstanding are surfaced. ─

test('advance eligibility service reports current salary and outstanding advances', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 40000, '2026-01-01');

    $advance = new EmployeeAdvance();
    $advance->fill(['user_id' => $employee->id, 'origin' => 'employee_request', 'requested_amount' => 10000]);
    $advance->forceFill([
        'created_by' => $employee->id, 'request_status' => 'approved', 'approved_amount' => 10000,
        'payment_status' => 'paid', 'original_amount' => 10000, 'outstanding_amount' => 6000,
    ]);
    $advance->save();

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-02-15'));

    expect($result['monthly_salary'])->toBe(40000.0);
    expect($result['outstanding_amount'])->toBe(6000.0);
});

test('advance eligibility service surfaces why salary is unavailable rather than guessing it', function () {
    $employee = User::factory()->create(); // no salary at all

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-02-15'));

    expect($result['salary_configured'])->toBeFalse();
    expect($result['eligible_advance_amount'])->toBe(0.0);
    expect($result['unavailable_reason'])->not->toBeNull();
});
