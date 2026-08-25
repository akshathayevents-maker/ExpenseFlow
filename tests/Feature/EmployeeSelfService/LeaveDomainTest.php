<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeaveAllocation;
use App\Models\EmployeeLeaveLedger;
use App\Models\EmployeeLeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveAllocationService;
use App\Services\LeaveBalanceService;
use App\Services\LeaveService;
use App\Services\MonthlyPayableService;
use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeSalaryService;
use Carbon\Carbon;

function ldSetSalary(User $employee, User $admin, float $amount, string $effectiveFrom): void
{
    Illuminate\Support\Facades\Auth::login($admin);
    app(EmployeeSalaryService::class)->setSalary($employee, $amount, Carbon::parse($effectiveFrom), $admin);
}

function ldLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true, 'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function ldRequestAs(User $actor, LeaveType $type, string $start, string $end, float $paid, float $lop = 0, string $status = 'pending'): LeaveRequest
{
    $leaveRequest = new LeaveRequest();
    $leaveRequest->forceFill([
        'user_id' => $actor->id, 'leave_type_id' => $type->id,
        'start_date' => $start, 'end_date' => $end,
        'days_requested' => $paid + $lop, 'paid_leave_days' => $paid, 'lop_days' => $lop,
        'reason' => 'x', 'status' => $status,
    ]);
    $leaveRequest->save();

    return $leaveRequest;
}

function ldPolicy(User $user, LeaveType $type, User $admin, array $attrs = []): EmployeeLeavePolicy
{
    return EmployeeLeavePolicy::create(array_merge([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'annual_entitlement' => 12, 'allocation_mode' => 'yearly',
        'effective_from' => '2026-01-01', 'is_active' => true, 'created_by' => $admin->id,
    ], $attrs));
}

// ── Employee-specific policies ────────────────────────────────────────────

test('different employees can have different leave policies for the same leave type', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $a = User::factory()->create();
    $b = User::factory()->create();
    $type = ldLeaveType();
    ldPolicy($a, $type, $admin, ['annual_entitlement' => 12]);
    ldPolicy($b, $type, $admin, ['annual_entitlement' => 18]);

    expect((float) EmployeeLeavePolicy::currentFor($a, $type, Carbon::parse('2026-06-01'))->annual_entitlement)->toBe(12.0);
    expect((float) EmployeeLeavePolicy::currentFor($b, $type, Carbon::parse('2026-06-01'))->annual_entitlement)->toBe(18.0);
});

// ── Allocation: annual ────────────────────────────────────────────────────

test('annual allocation is credited at the start of the leave year, in full, for an employee already employed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, ['annual_entitlement' => 18]);

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-01-01'));

    expect($created)->toHaveCount(1);
    expect((float) $created[0]->allocated_amount)->toBe(18.0);
    expect($created[0]->period_year)->toBe(2026);
    expect($created[0]->period_month)->toBe(0);
});

test('annual allocation is pro-rated for an employee joining mid-year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2026-07-01']);
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, ['annual_entitlement' => 18, 'effective_from' => '2026-01-01']);

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-07-01'));

    expect($created)->toHaveCount(1);
    // 2026 is not a leap year: 365 days total; Jul 1 -> Dec 31 inclusive = 184 days.
    $expected = round(18 * 184 / 365, 1);
    expect((float) $created[0]->allocated_amount)->toBe($expected);
    expect($expected)->toBeLessThan(18.0)->toBeGreaterThan(0.0);
});

test('an employee joining after the leave year ends gets no allocation for that year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2027-01-01']);
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, ['annual_entitlement' => 18, 'effective_from' => '2026-01-01']);

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-12-31'));

    expect($created)->toHaveCount(0);
});

// ── Allocation: monthly (completed periods only) ─────────────────────────

