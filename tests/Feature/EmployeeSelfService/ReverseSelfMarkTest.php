<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\EmployeeAttendanceSegment;
use App\Models\EmployeeLeaveLedger;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;
use App\Services\LeaveService;
use App\Services\PayableDaysCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// ── Round 4: the REAL reverse self-mark workflow (gap #1), the centralized
// same-half overlay invariant (gap #4), and the explicit NULL
// half_day_period rule (gap #2). All attendance legs below go through the
// real EmployeeAttendanceService::markPresent()/markHalfDay() methods —
// never raw EmployeeAttendanceSegment::create() calls.

function rsmNoWeeklyOff(): void
{
    Setting::set('weekly_off_days', '[]');
}

function rsmLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function rsmGrantBalance(User $employee, LeaveType $type, User $admin, float $amount): void
{
    EmployeeLeaveLedger::create([
        'user_id' => $employee->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01',
        'type' => 'allocation', 'amount' => $amount, 'created_by' => $admin->id,
    ]);
}

beforeEach(function () {
    rsmNoWeeklyOff();
    Carbon::setTestNow(Carbon::parse('2026-08-14 10:00:00', 'Asia/Kolkata'));
});

afterEach(function () {
    Carbon::setTestNow();
});

// 1. Leave first (AM), then real self-mark present for PM.
test('approved AM leave then real markPresent(second_half) writes a PM segment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    // Leave wrote the PRIMARY EmployeeAttendance row for first_half.
    $primary = EmployeeAttendance::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->first();
    expect($primary->half_day_period)->toBe('first_half');

    // Real self-mark workflow for the free half.
    $result = app(EmployeeAttendanceService::class)->markPresent($employee, 'second_half');

    expect($result)->toBeInstanceOf(EmployeeAttendanceSegment::class);
    expect($result->period)->toBe('second_half');
    expect($result->status)->toBe('present');
    expect($result->source)->toBe('self');

    // Primary row untouched.
    $primary->refresh();
    expect($primary->half_day_period)->toBe('first_half');

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(1.0);
});

// 2. Self-mark first (PM present via markHalfDay), then leave approval for AM
// must detect the occupied half and write its own half as a segment rather
// than throwing.
test('real self-mark first, then leave approval writes AM as a segment without throwing', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $pmAttendance = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'second_half');
    expect($pmAttendance)->toBeInstanceOf(EmployeeAttendance::class);
    expect($pmAttendance->half_day_period)->toBe('second_half');

    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $segment = EmployeeAttendanceSegment::where('leave_request_id', $leaveRequest->id)->first();
    expect($segment)->not->toBeNull();
    expect($segment->period)->toBe('first_half');
    expect($segment->status)->toBe('leave');

    $pmAttendance->refresh();
    expect($pmAttendance->half_day_period)->toBe('second_half');
    expect($pmAttendance->status)->toBe('half_day');

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(1.0);
});

// 3. Same-half duplicate: reject outright via real HTTP mark-present route,
// not silently overwritten.
test('marking present for an already-marked half is rejected', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    Auth::login($employee);

    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');

    expect(fn () => app(EmployeeAttendanceService::class)->markPresent($employee, 'first_half'))
        ->toThrow(ValidationException::class);
});

// 4. Overlay invariant (gap #4): force two independently-sourced facts at
// the SAME half via two real write paths in sequence -> rejected.
test('the overlay invariant rejects a second independent fact for the same half', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    // Self-mark occupies second_half as the primary row is first_half? No —
    // start from leave occupying first_half as primary row, self-mark takes
    // second_half as a segment, then attempt to force ANOTHER independent
    // fact onto second_half directly via the shared guard.
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markPresent($employee, 'second_half');

    // second_half is now occupied by a segment. A second attempt to mark
    // present for second_half must be rejected, not silently overwritten.
    expect(fn () => app(EmployeeAttendanceService::class)->markPresent($employee, 'second_half'))
        ->toThrow(ValidationException::class);

    expect(EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->where('period', 'second_half')->count())->toBe(1);
});

// 5. Cancellation direction B: AM leave (primary row) + PM attendance
// (segment, via the real self-mark workflow) -> cancel AM leave -> PM
// segment/attendance completely untouched.
test('cancelling AM leave leaves the self-marked PM segment untouched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    Auth::login($employee);
    $pmSegment = app(EmployeeAttendanceService::class)->markPresent($employee, 'second_half');
    $pmUpdatedAt = $pmSegment->updated_at;

    Auth::login($admin);
    app(LeaveService::class)->cancel($leaveRequest, $admin);

    $pmSegment->refresh();
    expect($pmSegment->status)->toBe('present');
    expect($pmSegment->period)->toBe('second_half');
    expect($pmSegment->updated_at->eq($pmUpdatedAt))->toBeTrue();

    // AM's primary row (leave_approval-sourced) was removed by cancel().
    expect(EmployeeAttendance::where('leave_request_id', $leaveRequest->id)->count())->toBe(0);

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(0.5);
});

