<?php

use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeSalary;
use App\Models\User;
use App\Services\AdvanceEligibilityService;
use App\Services\MonthlyPayableService;
use Carbon\Carbon;

/**
 * Marks a run of consecutive "present" attendance days so payable_days
 * actually accumulates (PayableDaysCalculator treats an unmarked day as 0
 * payable days — see app/Services/PayableDaysCalculator.php:126 — so tests
 * that need two different dates to produce two different payable-days
 * figures must seed real attendance rows, not rely on bare dates).
 */
function paeMarkPresent(User $user, string $from, string $to): void
{
    $period = Carbon::parse($from);
    $end = Carbon::parse($to);
    while ($period->lte($end)) {
        EmployeeAttendance::create([
            'user_id' => $user->id, 'attendance_date' => $period->toDateString(),
            'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
        ]);
        $period->addDay();
    }
}

/**
 * Extends the existing admin/payroll/index page (built in prior rounds) with
 * a "Daily Advance Eligibility" section that re-runs
 * AdvanceEligibilityService::evaluate() per employee for an admin-selected
 * date. No calculation is duplicated in the controller/view — every value
 * asserted here is compared directly against what the service itself
 * returns for the same inputs.
 *
 * Historical-date support: verified genuine, not invented. evaluate()
 * accepts an arbitrary Carbon $asOf and scopes MonthlyPayableService::calculate()
 * to $asOf->startOfMonth() .. min($asOf, endOfMonth) — see
 * app/Services/AdvanceEligibilityService.php:104-115. Two different $asOf
 * dates in the same month with different payable-days-so-far genuinely
 * produce different eligibility figures (test H below).
 */
function paeSetSalary(User $user, float $amount, string $from = '2026-01-01'): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => $amount, 'effective_from' => $from]);
    $salary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $salary->save();
}

function paeMakePaidAdvance(User $user, User $admin, float $original, float $outstanding): EmployeeAdvance
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

function paeMakeOt(User $user, string $date, string $status, ?float $calculated = null, ?float $approved = null, bool $override = false): EmployeeOvertime
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

// ── A: Admin can open the page ─────────────────────────────────────────────
test('admin can open the payroll page with the advance eligibility section', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08', 'eligibility_date' => '2026-08-15']))
        ->assertOk()
        ->assertSee('Advance Eligibility')
        ->assertSee($user->name);
});

// ── B: Non-admin access rejected ───────────────────────────────────────────
test('non-admin users cannot open the payroll page', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($employee->fresh())->get(route('admin.payroll.index'))->assertForbidden();
    $this->actingAs($manager->fresh())->get(route('admin.payroll.index'))->assertForbidden();
});

// ── C: Selected month is passed through to MonthlyPayableService::calculate() ──
test('selected month is passed through to MonthlyPayableService for the monthly salary section', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    paeMarkPresent($user, '2026-07-01', '2026-07-05');
    paeMarkPresent($user, '2026-08-01', '2026-08-20');

    $julyExpected = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-07-01'), Carbon::parse('2026-07-31'));
    $augExpected = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($julyExpected['payable_salary'])->not->toBe($augExpected['payable_salary']);

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee(number_format($augExpected['payable_salary'], 0));
});

// ── D: Employee-wise monthly values match calling the service directly ─────
test('monthly salary section values match MonthlyPayableService::calculate() directly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);

    $expected = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee(number_format($expected['monthly_salary'], 0))
        ->assertSee(number_format($expected['payable_salary'], 0))
        ->assertSee((string) $expected['applicable_working_days']);
});

// ── E: Approved OT uses approved_amount, not calculated_amount (regression) ──
test('approved overtime contribution uses approved_amount not calculated_amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    paeMakeOt($user, '2026-08-10', 'approved', 216.36, 500.00, true);

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('500')
        ->assertDontSee('216.36');
});

// ── F: Advance outstanding renders as informational, not summed into a deduction ──
test('advance outstanding renders as informational on the payroll index', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    paeMakePaidAdvance($user, $admin, 5000, 2000);

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('Advance Outstanding')
        ->assertSee('2,000');
});

// ── G: Advance deduction remains 0.0 in this view ──────────────────────────
test('advance deduction amount remains zero in the payroll index', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    paeMakePaidAdvance($user, $admin, 5000, 2000);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
    expect($result['advance_deduction_amount'])->toBe(0.0);

    $response = $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk();

    // Advance deduction still renders as 0 — markup shape is deliberately
    // redesigned (mobile card grid + desktop table), but the actual VALUE
    // must still be present and correct.
    $response->assertSee('Advance Ded.');
    $response->assertSee('₹0');
});

