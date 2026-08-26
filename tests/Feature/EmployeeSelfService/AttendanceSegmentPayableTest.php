<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceSegment;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveLedger;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;
use App\Services\LeaveService;
use App\Services\PayableDaysCalculator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// ── Real fix for the previously-punted "mixed half day" gap: a genuinely
// split day (one half from one source, the other half from another) must
// be counted correctly by PayableDaysCalculator via EmployeeAttendanceSegment
// overlay rows, not undercounted based on whichever single row happened to
// exist first. See LeaveService::writeOneAttendanceRow() and
// EmployeeAttendanceService::approveRegularization()/cancelRegularization().

function aspNoWeeklyOff(): void
{
    Setting::set('weekly_off_days', '[]');
}

function aspLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function aspGrantBalance(User $employee, LeaveType $type, User $admin, float $amount): void
{
    EmployeeLeaveLedger::create([
        'user_id' => $employee->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01',
        'type' => 'allocation', 'amount' => $amount, 'created_by' => $admin->id,
    ]);
}

beforeEach(function () {
    aspNoWeeklyOff();
});

// 1. AM present + PM paid leave -> both halves represented, payable = 1.0
test('AM present + PM paid leave: both halves represented and payable is 1.0', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType();
    aspGrantBalance($employee, $type, $admin, 10);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => 'first_half', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $segment = EmployeeAttendanceSegment::where('user_id', $employee->id)
        ->whereDate('attendance_date', '2026-08-14')->first();
    expect($segment)->not->toBeNull();
    expect($segment->period)->toBe('second_half');
    expect($segment->status)->toBe('leave');

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(1.0);
});

// 2. AM paid leave + PM present -> both halves represented, payable = 1.0
test('AM paid leave + PM present: both halves represented and payable is 1.0', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType();
    aspGrantBalance($employee, $type, $admin, 10);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => 'second_half', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(1.0);
});

// 3. AM present + PM LOP -> 0.5 payable
test('AM present + PM LOP: payable is 0.5', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType(); // no balance granted -> full LOP

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => 'first_half', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x', 'lop_confirmed' => true,
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $segment = EmployeeAttendanceSegment::where('user_id', $employee->id)->whereDate('attendance_date', '2026-08-14')->first();
    expect($segment->status)->toBe('lop');

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(0.5);
});

// 4. AM LOP + PM present -> 0.5 payable (reversed)
test('AM LOP + PM present: payable is 0.5', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType();

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => 'second_half', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x', 'lop_confirmed' => true,
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(0.5);
});

// 5. AM present + PM leave, then cancel the leave -> PM becomes available again,
// AM present untouched, payable recalculates to 0.5
test('cancelling the PM leave restores availability and leaves AM present untouched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType();
    aspGrantBalance($employee, $type, $admin, 10);

    // Today (real, unfrozen clock) — the self-mark workflow always resolves
    // "today" server-side, so the AM leg must be dated to whatever that
    // actually is rather than a hardcoded historical date.
    $dateStr = Carbon::now('Asia/Kolkata')->toDateString();

    Auth::login($employee);
    // AM leg via the real self-mark workflow (Case A: AM attendance + PM
    // leave, cancel PM leave) — matches Case B's established pattern of
    // using the real EmployeeAttendanceService for the attendance leg.
    $amAttendance = app(EmployeeAttendanceService::class)->markHalfDay($employee, 'first_half');
    $amUpdatedAt = $amAttendance->updated_at;

    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => $dateStr, 'end_date' => $dateStr,
        'is_half_day' => true, 'half_day_period' => 'second_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    app(LeaveService::class)->cancel($leaveRequest, $admin);

    expect(EmployeeAttendanceSegment::where('leave_request_id', $leaveRequest->id)->count())->toBe(0);

    $amAttendance->refresh();
    expect($amAttendance->status)->toBe('half_day');
    expect($amAttendance->half_day_period)->toBe('first_half');
    expect($amAttendance->source)->toBe('self');
    expect($amAttendance->updated_at->eq($amUpdatedAt))->toBeTrue(); // provably untouched

    // PM is regularizable again.
    $dayState = app(EmployeeAttendanceService::class)->getAttendanceDayState($employee, Carbon::parse($dateStr));
    expect($dayState['has_approved_leave'])->toBeFalse();

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse($dateStr), Carbon::parse($dateStr));
    expect($payable)->toBe(0.5);
});

// 6. AM leave + PM present, then cancel the leave -> PM present untouched
test('cancelling the AM leave leaves PM present completely untouched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType();
    aspGrantBalance($employee, $type, $admin, 10);

    $pmAttendance = EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'half_day',
        'half_day_period' => 'second_half', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    app(LeaveService::class)->cancel($leaveRequest, $admin);

    $pmAttendance->refresh();
    expect($pmAttendance->status)->toBe('half_day');
    expect($pmAttendance->half_day_period)->toBe('second_half');
    expect($pmAttendance->source)->toBe('self');
    expect($pmAttendance->updated_at->eq($pmAttendance->created_at))->toBeTrue();
});

