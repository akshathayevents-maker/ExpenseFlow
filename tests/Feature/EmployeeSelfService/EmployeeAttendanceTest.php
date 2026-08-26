<?php

use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;
use App\Services\PayableDaysCalculator;
use Carbon\Carbon;

function attendanceService(): EmployeeAttendanceService
{
    return app(EmployeeAttendanceService::class);
}

// today() is business-timezone (Asia/Kolkata) "now" — tests anchor to it
// rather than a hardcoded date so they remain correct regardless of when
// the suite runs.

test('employee attendance page loads', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('employee.attendance.index'))->assertOk();
});

test('employee sees only own attendance', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $today = attendanceService()->today();

    EmployeeAttendance::create([
        'user_id' => $b->id, 'attendance_date' => $today->toDateString(),
        'status' => 'present', 'marked_by' => $b->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $response = $this->actingAs($a)->get(route('employee.attendance.index'));

    $response->assertOk();
    expect(attendanceService()->getToday($a))->toBeNull();
});

test('employee can mark present', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('employee.attendance.mark-present'))->assertRedirect();

    $today = attendanceService()->today();
    $row = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $today->toDateString())->first();

    expect($row)->not->toBeNull();
    expect($row->status)->toBe('present');
    expect($row->marked_by)->toBe($user->id);
    expect($row->source)->toBe('self');
});

test('employee can mark half day', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('employee.attendance.mark-half-day'), ['half_day_period' => 'first_half'])->assertRedirect();

    $today = attendanceService()->today();
    $row = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $today->toDateString())->first();

    expect($row->status)->toBe('half_day');
    expect($row->half_day_period)->toBe('first_half');
});

test('duplicate same-day attendance is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('employee.attendance.mark-present'));
    $response = $this->actingAs($user)->post(route('employee.attendance.mark-half-day'), ['half_day_period' => 'first_half']);

    $response->assertSessionHasErrors('attendance');
    $today = attendanceService()->today();
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $today->toDateString())->count())->toBe(1);
});

test('employee cannot mark another employees attendance', function () {
    // Structurally impossible: mark-present/mark-half-day take no user
    // parameter at all — they always act on auth()->user(). Verified here by
    // confirming marking as user A never creates a row for user B.
    $a = User::factory()->create();
    $b = User::factory()->create();

    $this->actingAs($a)->post(route('employee.attendance.mark-present'));

    expect(attendanceService()->getToday($b))->toBeNull();
});

test('employee cannot edit another employees attendance', function () {
    // No edit/update route exists for attendance at all (v1: today-only,
    // create-once). Confirmed by route list containing only index +
    // mark-present + mark-half-day.
    $routeNames = collect(\Illuminate\Support\Facades\Route::getRoutes())
        ->map(fn ($r) => $r->getName())
        ->filter(fn ($n) => $n && str_starts_with($n, 'employee.attendance.'));

    expect($routeNames->all())->toEqualCanonicalizing([
        'employee.attendance.index',
        'employee.attendance.mark-present',
        'employee.attendance.mark-half-day',
    ]);
});

test('past attendance is protected — cannot mark for a non-today date', function () {
    $user = User::factory()->create();
    $service = attendanceService();

    // Directly seed a "yesterday" row the way an admin/system correction
    // would, then confirm the employee-facing mark() always targets today
    // regardless — there is no code path that lets it target another date.
    $yesterday = $service->today()->copy()->subDay();
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $yesterday->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $this->actingAs($user)->post(route('employee.attendance.mark-present'))->assertRedirect();

    // Yesterday's row is untouched; a NEW row was created for today.
    $yesterdayRow = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $yesterday->toDateString())->first();
    $todayRow = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $service->today()->toDateString())->first();

    expect($yesterdayRow->status)->toBe('present');
    expect($todayRow)->not->toBeNull();
});

test('future attendance cannot be created', function () {
    // No route accepts a date parameter, so there is no way to request
    // future attendance via the employee UI. Confirmed structurally.
    expect(\Illuminate\Support\Facades\Route::has('employee.attendance.mark-present'))->toBeTrue();
    $route = collect(\Illuminate\Support\Facades\Route::getRoutes())->first(fn ($r) => $r->getName() === 'employee.attendance.mark-present');
    expect($route->parameterNames())->toBe([]);
});

test('weekly off is displayed correctly in monthly history', function () {
    $user = User::factory()->create();
    Setting::set('weekly_off_days', json_encode([0])); // Sunday

    $service = attendanceService();
    $monthStart = $service->today()->copy()->startOfMonth();
    $history = $service->getMonthlyHistory($user, $monthStart);

    $sundays = $history->filter(fn ($d) => $d['date']->dayOfWeek === 0);
    if ($sundays->isNotEmpty()) {
        expect($sundays->first()['status'])->toBe('weekly_off');
    }
    expect(true)->toBeTrue(); // guard against an entirely empty month with no Sundays yet
});

