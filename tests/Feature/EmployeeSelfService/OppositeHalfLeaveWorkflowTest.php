<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceSegment;
use App\Models\EmployeeLeaveLedger;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

// ── Closing the two gaps explicitly flagged by the immediately preceding
// round: (1) the full create->approve workflow for opposite-half leave was
// never exercised end-to-end through the REAL service methods in every
// ordering, and (2) the live-DB half_day/NULL-period row's provenance was
// unconfirmed. This file exercises the full 10-case matrix plus the exact
// literal bug-report sequence, always via EmployeeAttendanceService::mark*()
// and LeaveService::createRequest()/approve() — never raw model writes for
// the action under test.

function owNoWeeklyOff(): void
{
    Setting::set('weekly_off_days', '[]');
}

function owLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function owGrantBalance(User $employee, LeaveType $type, User $admin, float $amount): void
{
    EmployeeLeaveLedger::create([
        'user_id' => $employee->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01',
        'type' => 'allocation', 'amount' => $amount, 'created_by' => $admin->id,
    ]);
}

beforeEach(function () {
    owNoWeeklyOff();
    Carbon::setTestNow(Carbon::parse('2026-08-14 10:00:00', 'Asia/Kolkata'));
});

afterEach(function () {
    Carbon::setTestNow();
});

// 1. First-half attendance (real self-mark) + second-half leave (create+approve) -> ALLOW.
test('case 1: first-half self-mark then second-half leave approval coexist', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $primary = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    expect($primary)->toBeInstanceOf(EmployeeAttendance::class);

    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $primary->refresh();
    expect($primary->status)->toBe('half_day');
    expect($primary->half_day_period)->toBe('first_half');

    $segment = EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->where('period', 'second_half')->first();
    expect($segment)->not->toBeNull();
    expect($segment->status)->toBe('leave');
    expect($leaveRequest->fresh()->status)->toBe('approved');
});

// 2. Second-half attendance + first-half leave -> ALLOW, mirrored.
test('case 2: second-half self-mark then first-half leave approval coexist', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $primary = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'second_half');

    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $primary->refresh();
    expect($primary->half_day_period)->toBe('second_half');
    $segment = EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->where('period', 'first_half')->first();
    expect($segment->status)->toBe('leave');
});

// 3. First-half attendance + first-half leave -> approval rejects.
test('case 3: same-half (first) attendance and leave conflict at approval, leave stays pending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);

    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))->toThrow(ValidationException::class);
    expect($leaveRequest->fresh()->status)->toBe('pending');
    expect(EmployeeAttendance::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->first()->half_day_period)->toBe('first_half');
});

// 4. Second-half attendance + second-half leave -> approval rejects.
test('case 4: same-half (second) attendance and leave conflict at approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'second_half');
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);

    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))->toThrow(ValidationException::class);
    expect($leaveRequest->fresh()->status)->toBe('pending');
});

// 5. Full-day attendance + first-half leave -> reject.
test('case 5: full-day attendance conflicts with a first-half leave at approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markPresent($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);

    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))->toThrow(ValidationException::class);
});

// 6. Full-day attendance + second-half leave -> reject.
test('case 6: full-day attendance conflicts with a second-half leave at approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markPresent($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);

    expect(fn () => app(LeaveService::class)->approve($leaveRequest, $admin))->toThrow(ValidationException::class);
});

// 7. No attendance + first-half leave -> ALLOW (regression check).
test('case 7: no attendance, first-half leave approves cleanly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $row = EmployeeAttendance::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->first();
    expect($row->status)->toBe('half_day_leave');
    expect($row->half_day_period)->toBe('first_half');
});

// 8. No attendance + second-half leave -> ALLOW (regression check).
test('case 8: no attendance, second-half leave approves cleanly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $row = EmployeeAttendance::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->first();
    expect($row->status)->toBe('half_day_leave');
    expect($row->half_day_period)->toBe('second_half');
});

// 9. Leave FIRST (second-half, pending), THEN opposite-half (first-half)
// attendance self-marked, THEN leave approved.
test('case 9: leave-first ordering — pending second-half leave, then first-half self-mark, then approve', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    expect($leaveRequest->status)->toBe('pending');

    $primary = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    expect($primary)->toBeInstanceOf(EmployeeAttendance::class);

    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $primary->refresh();
    expect($primary->status)->toBe('half_day');
    expect($primary->half_day_period)->toBe('first_half');

    $segment = EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->where('period', 'second_half')->first();
    expect($segment)->not->toBeNull();
    expect($segment->status)->toBe('leave');
});

