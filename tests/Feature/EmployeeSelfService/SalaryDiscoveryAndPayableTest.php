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
        ->assertSee('30,000.00');
});

test('global salaries list shows a no-salary-set indicator for employees without one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.salaries.index'))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee('No salary set');
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

test('monthly payable refuses to guess across a mid-month salary change', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    setSalary($employee, $admin, 30000, '2026-01-01');
    setSalary($employee, $admin, 35000, '2026-02-15');

    expect(fn () => app(MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'),
    ))->toThrow(DomainException::class);
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
