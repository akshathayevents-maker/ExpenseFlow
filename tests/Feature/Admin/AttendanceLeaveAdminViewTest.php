<?php

use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;

// weekly_off_days defaults to [0] (Sunday) via migration; neutralize it so
// these tests are stable regardless of which real calendar day they run on
// — same convention as AttendanceGateTest.php.
beforeEach(function () {
    Setting::set('weekly_off_days', '[]');
});

function alvToday(): \Carbon\Carbon
{
    return app(EmployeeAttendanceService::class)->today();
}

function alvLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

// ── Attendance ───────────────────────────────────────────────────────────

test('admin can view todays attendance with present, absent, and not marked employees classified correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $present = User::factory()->create(['role' => 'employee', 'is_active' => true]);
    $absent = User::factory()->create(['role' => 'employee', 'is_active' => true]);
    $notMarked = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    EmployeeAttendance::create([
        'user_id' => $present->id, 'attendance_date' => alvToday()->toDateString(),
        'status' => 'present', 'marked_by' => $present->id, 'marked_at' => now(), 'source' => 'self',
    ]);
    EmployeeAttendance::create([
        'user_id' => $absent->id, 'attendance_date' => alvToday()->toDateString(),
        'status' => 'absent', 'marked_by' => $admin->id, 'marked_at' => now(), 'source' => 'admin',
    ]);

    $response = $this->actingAs($admin->fresh())->get(route('admin.attendance.index'));

    $response->assertOk();
    $response->assertSeeText($present->name);
    $response->assertSeeText($absent->name);
    $response->assertSeeText($notMarked->name);
});

test('a present record dated today is reflected in the current months summary counts', function () {
    // Regression for the "Today's Attendance shows Present but Monthly
    // Summary shows all zeros" bug: getMonthlyHistory()/getMonthlySummary()
    // used to clamp their date range by comparing Carbon INSTANTS across
    // two different timezones (EmployeeAttendanceService::today() is
    // anchored to Asia/Kolkata, while AttendanceController::resolveMonth()
    // builds $month via Carbon::create() in the app's default UTC
    // timezone). That instant comparison could make "today" look earlier
    // than the start of its own calendar month, wrongly emptying the
    // entire month's history — even though the day itself has real data.
    $admin = User::factory()->create(['role' => 'admin']);
    $present = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    EmployeeAttendance::create([
        'user_id' => $present->id, 'attendance_date' => alvToday()->toDateString(),
        'status' => 'present', 'marked_by' => $present->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $service = app(EmployeeAttendanceService::class);
    $month = today()->startOfMonth(); // mirrors AttendanceController::resolveMonth()'s default, built in the app's default timezone
    $summary = $service->getMonthlySummary($present->fresh(), $month->copy());

    expect($summary['present'])->toBe(1);
    expect($summary['not_marked'])->toBe(0);
    expect($summary['payable_days'])->toBeGreaterThanOrEqual(1.0);

    $response = $this->actingAs($admin->fresh())->get(route('admin.attendance.index'));
    $response->assertOk();
});

test('month navigation changes the attendance summary data shown', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    $lastMonth = alvToday()->copy()->subMonthNoOverflow()->startOfMonth();
    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => $lastMonth->copy()->addDays(2)->toDateString(),
        'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $response = $this->actingAs($admin->fresh())
        ->get(route('admin.attendance.index', ['month' => $lastMonth->format('Y-m')]));

    $response->assertOk();
    $response->assertSeeText($lastMonth->format('F Y'));
});

test('admin can drill down into an employees attendance for a month', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    EmployeeAttendance::create([
        'user_id' => $employee->id, 'attendance_date' => alvToday()->toDateString(),
        'status' => 'present', 'marked_by' => $employee->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $response = $this->actingAs($admin->fresh())->get(route('admin.attendance.show', $employee));

    $response->assertOk();
    $response->assertSeeText($employee->name);
});

test('a month with no attendance data renders without error', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'employee', 'is_active' => true]);

    $farMonth = alvToday()->copy()->subYears(2)->format('Y-m');

    $this->actingAs($admin->fresh())
        ->get(route('admin.attendance.index', ['month' => $farMonth]))
        ->assertOk();
});

test('non admin cannot access the admin attendance page', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee->fresh())->get(route('admin.attendance.index'))->assertForbidden();
});

// ── Leave ────────────────────────────────────────────────────────────────

test('admin can view todays leave including the empty state', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin->fresh())->get(route('admin.leave.overview'))
        ->assertOk()
        ->assertSeeText('No employees are on leave today.');

    $employee = User::factory()->create(['role' => 'employee']);
    $type = alvLeaveType();
    LeaveRequest::create([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => alvToday()->toDateString(), 'end_date' => alvToday()->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'Personal',
    ])->forceFill(['status' => 'approved'])->save();

    $response = $this->actingAs($admin->fresh())->get(route('admin.leave.overview'));
    $response->assertOk();
    $response->assertSeeText($employee->name);
});

test('month navigation changes the leave overview data shown', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = alvLeaveType();

    $lastMonth = alvToday()->copy()->subMonthNoOverflow()->startOfMonth();
    LeaveRequest::create([
        'user_id' => $employee->id, 'leave_type_id' => $type->id,
        'start_date' => $lastMonth->copy()->addDays(3)->toDateString(),
        'end_date' => $lastMonth->copy()->addDays(3)->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'Trip',
    ])->forceFill(['status' => 'pending'])->save();

    $response = $this->actingAs($admin->fresh())
        ->get(route('admin.leave.overview', ['month' => $lastMonth->format('Y-m')]));

    $response->assertOk();
    $response->assertSeeText($employee->name);
});

test('a leave overview month with no data renders without error', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $farMonth = alvToday()->copy()->subYears(2)->format('Y-m');

    $this->actingAs($admin->fresh())
        ->get(route('admin.leave.overview', ['month' => $farMonth]))
        ->assertOk()
        ->assertSeeText('No leave requests for this month.');
});

test('non admin cannot access the admin leave overview page', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee->fresh())->get(route('admin.leave.overview'))->assertForbidden();
});

// ── Dashboard widgets ────────────────────────────────────────────────────

test('admin dashboard shows attendance today and leave today widgets with working links', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin->fresh())->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSeeText('Attendance Today');
    $response->assertSeeText("Today's Leave");
    $response->assertSee(route('admin.attendance.index'), false);
    $response->assertSee(route('admin.leave.overview'), false);
});
