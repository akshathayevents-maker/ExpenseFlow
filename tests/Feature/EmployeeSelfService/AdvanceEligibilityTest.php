<?php

use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\User;
use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeSalaryService;
use Carbon\Carbon;

function elSetSalary(User $employee, User $admin, float $amount, string $effectiveFrom): void
{
    Illuminate\Support\Facades\Auth::login($admin);
    app(EmployeeSalaryService::class)->setSalary($employee, $amount, Carbon::parse($effectiveFrom), $admin);
}

function elMarkPresent(User $user, string $date): void
{
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date,
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

function elPaidAdvance(User $employee, User $admin, float $original, float $outstanding): EmployeeAdvance
{
    $advance = new EmployeeAdvance();
    $advance->fill(['user_id' => $employee->id, 'origin' => 'employee_request', 'requested_amount' => $original]);
    $advance->forceFill([
        'created_by' => $employee->id, 'request_status' => 'approved', 'approved_amount' => $original,
        'payment_status' => 'paid', 'original_amount' => $original, 'outstanding_amount' => $outstanding,
        'approved_by' => $admin->id, 'approved_at' => now(), 'paid_by' => $admin->id, 'paid_at' => now(),
    ]);
    $advance->save();

    return $advance;
}

afterEach(function () {
    Carbon::setTestNow();
});

// ── Concrete formula test case (per task spec) ───────────────────────────
//
// Range is always [Jan 1, asOf] — MonthlyPayableService always starts from
// the 1st of asOf's month, never an arbitrary custom range.
// asOf = 17 Jan 2026 (Saturday). Sundays in [1 Jan, 17 Jan]: 4th, 11th → 2.
// applicable_working_days = 17 − 2 = 15 (Saturdays are NOT a configured
// weekly off in this app — only Sunday is, per Setting::weekly_off_days).
// Present marked on exactly 10 of those 15 working days → payable_days = 10.
// daily_salary = 30000 / 15 = 2000.00 (existing convention: monthly_salary
// ÷ applicable_working_days — the same divisor OvertimeCalculationService
// already uses, reused as-is here, not re-derived).
// earned_salary = 2000 × 10 = 20000.00
test('eligible advance is earned salary minus previous advances minus outstanding amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');

    foreach (['2026-01-01', '2026-01-02', '2026-01-03', '2026-01-05', '2026-01-06',
              '2026-01-07', '2026-01-08', '2026-01-09', '2026-01-10', '2026-01-12'] as $date) {
        elMarkPresent($employee, $date);
    }
    // Left unmarked (absent): 13, 14, 15, 16, 17 Jan.

    elPaidAdvance($employee, $admin, 2000, 0);    // fully repaid — "previous advances"
    elPaidAdvance($employee, $admin, 5000, 1000); // still owed — "outstanding amount"

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-17'));

    expect($result['salary_configured'])->toBeTrue();
    expect($result['monthly_salary'])->toBe(30000.0);
    expect($result['payable_days'])->toBe(10.0);
    expect($result['daily_salary'])->toBe(2000.0);
    expect($result['earned_salary'])->toBe(20000.0);
    expect($result['previous_advances_amount'])->toBe(2000.0);
    expect($result['outstanding_amount'])->toBe(1000.0);
    expect($result['eligible_advance_amount'])->toBe(17000.0); // 20000 - 2000 - 1000
});

test('a fully repaid advance counts as previous advances, not outstanding', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    elMarkPresent($employee, '2026-01-05');

    elPaidAdvance($employee, $admin, 4000, 0);

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-05'));

    expect($result['previous_advances_amount'])->toBe(4000.0);
    expect($result['outstanding_amount'])->toBe(0.0);
});

test('a still-owed advance counts as outstanding, not previous advances', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    elMarkPresent($employee, '2026-01-05');

    elPaidAdvance($employee, $admin, 4000, 2500);

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-05'));

    expect($result['previous_advances_amount'])->toBe(0.0);
    expect($result['outstanding_amount'])->toBe(2500.0);
});

test('multiple advance records are summed within their bucket, never double-counted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 60000, '2026-01-01');
    foreach (range(1, 15) as $d) {
        if ((int) date('N', strtotime("2026-01-$d")) !== 7) { // skip Sundays
            elMarkPresent($employee, sprintf('2026-01-%02d', $d));
        }
    }

    elPaidAdvance($employee, $admin, 1000, 0);    // settled
    elPaidAdvance($employee, $admin, 2000, 0);    // settled
    elPaidAdvance($employee, $admin, 3000, 1500); // still owed
    elPaidAdvance($employee, $admin, 500, 500);   // still owed (fully unpaid)

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-15'));

    expect($result['previous_advances_amount'])->toBe(3000.0); // 1000 + 2000
    expect($result['outstanding_amount'])->toBe(2000.0);        // 1500 + 500
});