test('monthly allocation is only credited after the month completes, never on day 1', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2026-01-01']);
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, [
        'annual_entitlement' => 12, 'allocation_mode' => 'monthly_accrual',
        'monthly_accrual_amount' => 1, 'effective_from' => '2026-01-01',
    ]);

    // Mid-August: Jan-Jul have completed and are allocated; August itself must not be.
    $mid = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-08-15'));
    expect(collect($mid)->pluck('period_month')->all())->not->toContain(8);

    // September 1 — August has now fully completed and becomes eligible.
    $afterCompletion = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-09-01'));
    expect(collect($afterCompletion)->pluck('period_month')->all())->toContain(8);
});

test('monthly allocation running twice for the same date never double-credits (idempotent)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2026-01-01']);
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, [
        'allocation_mode' => 'monthly_accrual', 'monthly_accrual_amount' => 1, 'effective_from' => '2026-01-01',
    ]);
    $service = app(LeaveAllocationService::class);

    $first = $service->generateForUser($user, Carbon::parse('2026-03-01'));
    $countAfterFirst = EmployeeLeaveAllocation::where('user_id', $user->id)->count();

    $second = $service->generateForUser($user, Carbon::parse('2026-03-01'));
    $countAfterSecond = EmployeeLeaveAllocation::where('user_id', $user->id)->count();

    expect($first)->not->toBeEmpty();
    expect($second)->toBeEmpty(); // nothing new — already allocated
    expect($countAfterSecond)->toBe($countAfterFirst);
});

test('an employee does not get a monthly allocation for a month they did not fully work', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2026-03-15']); // joined mid-March
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, [
        'allocation_mode' => 'monthly_accrual', 'monthly_accrual_amount' => 1, 'effective_from' => '2026-01-01',
    ]);

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-05-01'));

    // March (joined mid-month, not fully employed for it) must be skipped;
    // only April (the first fully-employed month) is eligible by May 1.
    $months = collect($created)->pluck('period_month')->all();
    expect($months)->not->toContain(3);
    expect($months)->toContain(4);
});

// ── Allocation: quarterly ─────────────────────────────────────────────────

test('quarterly allocation is only credited after the quarter completes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2026-01-01']);
    $type = ldLeaveType();
    ldPolicy($user, $type, $admin, [
        'allocation_mode' => 'quarterly_accrual', 'monthly_accrual_amount' => 3, 'effective_from' => '2026-01-01',
    ]);

    $beforeQ1Ends = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-03-15'));
    expect($beforeQ1Ends)->toHaveCount(0);

    $afterQ1Ends = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-04-01'));
    expect($afterQ1Ends)->toHaveCount(1);
    expect((float) $afterQ1Ends[0]->allocated_amount)->toBe(3.0);
    expect($afterQ1Ends[0]->period_month)->toBe(3);
});

// ── Balance: allocated/used/pending/available ─────────────────────────────

test('allocation increases balance and pending leave reserves it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();

    EmployeeLeaveLedger::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01',
        'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id,
    ]);

    $balanceService = app(LeaveBalanceService::class);
    expect($balanceService->availableFor($user, $type))->toBe(10.0);

    ldRequestAs($user, $type, '2026-02-01', '2026-02-01', 4);

    $balance = $balanceService->balanceFor($user, $type);
    expect($balance['allocated'])->toBe(10.0);
    expect($balance['pending'])->toBe(4.0);
    expect($balance['available'])->toBe(6.0);
});

test('two pending requests never reserve the same balance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01',
        'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id,
    ]);

    ldRequestAs($user, $type, '2026-02-01', '2026-02-01', 4);
    ldRequestAs($user, $type, '2026-02-05', '2026-02-05', 4);

    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(2.0); // 10 - 4 - 4
});