// 7 & 8. AM attendance + PM regularization: coexist, and cancel reverses
// ONLY the PM segment, leaving AM completely untouched.
test('AM attendance + PM regularization coexist and cancellation reverses only the PM segment', function () {
    // Both legs target business "today" (Asia/Kolkata), frozen via
    // Carbon::setTestNow(). This exercises the timezone fix in
    // EmployeeAttendanceService::assertRegularizable(): "today" is compared
    // by calendar date (->toDateString()) rather than by raw Carbon instant,
    // so a same-day PM regularization submitted alongside a same-day AM
    // self-mark is correctly accepted rather than rejected as "a future
    // date". Previously this test used a raw EmployeeAttendance::create()
    // for the AM leg to work around that bug; now both legs go through the
    // real service workflow, matching the established pattern from Cases
    // A/B/D in this file.
    Carbon::setTestNow(Carbon::parse('2026-08-14 10:00:00', 'Asia/Kolkata'));

    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $dateStr = app(EmployeeAttendanceService::class)->today()->toDateString();

    Auth::login($employee);
    $amAttendance = app(EmployeeAttendanceService::class)->markPresent($employee, 'first_half');
    $amUpdatedAt = $amAttendance->updated_at;

    $regularization = app(EmployeeAttendanceService::class)->createRegularization($employee, [
        'attendance_date' => $dateStr, 'requested_status' => 'half_day', 'half_day_period' => 'second_half', 'reason' => 'forgot',
    ]);

    Auth::login($admin);
    app(EmployeeAttendanceService::class)->approveRegularization($regularization, $admin);

    // AM row (from the real self-mark) is completely untouched by the
    // PM regularization's approval.
    $amAttendance->refresh();
    expect($amAttendance->status)->toBe('half_day');
    expect($amAttendance->half_day_period)->toBe('first_half');
    expect($amAttendance->source)->toBe('self');
    expect($amAttendance->updated_at->eq($amUpdatedAt))->toBeTrue();

    // PM is represented as an independent segment.
    $segment = EmployeeAttendanceSegment::where('regularization_id', $regularization->id)->first();
    expect($segment)->not->toBeNull();
    expect($segment->period)->toBe('second_half');
    expect($segment->status)->toBe('present');

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse($dateStr), Carbon::parse($dateStr));
    expect($payable)->toBe(1.0);

    // Cancel the regularization after approval -> only the PM segment reverts.
    app(EmployeeAttendanceService::class)->cancelRegularization($regularization, $admin);

    expect(EmployeeAttendanceSegment::where('regularization_id', $regularization->id)->count())->toBe(0);

    $amAttendance->refresh();
    expect($amAttendance->status)->toBe('half_day');
    expect($amAttendance->half_day_period)->toBe('first_half');
    expect($amAttendance->source)->toBe('self');
    expect($amAttendance->updated_at->eq($amUpdatedAt))->toBeTrue(); // provably untouched

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse($dateStr), Carbon::parse($dateStr));
    expect($payable)->toBe(0.5);

    Carbon::setTestNow();
});

// 9. Case D: AM leave (real LeaveService workflow) + PM regularization (real
// EmployeeAttendanceService workflow), cancel the regularization -> the AM
// leave's own representation (the primary employee_attendance row written
// by leave approval) is left completely untouched, matching the same
// "cancel only reverses what it itself wrote" invariant already proven for
// Cases A/B/C.
test('AM leave + PM regularization coexist and cancelling the regularization leaves AM leave untouched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = aspLeaveType();
    aspGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $amAttendance = EmployeeAttendance::where('leave_request_id', $leaveRequest->id)->first();
    expect($amAttendance)->not->toBeNull();
    expect($amAttendance->half_day_period)->toBe('first_half');
    $amUpdatedAt = $amAttendance->updated_at;

    Auth::login($employee);
    $regularization = app(EmployeeAttendanceService::class)->createRegularization($employee, [
        'attendance_date' => '2026-08-14', 'requested_status' => 'half_day', 'half_day_period' => 'second_half', 'reason' => 'forgot',
    ]);

    Auth::login($admin);
    app(EmployeeAttendanceService::class)->approveRegularization($regularization, $admin);

    // AM leave row is completely untouched by the regularization's approval.
    $amAttendance->refresh();
    expect($amAttendance->status)->toBe('half_day_leave');
    expect($amAttendance->half_day_period)->toBe('first_half');
    expect($amAttendance->updated_at->eq($amUpdatedAt))->toBeTrue();

    // PM is represented as an independent segment.
    $segment = EmployeeAttendanceSegment::where('regularization_id', $regularization->id)->first();
    expect($segment)->not->toBeNull();
    expect($segment->period)->toBe('second_half');
    expect($segment->status)->toBe('present');

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(1.0);

    // Cancel the regularization -> only the PM segment reverts, AM leave
    // representation remains completely unchanged.
    app(EmployeeAttendanceService::class)->cancelRegularization($regularization, $admin);

    expect(EmployeeAttendanceSegment::where('regularization_id', $regularization->id)->count())->toBe(0);

    $amAttendance->refresh();
    expect($amAttendance->status)->toBe('half_day_leave');
    expect($amAttendance->half_day_period)->toBe('first_half');
    expect($amAttendance->updated_at->eq($amUpdatedAt))->toBeTrue(); // provably untouched
    expect($leaveRequest->fresh()->status)->toBe('approved'); // AM leave itself untouched

    $payable = app(PayableDaysCalculator::class)->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));
    expect($payable)->toBe(0.5);
});