test('pending or approved-but-undisbursed advances do not reduce eligibility (no ledger movement yet)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    elMarkPresent($employee, '2026-01-05');

    $pending = new EmployeeAdvance();
    $pending->fill(['user_id' => $employee->id, 'origin' => 'employee_request', 'requested_amount' => 9000]);
    $pending->forceFill(['created_by' => $employee->id]); // stays pending, unpaid
    $pending->save();

    $approvedUnpaid = new EmployeeAdvance();
    $approvedUnpaid->fill(['user_id' => $employee->id, 'origin' => 'employee_request', 'requested_amount' => 9000]);
    $approvedUnpaid->forceFill([
        'created_by' => $employee->id, 'request_status' => 'approved', 'approved_amount' => 9000,
    ]); // approved but payment_status still 'unpaid'
    $approvedUnpaid->save();

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-05'));

    expect($result['previous_advances_amount'])->toBe(0.0);
    expect($result['outstanding_amount'])->toBe(0.0);
});

// ── Server-side enforcement on the actual request endpoint ───────────────

test('employee cannot request more than their eligible amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    foreach (['2026-01-01', '2026-01-02', '2026-01-03', '2026-01-05', '2026-01-06',
              '2026-01-07', '2026-01-08', '2026-01-09', '2026-01-10', '2026-01-12'] as $date) {
        elMarkPresent($employee, $date);
    }
    // eligible = 30000/15*10 = 20000

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-01-17',
        'status' => 'absent', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);
    Carbon::setTestNow(Carbon::parse('2026-01-17 10:00:00', 'Asia/Kolkata'));

    $this->actingAs($employee->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 20000,
    ])->assertRedirect();
    expect(EmployeeAdvance::count())->toBe(1);
});

test('server rejects an excessive amount even if the frontend max attribute is bypassed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    foreach (['2026-01-01', '2026-01-02', '2026-01-03', '2026-01-05', '2026-01-06',
              '2026-01-07', '2026-01-08', '2026-01-09', '2026-01-10', '2026-01-12'] as $date) {
        elMarkPresent($employee, $date);
    }
    // eligible = 20000 — attempt 20000.01 and a wildly larger bypass value

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-01-17',
        'status' => 'absent', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);
    Carbon::setTestNow(Carbon::parse('2026-01-17 10:00:00', 'Asia/Kolkata'));

    $this->actingAs($employee->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 20000.01,
    ])->assertSessionHasErrors('requested_amount');

    $this->actingAs($employee->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 999999,
    ])->assertSessionHasErrors('requested_amount');

    expect(EmployeeAdvance::count())->toBe(0);
});

test('employee with no salary configured cannot request an advance', function () {
    $employee = User::factory()->create();
    elMarkPresent($employee, Carbon::now('Asia/Kolkata')->toDateString());

    $this->actingAs($employee->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 100,
    ])->assertSessionHasErrors('requested_amount');

    expect(EmployeeAdvance::count())->toBe(0);
});

test('zero or negative eligibility blocks any positive advance request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    elMarkPresent($employee, '2026-01-05'); // 1 payable day out of applicable working days in [1-5 Jan]

    // Outstanding wipes out the small earned amount entirely.
    elPaidAdvance($employee, $admin, 50000, 50000);

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-05'));
    expect($result['eligible_advance_amount'])->toBe(0.0); // never negative

    Carbon::setTestNow(Carbon::parse('2026-01-05 10:00:00', 'Asia/Kolkata'));
    $this->actingAs($employee->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 100,
    ])->assertSessionHasErrors('requested_amount');

    // 1 = only the pre-existing paid advance from setup — the rejected
    // POST must not have created a new one.
    expect(EmployeeAdvance::count())->toBe(1);
});

// ── Employment period ─────────────────────────────────────────────────────

test('payable days before employment_start_date are excluded from earned salary', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2026-01-10']);
    elSetSalary($employee, $admin, 30000, '2026-01-01');

    // Mark present both before and on/after the employment start date.
    elMarkPresent($employee, '2026-01-05'); // before employment_start_date — must not count
    elMarkPresent($employee, '2026-01-12'); // on/after — counts

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-15'));

    // Only 1 payable day (Jan 12) should be counted; the pre-employment
    // present row is excluded by PayableDaysCalculator's own employment-
    // period clamp, not by any logic in AdvanceEligibilityService.
    expect($result['payable_days'])->toBe(1.0);
});

test('payable days after employment_end_date are excluded from earned salary', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_end_date' => '2026-01-10']);
    elSetSalary($employee, $admin, 30000, '2026-01-01');

    elMarkPresent($employee, '2026-01-05');  // within employment period — counts
    elMarkPresent($employee, '2026-01-14');  // after employment_end_date — must not count

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-01-15'));

    expect($result['payable_days'])->toBe(1.0);
});

// ── Isolation between employees ───────────────────────────────────────────