// 10. Leave FIRST (pending, some half), THEN SAME-half attendance attempted
// -> the self-mark attempt itself must fail immediately.
test('case 10: leave-first ordering — pending first-half leave blocks a same-half self-mark outright', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);

    expect(fn () => app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half'))
        ->toThrow(ValidationException::class);

    expect(EmployeeAttendance::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->count())->toBe(0);
});

// ── The exact literal bug-report sequence: self-mark half-day attendance for
// one half, then submit a real-form-shaped half-day leave request for the
// OTHER half (no end_date key — the exact shape that previously crashed
// createRequest() with "Undefined array key end_date"), then approve.
test('the exact original bug-report sequence: self-mark AM, then real-form half-day leave for PM, then approve', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $amAttendance = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    expect($amAttendance->half_day_period)->toBe('first_half');

    // Exact real-form payload shape: no 'end_date' key at all.
    $formPayload = [
        'leave_type_id' => $type->id,
        'start_date' => '2026-08-14',
        'is_half_day' => '1',
        'half_day_period' => 'second_half',
        'reason' => 'Doctor appointment in the afternoon',
    ];

    $leaveRequest = app(LeaveService::class)->createRequest($employee, $formPayload);
    expect($leaveRequest->status)->toBe('pending');
    expect($leaveRequest->is_half_day)->toBeTrue();
    expect($leaveRequest->half_day_period)->toBe('second_half');
    expect((float) $leaveRequest->days_requested)->toBe(0.5);

    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    expect($leaveRequest->fresh()->status)->toBe('approved');

    $amAttendance->refresh();
    expect($amAttendance->status)->toBe('half_day');
    expect($amAttendance->half_day_period)->toBe('first_half');

    $pmSegment = EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->where('period', 'second_half')->first();
    expect($pmSegment)->not->toBeNull();
    expect($pmSegment->status)->toBe('leave');
    expect($pmSegment->leave_request_id)->toBe($leaveRequest->id);

    // Both halves independently queryable and correct.
    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01', 'Asia/Kolkata'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');
    expect($day['status'])->toBe('half_day');
    expect($day['other_half_status'])->toBe('leave');
    expect($day['other_half_label'])->toContain('other half');
    expect($day['can_regularize'])->toBeFalse();
});

// ── Phase 4: monthly history / day-state must not flatten a mixed day, and
// must not offer Regularize on the leave-covered half.
test('a mixed AM-present/PM-leave day never appears as not_marked and never offers regularize', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01', 'Asia/Kolkata'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->not->toBe('not_marked');
    expect($day['status'])->not->toBe('leave'); // must not hide the attendance half
    expect($day['can_regularize'])->toBeFalse();
    expect($day['other_half_status'])->toBe('leave');

    $dayState = app(EmployeeAttendanceService::class)->getAttendanceDayState($employee, Carbon::parse('2026-08-14'));
    // The primary attendance row is present, so this half is not offered as
    // eligible for a fresh regularization at all via the normal not-marked path.
    expect($dayState['attendance'])->not->toBeNull();
});

// ── Phase 7: balance restoration on cancel-after-approval for a half-day leave.
test('cancelling an approved half-day leave restores the 0.5 day balance and removes the segment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();
    owGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    expect((float) $leaveRequest->paid_leave_days)->toBe(0.5);

    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $balanceService = app(\App\Services\LeaveBalanceService::class);
    $afterApprove = $balanceService->availableFor($employee, $type);
    expect($afterApprove)->toBe(9.5);

    app(LeaveService::class)->cancel($leaveRequest, $admin);

    $afterCancel = $balanceService->availableFor($employee, $type);
    expect($afterCancel)->toBe(10.0);
    expect($leaveRequest->fresh()->status)->toBe('cancelled');

    expect(EmployeeAttendanceSegment::where('leave_request_id', $leaveRequest->id)->count())->toBe(0);

    // The complementary first-half self-mark row is untouched.
    $primary = EmployeeAttendance::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->first();
    expect($primary->half_day_period)->toBe('first_half');
});

// ── Phase 5: half_day_period is required_if(is_half_day,1) at the FormRequest layer.
test('the leave create form rejects half-day submission missing half_day_period', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = owLeaveType();

    $response = $this->actingAs($employee->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date' => '2026-08-14',
        'is_half_day' => '1',
        'reason' => 'x',
    ]);

    $response->assertSessionHasErrors('half_day_period');
    expect(LeaveRequest::where('user_id', $employee->id)->count())->toBe(0);
});

test('the leave create page surfaces which half of today already has attendance, as a cosmetic hint only', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    Auth::login($employee);
    app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');

    $response = $this->actingAs($employee->fresh())->get(route('employee.leave.create'));

    $response->assertOk();
    $response->assertSee('attendance already marked today', false);
});