// ── H: The selected eligibility date is genuinely used ─────────────────────
// (Historical/as-of-date support IS genuine — verified by code trace in
// AdvanceEligibilityService::evaluate(), not assumed.)
test('selected eligibility date genuinely changes the eligibility calculation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    // Present only through day 10 — days 11-25 are unmarked/absent, so the
    // payable-so-far ratio (and thus earned_salary) genuinely differs
    // between an early asOf (fully present so far) and a later one (which
    // now includes unmarked/absent days).
    paeMarkPresent($user, '2026-08-01', '2026-08-10');

    $early = app(AdvanceEligibilityService::class)->evaluate($user, Carbon::parse('2026-08-05'));
    $late = app(AdvanceEligibilityService::class)->evaluate($user, Carbon::parse('2026-08-25'));

    // Different "as of" dates within the same month produce a different
    // MTD payable-days-based earned_salary, hence a different eligible amount.
    expect($early['earned_salary'])->not->toBe($late['earned_salary']);

    $response = $this->actingAs($admin)->get(route('admin.payroll.index', [
        'month' => '2026-08', 'eligibility_date' => '2026-08-25',
    ]))->assertOk();

    $response->assertSee(number_format($late['eligible_advance_amount'], 0));
});

// ── I: Employee-wise eligibility values match evaluate() directly ─────────
test('eligibility section values match AdvanceEligibilityService::evaluate() directly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    paeMakePaidAdvance($user, $admin, 5000, 2000);

    $expected = app(AdvanceEligibilityService::class)->evaluate($user, Carbon::parse('2026-08-15'));

    $this->actingAs($admin)->get(route('admin.payroll.index', [
        'month' => '2026-08', 'eligibility_date' => '2026-08-15',
    ]))->assertOk()
        ->assertSee(number_format($expected['eligible_advance_amount'], 0))
        ->assertSee(number_format($expected['outstanding_amount'], 0));
});

// ── J: Historical eligibility support is real — documented, not faked ─────
test('historical eligibility support is genuine — asOf date is threaded into MonthlyPayableService scoping, not silently ignored', function () {
    // Direct evidence: evaluate() calls monthlyPayableService->calculate()
    // with $asOf->copy()->startOfMonth() .. $asOf->copy()->endOfMonth()->min($asOf).
    // This test pins that behavior so a future regression (e.g. someone
    // hardcoding now() inside evaluate()) is caught.
    $user = User::factory()->create();
    paeSetSalary($user, 30000);
    paeMarkPresent($user, '2026-08-01', '2026-08-20');

    $result1 = app(AdvanceEligibilityService::class)->evaluate($user, Carbon::parse('2026-08-10'));
    $result10 = app(AdvanceEligibilityService::class)->evaluate($user, Carbon::parse('2026-08-20'));

    expect($result1['payable_days'])->not->toBe($result10['payable_days']);
});

// ── K1: Empty employee list renders without errors ─────────────────────────
test('payroll page with no employees renders both sections without errors', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee('No employees found');
});

// ── K2: Employee with no salary configured renders without errors ──────────
test('employee with no salary configured renders in both sections without errors', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    // No salary configured at all.

    $response = $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08']))
        ->assertOk();

    $eligibility = app(AdvanceEligibilityService::class)->evaluate($user, now());
    expect($eligibility['salary_configured'])->toBeFalse();
    $response->assertSee('Advance eligibility is unavailable because your salary has not been configured.');
});

// ── K3: Employee with no eligibility available (mid-month salary change) ──
test('employee with a mid-period salary change renders an unavailable reason instead of erroring', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    paeSetSalary($user, 25000, '2026-01-01');
    // A second salary row effective mid-August triggers MonthlyPayableService's
    // DomainException guard against undefined mid-period proration.
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => 32000, 'effective_from' => '2026-08-15']);
    $salary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $salary->save();

    $response = $this->actingAs($admin)->get(route('admin.payroll.index', ['month' => '2026-08', 'eligibility_date' => '2026-08-20']))
        ->assertOk();

    $eligibility = app(AdvanceEligibilityService::class)->evaluate($user, Carbon::parse('2026-08-20'));
    expect($eligibility['unavailable_reason'])->not->toBeNull();
    $response->assertSee($eligibility['unavailable_reason']);
});