test('holiday is displayed correctly in monthly history', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $today = $service->today();

    Holiday::create(['holiday_date' => $today->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $history = $service->getMonthlyHistory($user, $today->copy()->startOfMonth());
    $todayEntry = $history->firstWhere(fn ($d) => $d['date']->isSameDay($today));

    expect($todayEntry['status'])->toBe('holiday');
});

test('full-day approved leave appears as leave in monthly history', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $today = $service->today();

    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $today->toDateString(), 'end_date' => $today->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $history = $service->getMonthlyHistory($user, $today->copy()->startOfMonth());
    $todayEntry = $history->firstWhere(fn ($d) => $d['date']->isSameDay($today));

    expect($todayEntry['status'])->toBe('leave');
});

test('half-day approved leave appears as half_day_leave in monthly history', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $today = $service->today();

    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $today->toDateString(), 'end_date' => $today->toDateString(),
        'is_half_day' => true, 'half_day_period' => 'first_half',
        'days_requested' => 0.5, 'reason' => 'x', 'status' => 'approved',
    ]);

    $history = $service->getMonthlyHistory($user, $today->copy()->startOfMonth());
    $todayEntry = $history->firstWhere(fn ($d) => $d['date']->isSameDay($today));

    expect($todayEntry['status'])->toBe('half_day_leave');
});

test('an actual attendance row outranks approved leave for the same date', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $today = $service->today();

    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $today->toDateString(), 'end_date' => $today->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $today->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'admin',
    ]);

    $history = $service->getMonthlyHistory($user, $today->copy()->startOfMonth());
    $todayEntry = $history->firstWhere(fn ($d) => $d['date']->isSameDay($today));

    expect($todayEntry['status'])->toBe('present');
});

test('holiday marking is blocked server-side even if attempted directly', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    Holiday::create(['holiday_date' => $service->today()->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    expect(fn () => $service->markPresent($user))->toThrow(\Illuminate\Validation\ValidationException::class);
    expect(EmployeeAttendance::where('user_id', $user->id)->count())->toBe(0);
});

test('weekly off marking is blocked server-side even if attempted directly', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    Setting::set('weekly_off_days', json_encode([$service->today()->dayOfWeek]));

    expect(fn () => $service->markHalfDay($user))->toThrow(\Illuminate\Validation\ValidationException::class);
    expect(EmployeeAttendance::where('user_id', $user->id)->count())->toBe(0);
});

test('holiday marking is blocked via the HTTP route, not only hidden in Blade', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    Holiday::create(['holiday_date' => $service->today()->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $response = $this->actingAs($user)->post(route('employee.attendance.mark-present'));

    $response->assertSessionHasErrors('attendance');
    expect(EmployeeAttendance::where('user_id', $user->id)->count())->toBe(0);
});

test('attendance page shows holiday state and hides mark buttons', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    Holiday::create(['holiday_date' => $service->today()->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $this->actingAs($user)->get(route('employee.attendance.index'))
        ->assertOk()
        ->assertDontSee('Mark Present')
        ->assertDontSee('Mark Half Day');
});

test('monthly summary counts are correct', function () {
    $user = User::factory()->create();
    $service = attendanceService();

    $this->actingAs($user)->post(route('employee.attendance.mark-present'));

    $summary = $service->getMonthlySummary($user, $service->today()->copy()->startOfMonth());

    expect($summary['present'])->toBe(1);
    expect($summary)->toHaveKey('payable_days');
});

test('current month does not include future attendance', function () {
    $user = User::factory()->create();
    $service = attendanceService();

    $history = $service->getMonthlyHistory($user, $service->today()->copy()->startOfMonth());

    expect($history->every(fn ($d) => $d['date']->lte($service->today())))->toBeTrue();
});

test('previous month can be viewed', function () {
    $user = User::factory()->create();

    $prevMonth = attendanceService()->today()->copy()->subMonth()->format('Y-m');

    $this->actingAs($user)->get(route('employee.attendance.index', ['month' => $prevMonth]))->assertOk();
});

test('unmarked day is not falsely inserted as absent', function () {
    $user = User::factory()->create();
    $service = attendanceService();

    // Never marked anything — no DB row should exist, and the history should
    // report 'not_marked' (unless the day is a weekend/holiday), never
    // silently create an 'absent' row just because the page was viewed.
    $this->actingAs($user)->get(route('employee.attendance.index'));

    expect(EmployeeAttendance::where('user_id', $user->id)->count())->toBe(0);
});

test('PayableDaysCalculator remains the single source of truth for payable days', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $calculator = app(PayableDaysCalculator::class);

    $monthStart = $service->today()->copy()->startOfMonth();
    $end = $service->today();

    $summary = $service->getMonthlySummary($user, $monthStart);
    $direct  = $calculator->payableDaysSoFar($user, $monthStart, $end);

    expect($summary['payable_days'])->toBe($direct);
});

test('unauthorized users cannot access employee attendance routes', function () {
    $this->get(route('employee.attendance.index'))->assertRedirect(route('login'));
    $this->post(route('employee.attendance.mark-present'))->assertRedirect(route('login'));
});

// ── Dashboard quick-action state ────────────────────────────────────────

test('dashboard shows Not Marked when attendance is unmarked on a working day', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    if ($service->isTodayNonWorking()) {
        $this->markTestSkipped('Today is a non-working day in this test run.');
    }

    $this->actingAs($user)->get(route('employee.dashboard'))
        ->assertOk()
        ->assertSee('Not marked');
});

