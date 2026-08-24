<?php

use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;

function makeLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-' . uniqid(), 'allow_half_day' => true, 'is_active' => true,
    ], $attrs));
}

// ── Attendance gate exemption ────────────────────────────────────────────

test('employee can open and apply for leave without marking attendance first', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $this->actingAs($user->fresh())->get(route('employee.leave.index'))->assertOk();
    $this->actingAs($user->fresh())->get(route('employee.leave.create'))->assertOk();

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-01', // Tuesday
        'end_date'      => '2026-09-02', // Wednesday
        'reason'        => 'Personal work',
    ])->assertRedirect();

    expect(LeaveRequest::count())->toBe(1);
});

test('attendance gate is unaffected — dashboard still redirects to attendance when unmarked', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertRedirect(route('employee.attendance.index'));
});

// ── Employee: create ─────────────────────────────────────────────────────

test('employee can apply for leave', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $response = $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-01', // Tuesday
        'end_date'      => '2026-09-02', // Wednesday
        'reason'        => 'Personal work',
    ]);

    $leaveRequest = LeaveRequest::first();
    $response->assertRedirect(route('employee.leave.show', $leaveRequest));
    expect($leaveRequest->user_id)->toBe($user->id);
    expect($leaveRequest->status)->toBe('pending');
    expect((float) $leaveRequest->days_requested)->toBe(2.0);
});

test('days_requested excludes weekly-off days within the range', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    // Sat 5 Sep, Sun 6 Sep, Mon 7 Sep — Sunday is the configured weekly off.
    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-05',
        'end_date'      => '2026-09-07',
        'reason'        => 'Long weekend',
    ])->assertRedirect();

    expect((float) LeaveRequest::first()->days_requested)->toBe(2.0);
});

test('half day leave request forces single day and 0.5 days', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id'   => $type->id,
        'start_date'      => '2026-09-01',
        'end_date'        => '2026-09-01',
        'is_half_day'     => 1,
        'half_day_period' => 'first_half',
        'reason'          => 'Appointment',
    ])->assertRedirect();

    $leaveRequest = LeaveRequest::first();
    expect((float) $leaveRequest->days_requested)->toBe(0.5);
    expect($leaveRequest->end_date->toDateString())->toBe('2026-09-01');
});

test('half day request is rejected when the leave type does not allow half days', function () {
    $user = User::factory()->create();
    $type = makeLeaveType(['allow_half_day' => false]);

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id'   => $type->id,
        'start_date'      => '2026-09-01',
        'end_date'        => '2026-09-01',
        'is_half_day'     => 1,
        'half_day_period' => 'first_half',
        'reason'          => 'Appointment',
    ])->assertSessionHasErrors('is_half_day');

    expect(LeaveRequest::count())->toBe(0);
});

test('reason is required when applying for leave', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-01',
        'end_date'      => '2026-09-02',
    ])->assertSessionHasErrors('reason');

    expect(LeaveRequest::count())->toBe(0);
});

test('end date before start date is rejected', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-05',
        'end_date'      => '2026-09-01',
        'reason'        => 'Test',
    ])->assertSessionHasErrors('end_date');

    expect(LeaveRequest::count())->toBe(0);
});

test('past-dated leave requests are allowed', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2020-01-06', // Monday, well in the past
        'end_date'      => '2020-01-06',
        'reason'        => 'Backfilled request',
    ])->assertRedirect();

    expect(LeaveRequest::count())->toBe(1);
});

// ── Overlap protection ───────────────────────────────────────────────────

test('overlapping pending leave request is rejected', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-10', 'end_date' => '2026-09-15',
        'days_requested' => 5, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-14',
        'end_date'      => '2026-09-18',
        'reason'        => 'y',
    ])->assertSessionHasErrors('start_date');

    expect(LeaveRequest::count())->toBe(1);
});

test('overlapping approved leave request is rejected', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-10', 'end_date' => '2026-09-15',
        'days_requested' => 5, 'reason' => 'x', 'status' => 'approved',
    ]);

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-08',
        'end_date'      => '2026-09-11',
        'reason'        => 'y',
    ])->assertSessionHasErrors('start_date');

    expect(LeaveRequest::count())->toBe(1);
});

