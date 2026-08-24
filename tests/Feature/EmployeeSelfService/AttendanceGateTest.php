<?php

use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;

function gateToday(): \Carbon\Carbon
{
    return app(EmployeeAttendanceService::class)->today();
}

function markGateAttendance(User $user, string $status = 'present'): void
{
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => gateToday()->toDateString(),
        'status' => $status, 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

test('employee with no attendance today is redirected to attendance from dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertRedirect(route('employee.attendance.index'));
});

test('employee with todays present attendance can access dashboard', function () {
    $user = User::factory()->create();
    markGateAttendance($user, 'present');

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))->assertOk();
});

test('employee with todays half_day attendance can access dashboard', function () {
    $user = User::factory()->create();
    markGateAttendance($user, 'half_day');

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))->assertOk();
});

test('employee directly navigating to dashboard before marking attendance is still redirected', function () {
    $user = User::factory()->create();

    // Same scenario as the login-time redirect, but reached via direct
    // navigation later in the session rather than immediately after login —
    // proves the gate is route middleware, not a one-time login redirect.
    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertRedirect(route('employee.attendance.index'));
    $this->actingAs($user->fresh())->get(route('employee.expense-requests.index'))
        ->assertRedirect(route('employee.attendance.index'));
});

test('employee can access the attendance page before marking attendance', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.attendance.index'))->assertOk();
});

test('employee can mark attendance then access the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertRedirect(route('employee.attendance.index'));

    $this->actingAs($user->fresh())->post(route('employee.attendance.mark-present'))->assertRedirect();

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))->assertOk();
});

test('holiday today unlocks the dashboard without attendance', function () {
    $user = User::factory()->create();
    Holiday::create(['holiday_date' => gateToday()->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))->assertOk();
});

test('weekly off today unlocks the dashboard without attendance', function () {
    $user = User::factory()->create();
    Setting::set('weekly_off_days', json_encode([gateToday()->dayOfWeek]));

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))->assertOk();
});

test('approved leave today unlocks the dashboard without attendance', function () {
    $user = User::factory()->create();
    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => gateToday()->toDateString(), 'end_date' => gateToday()->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))->assertOk();
});

test('manager access is never affected by the attendance gate', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager->fresh())->get(route('manager.dashboard'))->assertOk();
});

test('admin access is never affected by the attendance gate', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin->fresh())->get(route('admin.dashboard'))->assertOk();
});

test('attendance and regularization routes remain accessible before attendance is marked', function () {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user)->get(route('employee.attendance.index'))->assertOk();
    $this->actingAs($user)->get(route('employee.attendance-regularizations.create'))->assertOk();
});

test('JSON endpoint returns a 409 JSON error instead of a redirect when attendance is unmarked', function () {
    $user = User::factory()->create()->fresh();

    $start = gateToday()->startOfMonth()->toDateString();
    $end   = gateToday()->endOfMonth()->toDateString();

    $response = $this->actingAs($user)
        ->getJson(route('employee.hall.bookings.calendar-events') . "?start={$start}&end={$end}");

    $response->assertStatus(409);
    $response->assertJson(['redirect' => route('employee.attendance.index')]);
});

test('normal browser navigation to the same route still redirects, not JSON', function () {
    $user = User::factory()->create()->fresh();

    $start = gateToday()->startOfMonth()->toDateString();
    $end   = gateToday()->endOfMonth()->toDateString();

    $this->actingAs($user)
        ->get(route('employee.hall.bookings.calendar-events') . "?start={$start}&end={$end}")
        ->assertRedirect(route('employee.attendance.index'));
});

// ── Real login endpoint (not actingAs()) ─────────────────────────────────

test('actual login redirects an employee with no todays attendance to the attendance page', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    // Login itself redirects to the normal employee destination (it has no
    // opinion on attendance) — the gate only fires when that destination is
    // actually requested, which is what following the redirect proves.
    $loginResponse->assertRedirect(route('employee.hall.bookings.calendar'));

    $this->get($loginResponse->headers->get('Location'))
        ->assertRedirect(route('employee.attendance.index'));
});

test('actual login allows an employee with todays attendance through to the normal destination', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    markGateAttendance($user, 'present');

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('employee.hall.bookings.calendar'));
});

test('intended URL cannot bypass the attendance gate on real login', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    // Simulate the employee having tried to visit a protected employee page
    // while logged out — Laravel's auth middleware stores this in
    // session('url.intended') and redirects to /login.
    $this->get(route('employee.overtime.index'))->assertRedirect('/login');

    $loginResponse = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    // redirect()->intended() sends the response to the previously-attempted
    // overtime page — that's expected, intended() itself doesn't know about
    // attendance. The real proof is the NEXT hop: following that intended
    // URL must bounce to attendance, not serve the overtime page.
    $loginResponse->assertRedirect(route('employee.overtime.index'));

    $this->get($loginResponse->headers->get('Location'))
        ->assertRedirect(route('employee.attendance.index'));
});

// ── Regularization sub-routes remain accessible before attendance ────────

test('regularization store/show/cancel routes remain accessible before attendance is marked', function () {
    $user = User::factory()->create()->fresh();

    // subDays(3), not subDay() — a safely-in-the-past weekday, avoiding the
    // chance that "yesterday" lands on a weekly-off day and the
    // regularization is (correctly) rejected for an unrelated reason.
    $this->actingAs($user)->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => gateToday()->copy()->subDays(3)->toDateString(),
        'requested_status' => 'present',
        'reason' => 'Forgot to mark attendance',
    ])->assertRedirect();

    $regularization = \App\Models\EmployeeAttendanceRegularization::first();
    expect($regularization)->not->toBeNull();

    $this->actingAs($user)->get(route('employee.attendance-regularizations.show', $regularization))->assertOk();
    $this->actingAs($user)->patch(route('employee.attendance-regularizations.cancel', $regularization))->assertRedirect();
});