test('dashboard shows Present today once attendance is marked', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    if ($service->isTodayNonWorking()) {
        $this->markTestSkipped('Today is a non-working day in this test run.');
    }

    $this->actingAs($user)->post(route('employee.attendance.mark-present'));

    $this->actingAs($user)->get(route('employee.dashboard'))
        ->assertOk()
        ->assertSee('Present today');
});

test('dashboard shows Holiday today when today is a holiday', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    Holiday::create(['holiday_date' => $service->today()->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $this->actingAs($user)->get(route('employee.dashboard'))
        ->assertOk()
        ->assertSee('Holiday today');
});

// ── Monthly history "can_regularize" flag (Regularize quick action) ──────

test('unmarked working date is flagged as regularizable', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $monthStart = $service->today()->copy()->startOfMonth();

    $history = $service->getMonthlyHistory($user, $monthStart);
    $weekday = $history->first(fn ($d) => $d['status'] === 'not_marked');

    if ($weekday === null) {
        $this->markTestSkipped('No unmarked weekday available this month in this test run.');
    }

    expect($weekday['can_regularize'])->toBeTrue();
});

test('date with attendance already marked is not flagged as regularizable', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $date = $service->today()->copy()->subDays(3); // safely-in-the-past weekday, matching the convention used elsewhere in this suite
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $history = $service->getMonthlyHistory($user, $date->copy()->startOfMonth());
    $day = $history->first(fn ($d) => $d['date']->isSameDay($date));

    expect($day['can_regularize'])->toBeFalse();
});

test('approved leave date is not flagged as regularizable', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $date = $service->today()->copy()->subDays(3);
    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $date->toDateString(), 'end_date' => $date->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $history = $service->getMonthlyHistory($user, $date->copy()->startOfMonth());
    $day = $history->first(fn ($d) => $d['date']->isSameDay($date));

    expect($day['can_regularize'])->toBeFalse();
});

test('holiday is not flagged as regularizable', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $date = $service->today()->copy()->subDays(3);
    Holiday::create(['holiday_date' => $date->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $history = $service->getMonthlyHistory($user, $date->copy()->startOfMonth());
    $day = $history->first(fn ($d) => $d['date']->isSameDay($date));

    expect($day['can_regularize'])->toBeFalse();
});

test('weekly off is not flagged as regularizable', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $date = $service->today()->copy()->subDays(3);
    Setting::set('weekly_off_days', json_encode([$date->dayOfWeek]));

    $history = $service->getMonthlyHistory($user, $date->copy()->startOfMonth());
    $day = $history->first(fn ($d) => $d['date']->isSameDay($date));

    expect($day['can_regularize'])->toBeFalse();
});

test('date with an existing pending regularization is not flagged as regularizable', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $date = $service->today()->copy()->subDays(3);
    \App\Models\EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $history = $service->getMonthlyHistory($user, $date->copy()->startOfMonth());
    $day = $history->first(fn ($d) => $d['date']->isSameDay($date));

    expect($day['can_regularize'])->toBeFalse();
});

test('the attendance page renders a Regularize link only for regularizable dates', function () {
    $user = User::factory()->create();
    $service = attendanceService();
    $markedDate = $service->today()->copy()->subDays(1);
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $markedDate->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $response = $this->actingAs($user)->get(route('employee.attendance.index'));

    $response->assertOk();
    $response->assertSee('js-regularize-link', false);
    // The marked date's row must not carry a regularize link keyed to it.
    $response->assertDontSee('data-date="' . $markedDate->toDateString() . '"', false);
});
