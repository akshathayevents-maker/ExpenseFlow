<?php

use App\Models\AdvanceTransaction;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeSalary;
use App\Models\User;
use App\Services\MonthlyPayableService;
use Carbon\Carbon;

/**
 * Regression + coverage suite for the "approved_amount vs calculated_amount"
 * bug found in a real approved-with-override overtime record (₹216.36
 * calculated vs ₹500.00 approved), and for the new Admin monthly payroll
 * page built on top of the fixed MonthlyPayableService.
 *
 * Tests 9 and 10 (immutability of historical approved OT against a later
 * salary change / multiplier config change) already exist and are NOT
 * duplicated here — see:
 *   - tests/Feature/EmployeeSelfService/OvertimeWorkflowTest.php:490-525
 *     ("a later salary change does not alter an already-approved OT's
 *     frozen snapshot")
 *   - tests/Feature/EmployeeSelfService/OvertimeCalculationServiceTest.php:219-232
 *     ("a global settings change after an approval does not alter an
 *     already-persisted frozen snapshot")
 */
function mpSetSalary(User $user, float $amount, string $from = '2026-01-01'): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => $amount, 'effective_from' => $from]);
    $salary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $salary->save();
}

function mpMakeOt(User $user, string $date, string $status, ?float $calculated = null, ?float $approved = null, bool $override = false): EmployeeOvertime
{
    $creator = User::factory()->create(['role' => 'admin']);
    $ot = new EmployeeOvertime();
    $ot->fill(['user_id' => $user->id, 'ot_date' => $date, 'hours' => 2, 'category' => 'weekday', 'reason' => 'x']);
    $attrs = ['origin' => 'admin_recorded', 'created_by' => $creator->id, 'request_status' => $status];
    if ($calculated !== null) {
        $attrs['hourly_rate_snapshot'] = 100;
        $attrs['rate_multiplier'] = 1.5;
        $attrs['calculated_amount'] = $calculated;
    }
    if ($approved !== null) {
        $attrs['approved_amount'] = $approved;
        $attrs['used_manual_override'] = $override;
    }
    $ot->forceFill($attrs);
    $ot->save();

    return $ot;
}

// ── 1: Approved normal OT (no override) contributes approved_amount ───────
test('approved OT without override contributes approved_amount which equals calculated_amount', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'approved', 300.00, 300.00, false);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(300.0);
});

// ── 2: CORE REGRESSION — manual-override OT contributes approved_amount, not calculated_amount ──
test('approved manual-override OT contributes approved_amount NOT calculated_amount (216.36 vs 500 bug)', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'approved', 216.36, 500.00, true);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(500.0)
        ->and($result['approved_overtime_amount'])->not->toBe(216.36);
});

// ── 3-5: Pending/Rejected/Cancelled OT contribute zero ─────────────────────
test('pending OT contributes zero to monthly payable', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'pending');

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(0.0);
});

test('rejected OT contributes zero to monthly payable', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'rejected');

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(0.0);
});

test('cancelled OT contributes zero to monthly payable', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'cancelled');

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(0.0);
});

// ── 6: Multiple approved OT records in the same month sum correctly ───────
test('multiple approved OT records in the same month sum by approved_amount', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-05', 'approved', 216.36, 500.00, true);
    mpMakeOt($user, '2026-08-15', 'approved', 300.00, 300.00, false);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(800.0);
});

// ── 7: OT from a different month is excluded ───────────────────────────────
test('OT from a different month is excluded from the calculation', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-07-20', 'approved', 300.00, 300.00, false);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(0.0);
});

// ── 8: OT belonging to a different employee is excluded ────────────────────
test('OT belonging to a different employee is excluded', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($other, '2026-08-10', 'approved', 300.00, 300.00, false);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['approved_overtime_amount'])->toBe(0.0);
});

// ── 11: Admin OT listing shows approved_amount ─────────────────────────────
test('admin overtime listing displays approved_amount for an approved manual-override record', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    mpMakeOt($user, '2026-08-10', 'approved', 216.36, 500.00, true);

    $this->actingAs($admin)->get(route('admin.overtime.index'))
        ->assertOk()
        ->assertSee('500.00')
        ->assertDontSee('216.36');
});

// ── 12: Employee approved OT view shows approved_amount ────────────────────
test('employee overtime index displays approved_amount for an approved manual-override record', function () {
    $user = User::factory()->create();
    mpMakeOt($user, '2026-08-10', 'approved', 216.36, 500.00, true);
    // Attendance-first gate — mark today's attendance so the employee.overtime.index
    // request is not redirected. Same pattern as OvertimeWorkflowTest::giveOtSalary().
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $this->actingAs($user)->get(route('employee.overtime.index'))
        ->assertOk()
        ->assertSee('500.00')
        ->assertDontSee('216.36');
});

// ── 13: Monthly payable calculation uses approved_amount (fixed service) ──
test('MonthlyPayableService net_payable reflects approved_amount, not calculated_amount', function () {
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'approved', 216.36, 500.00, true);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['net_payable'])->toBe(round($result['payable_salary'] + 500.0, 2));
});

