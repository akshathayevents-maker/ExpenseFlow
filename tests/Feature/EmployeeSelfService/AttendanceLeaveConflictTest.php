<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveLedger;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

function alcNoWeeklyOff(): void
{
    Setting::set('weekly_off_days', '[]');
}

function alcLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function alcGrantBalance(User $employee, LeaveType $type, User $admin, float $amount): void
{
    EmployeeLeaveLedger::create([
        'user_id' => $employee->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01',
        'type' => 'allocation', 'amount' => $amount, 'created_by' => $admin->id,
    ]);
}

beforeEach(function () {
    alcNoWeeklyOff(); // Sat/Sun treated as normal weekdays unless a test opts in
});

test('an approved full-day leave shows On Leave with no Regularize action', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->toBe('leave');
    expect($day['leave_type_name'])->toBe('Casual Leave');
    expect($day['can_regularize'])->toBeFalse();

    $dayState = app(EmployeeAttendanceService::class)->getAttendanceDayState($employee, Carbon::parse('2026-08-14'));
    expect($dayState['eligible'])->toBeFalse();
    expect($dayState['block_reason'])->toContain('Approved Leave');
});

test('a pending leave shows Leave Pending and blocks regularization', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->toBe('leave_pending');
    expect($day['leave_type_name'])->toBe('Casual Leave');
    expect($day['can_regularize'])->toBeFalse();

    $dayState = app(EmployeeAttendanceService::class)->getAttendanceDayState($employee, Carbon::parse('2026-08-14'));
    expect($dayState['eligible'])->toBeFalse();
    expect($dayState['block_reason'])->toContain('pending leave request');
});

test('a rejected leave no longer suppresses regularization', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->reject($leaveRequest, $admin);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->toBe('not_marked');
    expect($day['can_regularize'])->toBeTrue();
});

test('a cancelled-after-approval leave no longer suppresses regularization', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);
    app(LeaveService::class)->cancel($leaveRequest, $admin);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    // cancel() deletes the 'leave_approval'-sourced attendance row it wrote
    expect($day['status'])->toBe('not_marked');
    expect($day['can_regularize'])->toBeTrue();
});

test('an approved half-day leave shows the correct half-day status via the real attendance row', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14',
        'is_half_day' => true, 'half_day_period' => 'first_half', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->toBe('half_day_leave');
    expect($day['can_regularize'])->toBeFalse();
});

test('a normal unmarked working day still shows Not Marked with Regularize available', function () {
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-10');

    expect($day['status'])->toBe('not_marked');
    expect($day['can_regularize'])->toBeTrue();
});

test('a weekly off day is never shown as Not Marked regardless of leave', function () {
    Setting::set('weekly_off_days', json_encode([0])); // Sunday
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $sunday = $history->first(fn ($d) => $d['date']->dayOfWeek === 0);

    expect($sunday['status'])->toBe('weekly_off');
    expect($sunday['can_regularize'])->toBeFalse();
});

test('a holiday outranks an overlapping approved leave for status display', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    // Leave is requested and approved BEFORE the date becomes a holiday —
    // applicableWorkingDays() would reject requesting leave for a day that
    // is already a holiday, so this models the realistic sequence: the
    // holiday is declared afterward, and must still outrank the
    // already-approved leave for display purposes.
    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    \App\Models\Holiday::create(['holiday_date' => '2026-08-14', 'name' => 'Test Holiday', 'is_active' => true]);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->toBe('holiday');
});

test('a leave request spanning multiple dates marks every date in range', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-12', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    foreach (['2026-08-12', '2026-08-13', '2026-08-14'] as $dateStr) {
        $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === $dateStr);
        expect($day['status'])->toBe('leave');
        expect($day['can_regularize'])->toBeFalse();
    }
});

test('an existing attendance row outranks a pending leave request on the same date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => '2026-08-14', 'status' => 'present',
        'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    Auth::login($employee);
    app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);

    $history = app(EmployeeAttendanceService::class)->getMonthlyHistory($employee, Carbon::parse('2026-08-01'));
    $day = $history->firstWhere(fn ($d) => $d['date']->toDateString() === '2026-08-14');

    expect($day['status'])->toBe('present');
    expect($day['can_regularize'])->toBeFalse();
});

test('the regularization endpoint rejects a direct request on an approved-leave date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    expect(fn () => app(EmployeeAttendanceService::class)->createRegularization($employee, [
        'attendance_date' => '2026-08-14', 'requested_status' => 'present', 'reason' => 'forgot',
    ]))->toThrow(Illuminate\Validation\ValidationException::class);

    expect(EmployeeAttendanceRegularization::where('user_id', $employee->id)->count())->toBe(0);
});

test('the regularization endpoint rejects a direct request on a pending-leave date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);

    expect(fn () => app(EmployeeAttendanceService::class)->createRegularization($employee, [
        'attendance_date' => '2026-08-14', 'requested_status' => 'present', 'reason' => 'forgot',
    ]))->toThrow(Illuminate\Validation\ValidationException::class);

    expect(EmployeeAttendanceRegularization::where('user_id', $employee->id)->count())->toBe(0);
});

test('approved leave still contributes correctly to payable days, unaffected by this attendance-display fix', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['employment_start_date' => '2020-01-01']);
    $type = alcLeaveType();
    alcGrantBalance($employee, $type, $admin, 10);

    Auth::login($employee);
    $leaveRequest = app(LeaveService::class)->createRequest($employee, [
        'leave_type_id' => $type->id, 'start_date' => '2026-08-14', 'end_date' => '2026-08-14', 'reason' => 'x',
    ]);
    Auth::login($admin);
    app(LeaveService::class)->approve($leaveRequest, $admin);

    $payableDays = app(App\Services\PayableDaysCalculator::class)
        ->payableDaysSoFar($employee, Carbon::parse('2026-08-14'), Carbon::parse('2026-08-14'));

    expect($payableDays)->toBe(1.0); // paid leave day remains fully payable
});