test('a rejected or cancelled leave request does not block an overlapping new request', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-10', 'end_date' => '2026-09-15',
        'days_requested' => 5, 'reason' => 'x', 'status' => 'rejected',
    ]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-10', 'end_date' => '2026-09-15',
        'days_requested' => 5, 'reason' => 'x', 'status' => 'cancelled',
    ]);

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-12',
        'end_date'      => '2026-09-13',
        'reason'        => 'y',
    ])->assertRedirect();

    expect(LeaveRequest::where('status', 'pending')->count())->toBe(1);
});

test('non-overlapping leave request is allowed', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-10', 'end_date' => '2026-09-11',
        'days_requested' => 2, 'reason' => 'x', 'status' => 'approved',
    ]);

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-20',
        'end_date'      => '2026-09-21',
        'reason'        => 'y',
    ])->assertRedirect();

    expect(LeaveRequest::count())->toBe(2);
});

test('adjacent (back-to-back) leave dates are allowed', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-10', 'end_date' => '2026-09-11',
        'days_requested' => 2, 'reason' => 'x', 'status' => 'approved',
    ]);

    // Starts the day right after the existing request ends — must not
    // be treated as an overlap.
    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id,
        'start_date'    => '2026-09-12',
        'end_date'      => '2026-09-13',
        'reason'        => 'y',
    ])->assertRedirect();

    expect(LeaveRequest::count())->toBe(2);
});

// ── Employee: view/cancel ────────────────────────────────────────────────

test('employee can see own leave requests', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($user->fresh())->get(route('employee.leave.index'))->assertOk()->assertSee($type->name);
    $this->actingAs($user->fresh())->get(route('employee.leave.show', $leaveRequest))->assertOk();
});

test('employee cannot see another employees leave request', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $type = makeLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $b->id, 'leave_type_id' => $type->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($a->fresh())->get(route('employee.leave.show', $leaveRequest))->assertForbidden();
});

test('employee can cancel own pending leave request', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($user->fresh())->patch(route('employee.leave.cancel', $leaveRequest))->assertRedirect();

    expect($leaveRequest->fresh()->status)->toBe('cancelled');
});

test('employee cannot cancel another employees leave request', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $type = makeLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $b->id, 'leave_type_id' => $type->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'pending',
    ]);

    $this->actingAs($a->fresh())->patch(route('employee.leave.cancel', $leaveRequest))->assertForbidden();
});

test('cannot cancel an already approved leave request', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();
    $leaveRequest = hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01',
        'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $this->actingAs($user->fresh())->patch(route('employee.leave.cancel', $leaveRequest))->assertForbidden();
});

// ── Audit ────────────────────────────────────────────────────────────────

test('leave request and cancellation are audited', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $this->actingAs($user->fresh())->post(route('employee.leave.store'), [
        'leave_type_id' => $type->id, 'start_date' => '2026-09-01', 'end_date' => '2026-09-01', 'reason' => 'x',
    ]);
    $leaveRequest = LeaveRequest::first();
    expect(AuditLog::where('module', 'leave_request')->where('action', 'requested')->exists())->toBeTrue();

    $this->actingAs($user->fresh())->patch(route('employee.leave.cancel', $leaveRequest));
    expect(AuditLog::where('module', 'leave_request')->where('action', 'cancelled')->exists())->toBeTrue();
});

// ── Navigation / UX ──────────────────────────────────────────────────────

test('employee navigation exposes Leave under My Work', function () {
    $user = User::factory()->create();

    // Attendance page still requires the gate itself — mark attendance so
    // this purely-navigational assertion isn't tangled with the gate redirect.
    App\Models\EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => \Carbon\Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $this->actingAs($user->fresh())->get(route('employee.attendance.index'))
        ->assertOk()
        ->assertSee('Leave');
});

test('attendance page shows Apply Leave and My Leave entry points', function () {
    $user = User::factory()->create();
    App\Models\EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => \Carbon\Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $this->actingAs($user->fresh())->get(route('employee.attendance.index'))
        ->assertOk()
        ->assertSee('Apply Leave')
        ->assertSee('My Leave')
        ->assertSee(route('employee.leave.create'), false)
        ->assertSee(route('employee.leave.index'), false);
});

// ── Mass-assignment hardening ─────────────────────────────────────────────

test('status and reviewed_by cannot be set via direct mass assignment on LeaveRequest', function () {
    $user = User::factory()->create();
    $type = makeLeaveType();

    $leaveRequest = LeaveRequest::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-09-01', 'end_date' => '2026-09-01', 'days_requested' => 1, 'reason' => 'x',
        'status' => 'approved',
    ]);

    expect($leaveRequest->fresh()->status)->toBe('pending');
});