test('rejected leave releases the pending reservation without any ledger usage entry', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id]);

    $leaveRequest = ldRequestAs($user, $type, '2026-02-02', '2026-02-02', 1);
    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(9.0);

    Illuminate\Support\Facades\Auth::login($admin);
    app(LeaveService::class)->reject($leaveRequest, $admin);

    expect($leaveRequest->fresh()->status)->toBe('rejected');
    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(10.0); // reservation released
    expect(EmployeeLeaveLedger::where('user_id', $user->id)->where('type', 'usage')->exists())->toBeFalse();
});

// ── LOP split ──────────────────────────────────────────────────────────────

test('requesting more than the available paid balance requires explicit LOP confirmation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 2, 'created_by' => $admin->id]);

    expect(fn () => app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-06', // 5 weekdays (Mon-Fri)
        'reason' => 'x',
    ]))->toThrow(Illuminate\Validation\ValidationException::class);

    expect(LeaveRequest::count())->toBe(0);
});

test('explicit LOP confirmation splits the request into paid_leave_days and lop_days correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 2, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-06', // Mon-Fri = 5 days
        'reason' => 'x', 'lop_confirmed' => true,
    ]);

    expect((float) $leaveRequest->days_requested)->toBe(5.0);
    expect((float) $leaveRequest->paid_leave_days)->toBe(2.0);
    expect((float) $leaveRequest->lop_days)->toBe(3.0);
    expect($leaveRequest->lop_confirmed)->toBeTrue();
});

test('a fully-paid request needs no LOP confirmation at all', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-02',
        'reason' => 'x',
    ]);

    expect((float) $leaveRequest->paid_leave_days)->toBe(1.0);
    expect((float) $leaveRequest->lop_days)->toBe(0.0);
});

// ── Approval / attendance integration ────────────────────────────────────

test('approving a fully-paid leave request writes a usage ledger entry and leave attendance rows', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-03', // Mon-Tue
        'reason' => 'x',
    ]);

    Illuminate\Support\Facades\Auth::login($admin);

    app(LeaveService::class)->approve($leaveRequest, $admin);

    expect($leaveRequest->fresh()->status)->toBe('approved');
    expect((float) EmployeeLeaveLedger::where('user_id', $user->id)->where('type', 'usage')->sum('amount'))->toBe(-2.0);

    $rows = EmployeeAttendance::where('user_id', $user->id)
        ->whereDate('attendance_date', '>=', '2026-02-02')->whereDate('attendance_date', '<=', '2026-02-03')
        ->get();
    expect($rows)->toHaveCount(2);
    expect($rows->pluck('status')->unique()->all())->toBe(['leave']);
    expect($rows->pluck('source')->unique()->all())->toBe(['leave_approval']);
    expect($rows->pluck('leave_request_id')->unique()->all())->toBe([$leaveRequest->id]);
});

test('approving a split paid+LOP leave request writes leave rows for paid days and lop rows for LOP days', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 2, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-06', // Mon-Fri
        'reason' => 'x', 'lop_confirmed' => true,
    ]);

    Illuminate\Support\Facades\Auth::login($admin);

    app(LeaveService::class)->approve($leaveRequest, $admin);

    $rows = EmployeeAttendance::where('user_id', $user->id)
        ->whereDate('attendance_date', '>=', '2026-02-02')
        ->whereDate('attendance_date', '<=', '2026-02-06')
        ->orderBy('attendance_date')->get();

    expect($rows)->toHaveCount(5);
    // Paid days assigned chronologically first (Mon, Tue), then LOP (Wed, Thu, Fri).
    expect($rows->pluck('status')->all())->toBe(['leave', 'leave', 'lop', 'lop', 'lop']);
});

test('rejecting a leave request never writes attendance or usage ledger', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-02', 'reason' => 'x',
    ]);
    Illuminate\Support\Facades\Auth::login($admin);
    app(LeaveService::class)->reject($leaveRequest, $admin);

    expect(EmployeeAttendance::where('user_id', $user->id)->count())->toBe(0);
});