test('employee cannot influence another employees eligibility via extra request fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employeeA = User::factory()->create();
    $employeeB = User::factory()->create();
    elSetSalary($employeeA, $admin, 30000, '2026-01-01');
    elSetSalary($employeeB, $admin, 30000, '2026-01-01');
    elMarkPresent($employeeA, '2026-01-05');
    elMarkPresent($employeeB, '2026-01-05');

    Carbon::setTestNow(Carbon::parse('2026-01-05 10:00:00', 'Asia/Kolkata'));

    // employeeA is authenticated; attempting to smuggle another user's id
    // must have no effect — the advance is always created for auth()->user().
    $this->actingAs($employeeA->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 1,
        'user_id'          => $employeeB->id,
        'employee_id'      => $employeeB->id,
    ])->assertRedirect();

    $advance = EmployeeAdvance::first();
    expect($advance->user_id)->toBe($employeeA->id);
});

// ── Mid-period salary change: segmented eligibility (not blocked) ────────

test('eligibility segments earned salary across a mid-period salary change (user\'s example)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 15000, '2026-08-01');
    elSetSalary($employee, $admin, 20000, '2026-08-16');

    // Mark every day of August up to the 31st present; no weekly-off
    // configured in this suite, so all 31 days are applicable working days.
    for ($d = Carbon::parse('2026-08-01'); $d->lte(Carbon::parse('2026-08-31')); $d->addDay()) {
        elMarkPresent($employee, $d->toDateString());
    }

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-08-31'));

    // applicable_working_days = 31 calendar days minus 5 Sundays (Aug 2, 9,
    // 16, 23, 30 in 2026) = 26.
    // Segment 1: Aug 1-15 (15 days, minus Sundays Aug 2 & 9 = 13 working) @ 15000/26
    // Segment 2: Aug 16-31 (16 days, minus Sundays Aug 16, 23 & 30 = 13 working) @ 20000/26
    $expectedEarned = round((13 * 15000 / 26) + (13 * 20000 / 26), 2);
    expect($result['unavailable_reason'])->toBeNull();
    expect($result['salary_configured'])->toBeTrue();
    expect($result['salary_change_during_period'])->toBeTrue();
    expect($result['earned_salary'])->toBe($expectedEarned);
    expect($result['eligible_advance_amount'])->toBe($expectedEarned);
});

test('eligibility salary_change_during_period is false when salary is unchanged for the whole period', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 30000, '2026-01-01');
    elMarkPresent($employee, '2026-08-05');

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-08-10'));

    expect($result['unavailable_reason'])->toBeNull();
    expect($result['salary_change_during_period'])->toBeFalse();
});

test('eligibility treats a salary change effective exactly on the last evaluated day as one day at the new rate', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 10000, '2026-08-01');
    elSetSalary($employee, $admin, 40000, '2026-08-10'); // last day of the evaluated (partial) period

    for ($d = Carbon::parse('2026-08-01'); $d->lte(Carbon::parse('2026-08-10')); $d->addDay()) {
        elMarkPresent($employee, $d->toDateString());
    }

    $result = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-08-10'));

    // applicable_working_days for [Aug 1, Aug 10] = 8 (Aug 2 & 9 are
    // Sundays, the default weekly-off day). Aug 1-9 minus those 2 Sundays
    // = 7 working days @ 10000/8; Aug 10 (Monday, 1 working day) @ 40000/8.
    $expected = round((7 * 10000 / 8) + (1 * 40000 / 8), 2);
    expect($result['earned_salary'])->toBe($expected);
});

test('payroll and advance eligibility agree on the same segmented scenario', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 15000, '2026-08-01');
    elSetSalary($employee, $admin, 20000, '2026-08-16');

    for ($d = Carbon::parse('2026-08-01'); $d->lte(Carbon::parse('2026-08-20')); $d->addDay()) {
        elMarkPresent($employee, $d->toDateString());
    }

    $eligibility = app(AdvanceEligibilityService::class)->evaluate($employee, Carbon::parse('2026-08-20'));
    $payable = app(\App\Services\MonthlyPayableService::class)->calculate(
        $employee, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-20'),
    );

    expect($eligibility['earned_salary'])->toBe($payable['payable_salary']);
    expect($eligibility['payable_days'])->toBe($payable['payable_days']);
});

// ── Manager/admin unaffected ──────────────────────────────────────────────

test('admin approval is not constrained by the employee eligibility formula', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    elSetSalary($employee, $admin, 10000, '2026-01-01'); // small salary, small eligibility

    $advance = new EmployeeAdvance();
    $advance->fill(['user_id' => $employee->id, 'origin' => 'employee_request', 'requested_amount' => 500]);
    $advance->forceFill(['created_by' => $employee->id]);
    $advance->save();

    // Admin approves an amount that would exceed the employee's own
    // eligibility cap — unchanged existing behavior, since the eligibility
    // formula added here only gates the employee's own request submission.
    $this->actingAs($admin)->patch(route('admin.advances.approve', $advance), [
        'approved_amount' => 999999,
    ])->assertRedirect();

    expect((float) $advance->fresh()->approved_amount)->toBe(999999.0);
});