// 6. HTTP-level real workflow: acting as employee, POST to the mark-present
// route with half_day_period.
test('POST mark-present with half_day_period marks just the other half', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $this->actingAs($employee)
        ->post(route('employee.attendance.mark-present'), ['half_day_period' => 'second_half'])
        ->assertRedirect();

    $segment = EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->where('period', 'second_half')->first();
    expect($segment)->not->toBeNull();
    expect($segment->status)->toBe('present');
});

// ── Gap #2: legacy/defensive NULL half_day_period rule ──────────────────
// NULL period = ambiguous, conflicts with BOTH halves, cannot coexist with
// anything half-specific, still counts 0.5 payable.

test('a half_day row with NULL period blocks a first_half leave attempt', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => null, 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    // createRequest() only checks leave-vs-leave overlap (assertNoOverlap);
    // leave-vs-existing-attendance is deferred to approval time
    // (writeOneAttendanceRow), same as every other attendance conflict in
    // this codebase — so the request itself is accepted, and the conflict
    // surfaces at approve().
    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);

    Auth::login($admin);
    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))
        ->toThrow(ValidationException::class);
});

test('a half_day row with NULL period blocks a second_half leave attempt', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => null, 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);

    Auth::login($admin);
    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))
        ->toThrow(ValidationException::class);
});

test('a half_day row with NULL period blocks a first_half regularization on a date with an ambiguous approved leave', function () {
    // The regularization-vs-leave conflict guard (assertRegularizable ->
    // hasApprovedLeave -> periodsOverlap) is exercised here with the LEAVE
    // side carrying an ambiguous/legacy NULL period (is_half_day=true,
    // half_day_period=null) — the same conservative "NULL conflicts with
    // everything half-specific" rule applies regardless of which side
    // (attendance vs. leave) is the ambiguous one, since both route through
    // the single shared AttendanceConflictChecker::periodsOverlap().
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    $leave = \App\Models\LeaveRequest::create([
        'user_id' => $employee->id, 'leave_type_id' => rsmLeaveType()->id,
        'start_date' => '2026-08-13', 'end_date' => '2026-08-13',
        'is_half_day' => true, 'half_day_period' => null, 'days_requested' => 0.5,
        'reason' => 'x', 'status' => 'approved', 'paid_leave_days' => 0.5, 'lop_days' => 0,
        'reviewed_by' => $admin->id, 'reviewed_at' => now(),
    ]);

    Auth::login($employee);
    expect(fn () => app(EmployeeAttendanceService::class)->createRegularization($employee, [
        'attendance_date' => '2026-08-13', 'requested_status' => 'half_day', 'half_day_period' => 'first_half', 'reason' => 'x',
    ]))->toThrow(ValidationException::class);

    expect(fn () => app(EmployeeAttendanceService::class)->createRegularization($employee, [
        'attendance_date' => '2026-08-13', 'requested_status' => 'half_day', 'half_day_period' => 'second_half', 'reason' => 'x',
    ]))->toThrow(ValidationException::class);
});

test('a half_day row with NULL period still contributes 0.5 to payableDaysSoFar', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => null, 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(0.5);
});

// ── New half-day self-mark UI gap: a plain "Mark Half Day" must always
// carry a period — both at the HTTP layer (FormRequest-style validate())
// and at the service layer directly (backend-authoritative), so a NEW
// half-day write can never land with an ambiguous NULL period again.

test('case A: HTTP mark-half-day without a period is rejected with a visible validation error', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    $response = $this->actingAs($employee)->post(route('employee.attendance.mark-half-day'));

    $response->assertSessionHasErrors('half_day_period');
    expect(EmployeeAttendance::where('user_id', $employee->id)->count())->toBe(0);
});

test('case D: markHalfDay(user, null) called directly at the service layer is rejected', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    expect(fn () => app(EmployeeAttendanceService::class)->markHalfDay($employee, null))
        ->toThrow(ValidationException::class);

    expect(EmployeeAttendance::where('user_id', $employee->id)->count())->toBe(0);
});

test('case E: markHalfDay is still rejected for a half already covered by a same-half pending leave', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(\App\Services\LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);

    expect(fn () => app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half'))
        ->toThrow(ValidationException::class);
});

test('case F: markHalfDay is still allowed for the opposite half of a pending leave', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = rsmLeaveType();
    rsmGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(\App\Services\LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);

    $result = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'second_half');

    expect($result)->toBeInstanceOf(EmployeeAttendance::class);
    expect($result->half_day_period)->toBe('second_half');
});

test('mark-other-half UI still offers only the free half after a first_half self-mark', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');

    $offered = app(EmployeeAttendanceService::class)->markableOtherHalfToday($employee);

    expect($offered)->toBe('second_half');
});

test('mark-other-half UI still offers only the free half after a second_half self-mark', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'second_half');

    $offered = app(EmployeeAttendanceService::class)->markableOtherHalfToday($employee);

    expect($offered)->toBe('first_half');
});