test('cancelling an approved leave request reverses the ledger and removes the attendance rows', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-03', 'reason' => 'x',
    ]);
    Illuminate\Support\Facades\Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);
    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(8.0);

    Illuminate\Support\Facades\Auth::login($admin);

    app(LeaveService::class)->cancel($leaveRequest, $admin);

    expect($leaveRequest->fresh()->status)->toBe('cancelled');
    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(10.0); // fully restored
    expect(EmployeeAttendance::where('leave_request_id', $leaveRequest->id)->count())->toBe(0);
});

test('approval refuses to overwrite a conflicting attendance record on the same date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 10, 'created_by' => $admin->id]);
    EmployeeAttendance::create(['user_id' => $user->id, 'attendance_date' => '2026-02-02', 'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self']);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-02', 'reason' => 'x',
    ]);

    Illuminate\Support\Facades\Auth::login($admin);

    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))
        ->toThrow(Illuminate\Validation\ValidationException::class);

    expect($leaveRequest->fresh()->status)->toBe('pending'); // never approved — transaction rolled back
});

// ── Payable days / salary / advance integration ──────────────────────────

test('LOP days reduce payable days while paid leave days do not', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 2, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($user);

    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-02', 'end_date' => '2026-02-06', // Mon-Fri, 5 days
        'reason' => 'x', 'lop_confirmed' => true,
    ]);
    Illuminate\Support\Facades\Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $payableDays = app(App\Services\PayableDaysCalculator::class)
        ->payableDaysSoFar($user, Carbon::parse('2026-02-02'), Carbon::parse('2026-02-06'));

    expect($payableDays)->toBe(2.0); // 2 paid leave days count, 3 LOP days do not
});

test('monthly payable salary is reduced by LOP days via the existing MonthlyPayableService chain, without any leave-specific logic inside it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    ldSetSalary($user, $admin, 30000, '2026-01-01');
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 0, 'created_by' => $admin->id]);

    // Mark present for every weekday 1-6 Feb except one taken as full LOP.
    foreach (['2026-02-02', '2026-02-03', '2026-02-04', '2026-02-05'] as $date) {
        EmployeeAttendance::create(['user_id' => $user->id, 'attendance_date' => $date, 'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self']);
    }
    Illuminate\Support\Facades\Auth::login($user);
    $leaveRequest = app(LeaveService::class)->createRequest($user, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-06', 'end_date' => '2026-02-06', // Friday, full LOP (0 balance)
        'reason' => 'x', 'lop_confirmed' => true,
    ]);
    Illuminate\Support\Facades\Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $result = app(MonthlyPayableService::class)->calculate($user, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-06'));

    // applicable working days Feb 1-6 (Sun 1 is weekly off) = 5; payable = 4 (present) + 0 (LOP Friday) = 4.
    expect($result['applicable_working_days'])->toBe(5);
    expect($result['payable_days'])->toBe(4.0);
    expect($result['payable_salary'])->toBe(round($result['daily_salary'] * 4, 2));
});