// ── 14: LOP deduction in the payroll view uses existing LOP logic (payable_days) ──
test('admin payroll detail page surfaces payable_days exactly as computed by the existing PayableDaysCalculator chain', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    mpSetSalary($user, 30000);

    $response = $this->actingAs($admin)->get(route('admin.payroll.show', ['employee' => $user->id, 'month' => '2026-08']))
        ->assertOk();

    $expected = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
    $response->assertSee(number_format($expected['payable_salary'], 2));
});

/**
 * Helper: create a paid advance with a given outstanding balance, without
 * touching EmployeeAdvanceService (these tests only need the resulting row
 * shape, not the workflow that produced it).
 */
function mpMakePaidAdvance(User $user, User $admin, float $original, float $outstanding): EmployeeAdvance
{
    $advance = new EmployeeAdvance();
    $advance->fill(['user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => $original]);
    $advance->forceFill([
        'created_by' => $user->id, 'request_status' => 'approved', 'approved_amount' => $original,
        'approved_by' => $admin->id, 'approved_at' => now(), 'payment_status' => 'paid',
        'paid_at' => now(), 'paid_by' => $admin->id, 'original_amount' => $original, 'outstanding_amount' => $outstanding,
    ]);
    $advance->save();

    return $advance;
}

// ── 9: CORE FIX — a real outstanding balance is NOT deducted from a month with no recorded repayment ──
test('advance_deduction_amount is zero when no repayment was recorded in the month, even with a large outstanding balance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakePaidAdvance($user, $admin, 5000, 2000);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['advance_deduction_amount'])->toBe(0.0);
});

// ── 10: The outstanding balance is still surfaced separately as informational data ──
test('advance_outstanding_balance is returned as a separate informational field', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakePaidAdvance($user, $admin, 5000, 2000);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['advance_outstanding_balance'])->toBe(2000.0)
        ->and($result['advance_deduction_amount'])->not->toBe($result['advance_outstanding_balance']);

    $this->actingAs($admin)->get(route('admin.payroll.show', ['employee' => $user->id, 'month' => '2026-08']))
        ->assertOk()
        ->assertSee('Advance Outstanding')
        ->assertSee('2,000.00');
});

// ── 11: recovery transactions are NEVER treated as a payroll deduction — a manual repayment has no automated tie to any payroll run ──
test('advance_deduction_amount stays zero regardless of recovery transactions dated within the requested month', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    $advance = mpMakePaidAdvance($user, $admin, 5000, 3000);

    // Advance disbursement transaction — never a deduction.
    AdvanceTransaction::create([
        'employee_advance_id' => $advance->id, 'user_id' => $user->id,
        'transaction_date' => '2026-07-01', 'type' => 'advance', 'amount' => 5000,
        'balance_after' => 5000, 'created_by' => $admin->id,
    ]);
    // Recovery inside the requested month — a manual ledger entry, not a
    // payroll deduction (see MonthlyPayableService class docblock: recovery
    // transactions have no enforced tie to any payroll process anywhere in
    // this codebase), so it must NOT reduce net_payable.
    AdvanceTransaction::create([
        'employee_advance_id' => $advance->id, 'user_id' => $user->id,
        'transaction_date' => '2026-08-12', 'type' => 'recovery', 'amount' => 1000,
        'balance_after' => 4000, 'created_by' => $admin->id,
    ]);
    // Another recovery in a different month — also must not count.
    AdvanceTransaction::create([
        'employee_advance_id' => $advance->id, 'user_id' => $user->id,
        'transaction_date' => '2026-09-05', 'type' => 'recovery', 'amount' => 1000,
        'balance_after' => 3000, 'created_by' => $admin->id,
    ]);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['advance_deduction_amount'])->toBe(0.0);
});

// ── 12: Net payable combines payable salary and approved overtime only — advance recoveries never reduce it (no payroll-deduction concept exists) ──
test('net payable combines payable salary and approved overtime, with advance_deduction_amount always zero', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    mpSetSalary($user, 30000);
    mpMakeOt($user, '2026-08-10', 'approved', 216.36, 500.00, true);
    $advance = mpMakePaidAdvance($user, $admin, 5000, 1500);
    AdvanceTransaction::create([
        'employee_advance_id' => $advance->id, 'user_id' => $user->id,
        'transaction_date' => '2026-08-20', 'type' => 'recovery', 'amount' => 700,
        'balance_after' => 1500, 'created_by' => $admin->id,
    ]);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($result['advance_deduction_amount'])->toBe(0.0)
        ->and($result['advance_deduction_amount'])->not->toBe($result['advance_outstanding_balance']);

    $expectedNet = round($result['payable_salary'] + 500.0 - 0.0, 2);
    expect($result['net_payable'])->toBe($expectedNet);
});

// ── Admin monthly payroll page smoke tests (mobile + desktop covered via Chrome verification) ──
test('admin monthly payroll index page loads and lists employees with their net payable', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    mpSetSalary($user, 30000);

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee($user->name);
});