test('advance eligibility is reduced when LOP reduces earned salary, via the existing chain with no duplicated leave logic', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = ldLeaveType();

    // Two identically-paid, identically-attended employees over the same
    // Feb 1-3 window (same denominator of applicable working days) — the
    // only difference is that userB's Feb 3 is an approved LOP day instead
    // of a worked day. Comparing them isolates LOP's effect on earned
    // salary from the (unrelated) fact that AdvanceEligibilityService's
    // daily rate is itself denominated over the evaluated range.
    $userA = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $userB = User::factory()->create(['employment_start_date' => '2020-01-01']);
    ldSetSalary($userA, $admin, 30000, '2026-01-01');
    ldSetSalary($userB, $admin, 30000, '2026-01-01');
    EmployeeLeaveLedger::create(['user_id' => $userB->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 0, 'created_by' => $admin->id]);

    foreach ([$userA, $userB] as $u) {
        EmployeeAttendance::create(['user_id' => $u->id, 'attendance_date' => '2026-02-02', 'status' => 'present', 'marked_by' => $u->id, 'marked_at' => now(), 'source' => 'self']);
    }
    EmployeeAttendance::create(['user_id' => $userA->id, 'attendance_date' => '2026-02-03', 'status' => 'present', 'marked_by' => $userA->id, 'marked_at' => now(), 'source' => 'self']);

    Illuminate\Support\Facades\Auth::login($userB);
    $leaveRequest = app(LeaveService::class)->createRequest($userB, [
        'leave_type_id' => $type->id, 'start_date' => '2026-02-03', 'end_date' => '2026-02-03',
        'reason' => 'x', 'lop_confirmed' => true,
    ]);
    Illuminate\Support\Facades\Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $withoutLop = app(AdvanceEligibilityService::class)->evaluate($userA, Carbon::parse('2026-02-03'));
    $withLop = app(AdvanceEligibilityService::class)->evaluate($userB, Carbon::parse('2026-02-03'));

    // Same range, same denominator — userB's LOP day earns strictly less
    // than userA's fully-worked day, proving LOP never leaks into
    // eligibility as if it were paid.
    expect($withLop['payable_days'])->toBeLessThan($withoutLop['payable_days']);
    expect($withLop['earned_salary'])->toBeLessThan($withoutLop['earned_salary']);
});

// ── Carry forward ──────────────────────────────────────────────────────────

test('carry-forward is capped at max_carry_forward and posted as its own ledger entry', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2025-01-01']);
    $type = ldLeaveType(['allow_carry_forward' => true, 'max_carry_forward' => 3]);
    ldPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2025-01-01']);

    // 2025: 12 allocated, 7 used -> 5 unused at year end, capped to 3.
    app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2025-01-01'));
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2025-06-01', 'type' => 'usage', 'amount' => -7, 'created_by' => $admin->id]);

    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-01-01'));

    $carryForward = collect($created)->firstWhere('source', 'carry_forward');
    expect($carryForward)->not->toBeNull();
    expect((float) $carryForward->allocated_amount)->toBe(3.0); // capped, not the full 5 unused
});

test('carry-forward is never generated when the leave type disables it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2025-01-01']);
    $type = ldLeaveType(['allow_carry_forward' => false]);
    ldPolicy($user, $type, $admin, ['annual_entitlement' => 12, 'effective_from' => '2025-01-01']);

    app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2025-01-01'));
    $created = app(LeaveAllocationService::class)->generateForUser($user, Carbon::parse('2026-01-01'));

    expect(collect($created)->firstWhere('source', 'carry_forward'))->toBeNull();
});

// ── Manual adjustment ────────────────────────────────────────────────────

test('admin manual adjustment creates an audited ledger entry and increases available balance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();

    Illuminate\Support\Facades\Auth::login($admin);

    $ledger = app(LeaveAllocationService::class)->manualAdjustment($user, $type, 2, 'Management approved additional leave', $admin);

    expect($ledger->type)->toBe('adjustment');
    expect((float) $ledger->amount)->toBe(2.0);
    expect($ledger->notes)->toBe('Management approved additional leave');
    expect($ledger->created_by)->toBe($admin->id);
    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(2.0);
    expect(App\Models\AuditLog::where('module', 'leave_ledger')->where('action', 'manual_leave_adjustment')->exists())->toBeTrue();
});

test('a negative manual adjustment that would create a negative balance is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = ldLeaveType();
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 2, 'created_by' => $admin->id]);

    Illuminate\Support\Facades\Auth::login($admin);

    expect(fn () => app(LeaveAllocationService::class)->manualAdjustment($user, $type, -5, 'Correction', $admin))
        ->toThrow(Illuminate\Validation\ValidationException::class);

    expect(app(LeaveBalanceService::class)->availableFor($user, $type))->toBe(2.0); // unchanged
});
