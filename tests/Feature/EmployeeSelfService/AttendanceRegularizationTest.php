<?php

use App\Models\AuditLog;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceRegularization;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\EmployeeAttendanceService;

function regService(): EmployeeAttendanceService
{
    return app(EmployeeAttendanceService::class);
}

// A safely-in-the-past date, so it is never a future date.
function regTestDate(): \Carbon\Carbon
{
    return regService()->today()->copy()->subDays(3);
}

// The `settings` table seeds a DB default of weekly_off_days = [0] (Sunday)
// via migration (see database/migrations/2026_08_24_090003_create_holidays_table.php).
// regTestDate() (and the ad-hoc subDay()/subDays() offsets built from it
// further down this file) is a relative date computed from the real
// wall-clock "today" — on any real calendar day where that offset lands on a
// Sunday, assertRegularizable() would (correctly) reject it as a weekly-off
// day, silently failing the regularization creation and cascading into
// unrelated assertions. Neutralize weekly-off globally, matching the
// established convention in AttendanceLeaveConflictTest.php's
// alcNoWeeklyOff() helper — individual tests that need to exercise
// weekly-off behavior explicitly call Setting::set('weekly_off_days', ...)
// again afterward, which overrides this.
beforeEach(function () {
    Setting::set('weekly_off_days', '[]');
});

// ── Employee: create ─────────────────────────────────────────────────────

test('employee can open regularization form', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.attendance-regularizations.create'))->assertOk();
});

test('employee can submit regularization', function () {
    $user = User::factory()->create();
    $date = regTestDate();

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'Forgot to mark attendance',
    ]);

    $reg = EmployeeAttendanceRegularization::first();
    $response->assertRedirect(route('employee.attendance-regularizations.show', $reg));
    expect($reg->user_id)->toBe($user->id);
    expect($reg->requested_status)->toBe('present');
    expect($reg->request_status)->toBe('pending');
    expect($reg->created_by)->toBe($user->id);
});

test('reason is optional and submission succeeds without one', function () {
    $user = User::factory()->create();
    $date = regTestDate();

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present',
    ]);

    $response->assertSessionDoesntHaveErrors('reason');
    $reg = EmployeeAttendanceRegularization::first();
    expect($reg)->not->toBeNull();
    expect($reg->reason)->toBe('');
});

test('reason is still accepted and stored when provided', function () {
    $user = User::factory()->create();
    $date = regTestDate();

    $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'Forgot to mark attendance',
    ]);

    expect(EmployeeAttendanceRegularization::first()->reason)->toBe('Forgot to mark attendance');
});

test('future date is blocked', function () {
    $user = User::factory()->create();
    $future = regService()->today()->copy()->addDay();

    $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $future->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ])->assertSessionHasErrors('attendance_date');

    expect(EmployeeAttendanceRegularization::count())->toBe(0);
});

test('holiday date is blocked', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    Holiday::create(['holiday_date' => $date->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);

    $response->assertSessionHasErrors('attendance_date');
    expect(EmployeeAttendanceRegularization::count())->toBe(0);
});

test('weekly off date is blocked', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    Setting::set('weekly_off_days', json_encode([$date->dayOfWeek]));

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);

    $response->assertSessionHasErrors('attendance_date');
    expect(EmployeeAttendanceRegularization::count())->toBe(0);
});

test('employee cannot submit for another employee', function () {
    // Structurally impossible: store() never accepts a user_id from the
    // client — it always targets auth()->user(). Verified by confirming the
    // created record's user_id always matches the acting user, never an
    // arbitrary supplied one.
    $a = User::factory()->create();
    $date = regTestDate();

    $this->actingAs($a->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'x', 'user_id' => 99999,
    ]);

    expect(EmployeeAttendanceRegularization::first()->user_id)->toBe($a->id);
});

test('duplicate pending request for the same date is blocked', function () {
    $user = User::factory()->create();
    $date = regTestDate();

    $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'first',
    ]);
    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'half_day', 'reason' => 'second',
    ]);

    $response->assertSessionHasErrors('attendance_date');
    expect(EmployeeAttendanceRegularization::count())->toBe(1);
});

// ── Employee: view/cancel ────────────────────────────────────────────────

test('employee can view own regularization request', function () {
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->get(route('employee.attendance-regularizations.show', $reg))->assertOk();
});

test('employee cannot view another employees regularization request', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $b->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $b->id,
    ]);

    $this->actingAs($a)->get(route('employee.attendance-regularizations.show', $reg))->assertForbidden();
});

test('employee can cancel pending request', function () {
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('employee.attendance-regularizations.cancel', $reg))->assertRedirect();

    expect($reg->fresh()->request_status)->toBe('cancelled');
});

test('employee cannot cancel an approved request', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);
    $this->actingAs($manager);
    regService()->approveRegularization($reg, $manager);

    $this->actingAs($user)->patch(route('employee.attendance-regularizations.cancel', $reg))->assertForbidden();
    expect($reg->fresh()->request_status)->toBe('approved');
});

// ── Manager/Admin ─────────────────────────────────────────────────────────

test('pending request is visible to manager', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->get(route('manager.attendance-regularizations.index'))
        ->assertOk()
        ->assertSee($user->name);
});

test('manager can approve', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.attendance-regularizations.approve', $reg), ['review_note' => 'ok'])->assertRedirect();

    $reg->refresh();
    expect($reg->request_status)->toBe('approved');
    expect($reg->reviewed_by)->toBe($manager->id);
});

test('manager can reject', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.attendance-regularizations.reject', $reg), ['review_note' => 'not eligible'])->assertRedirect();

    expect($reg->fresh()->request_status)->toBe('rejected');
});

test('reject requires a note', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.attendance-regularizations.reject', $reg), ['review_note' => ''])
        ->assertSessionHasErrors('review_note');
});

test('unauthorized user cannot approve or reject', function () {
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('manager.attendance-regularizations.approve', $reg), ['review_note' => 'ok'])->assertForbidden();
});

test('self-approval is prevented for a manager regularizing their own attendance', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $manager->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $manager->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.attendance-regularizations.approve', $reg), ['review_note' => 'ok'])->assertForbidden();
});

// ── Approval → attendance effect ─────────────────────────────────────────

test('approval with no existing attendance creates a new attendance row', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager);
    regService()->approveRegularization($reg, $manager, 'ok');

    $attendance = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->status)->toBe('present');
    expect($attendance->source)->toBe('admin');
    expect($attendance->corrected_by)->toBe($manager->id);
});

test('approval with existing attendance updates it and records the previous status', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'status' => 'half_day', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager);
    regService()->approveRegularization($reg, $manager, 'ok');

    $attendance = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->first();
    expect($attendance->status)->toBe('present');
    expect($attendance->previous_status)->toBe('half_day');
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->count())->toBe(1);
});

test('approval and attendance update happen atomically', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    // Force the attendance write to fail mid-transaction and confirm the
    // regularization approval rolled back entirely rather than partially
    // completing.
    EmployeeAttendance::creating(function () {
        throw new \RuntimeException('forced failure for atomicity test');
    });

    $this->actingAs($manager);
    try {
        regService()->approveRegularization($reg, $manager, 'ok');
    } catch (\RuntimeException $e) {
        // expected
    }

    expect($reg->fresh()->request_status)->toBe('pending');
    expect($reg->fresh()->reviewed_by)->toBeNull();
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->count())->toBe(0);
});

// ── Leave integration ─────────────────────────────────────────────────────

test('approved leave blocks regularization for the same date', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $date->toDateString(), 'end_date' => $date->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);

    $response->assertSessionHasErrors('attendance_date');
    expect(EmployeeAttendanceRegularization::count())->toBe(0);
});

// ── Status immutability ───────────────────────────────────────────────────

test('approved request cannot be approved or rejected again', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $another = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);
    $this->actingAs($manager);
    regService()->approveRegularization($reg, $manager, 'ok');

    $this->actingAs($another)->patch(route('admin.attendance-regularizations.approve', $reg), ['review_note' => 'ok'])->assertForbidden();
    $this->actingAs($another)->patch(route('admin.attendance-regularizations.reject', $reg), ['review_note' => 'no longer applicable'])->assertForbidden();
    expect($reg->fresh()->request_status)->toBe('approved');
});

test('rejected request remains unchanged and is not payable/actionable', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);
    $this->actingAs($manager);
    regService()->rejectRegularization($reg, $manager, 'not eligible');

    expect($reg->fresh()->request_status)->toBe('rejected');
    expect(EmployeeAttendance::where('user_id', $user->id)->count())->toBe(0);
});

test('cancelled request remains in history, not deleted', function () {
    $user = User::factory()->create();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);
    $this->actingAs($user);
    regService()->cancelRegularization($reg, $user);

    expect(EmployeeAttendanceRegularization::find($reg->id))->not->toBeNull();
    expect($reg->fresh()->request_status)->toBe('cancelled');
});

// ── Audit ─────────────────────────────────────────────────────────────────

test('submission, approval, rejection and cancellation are all audited', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();

    $this->actingAs($user);
    $reg = regService()->createRegularization($user, [
        'attendance_date' => regTestDate()->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);
    expect(AuditLog::where('module', 'employee_attendance_regularization')->where('action', 'submitted')->exists())->toBeTrue();

    regService()->approveRegularization($reg, $manager, 'ok');
    expect(AuditLog::where('module', 'employee_attendance_regularization')->where('action', 'approved')->exists())->toBeTrue();

    $reg2 = regService()->createRegularization($user, [
        'attendance_date' => regTestDate()->copy()->subDay()->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);
    regService()->rejectRegularization($reg2, $manager, 'no');
    expect(AuditLog::where('module', 'employee_attendance_regularization')->where('action', 'rejected')->exists())->toBeTrue();

    $reg3 = regService()->createRegularization($user, [
        'attendance_date' => regTestDate()->copy()->subDays(2)->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);
    regService()->cancelRegularization($reg3, $user);
    expect(AuditLog::where('module', 'employee_attendance_regularization')->where('action', 'cancelled')->exists())->toBeTrue();
});

// ── Mass-assignment security ─────────────────────────────────────────────

test('employee cannot mass-assign request_status/reviewed_by/reviewed_at/review_note on create', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();

    $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date'  => $date->toDateString(),
        'requested_status' => 'present',
        'reason'           => 'x',
        'request_status'   => 'approved',
        'reviewed_by'      => $manager->id,
        'reviewed_at'      => now()->toDateTimeString(),
        'review_note'      => 'forged approval',
    ]);

    $reg = EmployeeAttendanceRegularization::first();
    expect($reg)->not->toBeNull();
    expect($reg->request_status)->toBe('pending');
    expect($reg->reviewed_by)->toBeNull();
    expect($reg->reviewed_at)->toBeNull();
    expect($reg->review_note)->toBeNull();
});

test('direct mass-assignment of protected fields via create() is silently ignored by the model', function () {
    $user = User::factory()->create();

    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => regTestDate()->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
        'request_status' => 'approved', 'reviewed_by' => 999, 'review_note' => 'forged',
    ]);

    // request_status has no in-memory value until refetched (DB DEFAULT
    // 'pending' applies at the storage layer, not the in-memory attribute
    // bag) — fresh() reloads it to confirm the injected 'approved' never
    // reached the database.
    expect($reg->fresh()->request_status)->toBe('pending');
    expect($reg->reviewed_by)->toBeNull();
    expect($reg->review_note)->toBeNull();
});

// ── Resubmission after rejection/cancellation ────────────────────────────

test('rejected request can be resubmitted for the same employee and date', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'first attempt', 'created_by' => $user->id,
    ]);
    $this->actingAs($manager);
    regService()->rejectRegularization($reg, $manager, 'not eligible');

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'second attempt',
    ]);

    $response->assertRedirect();
    expect(EmployeeAttendanceRegularization::where('user_id', $user->id)->count())->toBe(2);
    expect(EmployeeAttendanceRegularization::latest('id')->first()->reason)->toBe('second attempt');
});

test('cancelled request can be resubmitted for the same employee and date', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'first attempt', 'created_by' => $user->id,
    ]);
    $this->actingAs($user);
    regService()->cancelRegularization($reg, $user);

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'second attempt',
    ]);

    $response->assertRedirect();
    expect(EmployeeAttendanceRegularization::where('user_id', $user->id)->count())->toBe(2);
});

// ── Approval-time race conditions ────────────────────────────────────────

test('approval fails if approved leave is created after submission but before approval', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $date->toDateString(), 'end_date' => $date->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $this->actingAs($manager);
    expect(fn () => regService()->approveRegularization($reg, $manager, 'ok'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);

    $reg->refresh();
    expect($reg->request_status)->toBe('pending');
    expect($reg->reviewed_by)->toBeNull();
    expect($reg->reviewed_at)->toBeNull();
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->count())->toBe(0);
});

test('approval fails if a holiday is added after submission but before approval', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    Holiday::create(['holiday_date' => $date->toDateString(), 'name' => 'Surprise Holiday', 'is_active' => true]);

    $this->actingAs($manager);
    expect(fn () => regService()->approveRegularization($reg, $manager, 'ok'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);

    $reg->refresh();
    expect($reg->request_status)->toBe('pending');
    expect($reg->reviewed_by)->toBeNull();
    expect($reg->reviewed_at)->toBeNull();
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->count())->toBe(0);
});

test('approval fails if the weekly-off configuration changes after submission but before approval', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    Setting::set('weekly_off_days', json_encode([$date->dayOfWeek]));

    $this->actingAs($manager);
    expect(fn () => regService()->approveRegularization($reg, $manager, 'ok'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);

    $reg->refresh();
    expect($reg->request_status)->toBe('pending');
    expect($reg->reviewed_by)->toBeNull();
    expect($reg->reviewed_at)->toBeNull();
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->count())->toBe(0);
});

// ── Half-day approval ─────────────────────────────────────────────────────

test('existing present attendance regularized to half_day updates correctly with no duplicate row', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'half_day', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager);
    regService()->approveRegularization($reg, $manager, 'ok');

    $rows = EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->get();
    expect($rows)->toHaveCount(1);
    expect($rows->first()->status)->toBe('half_day');
    expect($rows->first()->previous_status)->toBe('present');
    expect($rows->first()->corrected_by)->toBe($manager->id);
});

// ── Attendance page date-selection flow ──────────────────────────────────

test('attendance page defaults to today when no date is supplied', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('employee.attendance.index'));

    $response->assertOk();
    $response->assertViewHas('selectedDate', fn ($date) => $date->isSameDay(regService()->today()));
});

test('attendance page accepts a valid past date', function () {
    $user = User::factory()->create();
    $date = regTestDate();

    $response = $this->actingAs($user)->get(route('employee.attendance.index', ['date' => $date->toDateString()]));

    $response->assertOk();
    $response->assertViewHas('selectedDate', fn ($d) => $d->isSameDay($date));
});

test('selected date displays the correct current attendance status', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'status' => 'half_day', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $this->actingAs($user)->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['attendance']?->status === 'half_day');
});

test('selected holiday is shown as non-regularizable', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    Holiday::create(['holiday_date' => $date->toDateString(), 'name' => 'Test Holiday', 'is_active' => true]);

    $this->actingAs($user)->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['eligible'] === false && $state['category'] === 'holiday')
        ->assertSee('Holiday');
});

test('selected weekly-off date is shown as non-regularizable', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    Setting::set('weekly_off_days', json_encode([$date->dayOfWeek]));

    $this->actingAs($user)->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['eligible'] === false && $state['category'] === 'weekend')
        ->assertSee('Weekly Off');
});

test('selected approved-leave date is shown as non-regularizable', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    $leaveType = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'is_active' => true]);
    hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $leaveType->id,
        'start_date' => $date->toDateString(), 'end_date' => $date->toDateString(),
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'x', 'status' => 'approved',
    ]);

    $this->actingAs($user)->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['eligible'] === false && $state['has_approved_leave'] === true)
        ->assertSee('Approved Leave');
});

test('selected date with a pending regularization shows the pending request', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['eligible'] === false && $state['pending_regularization']?->id === $reg->id)
        ->assertSee('already submitted');
});

test('rejected regularization allows resubmission from the attendance page', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);
    $this->actingAs($manager);
    regService()->rejectRegularization($reg, $manager, 'no');

    $this->actingAs($user->fresh())->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['eligible'] === true);
});

test('cancelled regularization allows resubmission from the attendance page', function () {
    $user = User::factory()->create();
    $date = regTestDate();
    $reg = EmployeeAttendanceRegularization::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'requested_status' => 'present', 'reason' => 'x', 'created_by' => $user->id,
    ]);
    $this->actingAs($user);
    regService()->cancelRegularization($reg, $user);

    $this->actingAs($user->fresh())->get(route('employee.attendance.index', ['date' => $date->toDateString()]))
        ->assertOk()
        ->assertViewHas('dayState', fn ($state) => $state['eligible'] === true);
});

test('a valid date can submit regularization from the attendance page form', function () {
    $user = User::factory()->create();
    $date = regTestDate();

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'Submitted from attendance page',
    ]);

    $reg = EmployeeAttendanceRegularization::first();
    $response->assertRedirect(route('employee.attendance-regularizations.show', $reg));
    expect($reg->reason)->toBe('Submitted from attendance page');
});

test('existing attendance is updated after approval rather than duplicated (via the page-driven flow)', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $date = regTestDate();
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date->toDateString(),
        'status' => 'half_day', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $date->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);
    $reg = EmployeeAttendanceRegularization::first();

    $this->actingAs($manager);
    regService()->approveRegularization($reg, $manager, 'ok');

    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->count())->toBe(1);
    expect(EmployeeAttendance::where('user_id', $user->id)->whereDate('attendance_date', $date->toDateString())->first()->status)->toBe('present');
});

test('future date remains blocked server-side even if manually submitted from the page form', function () {
    $user = User::factory()->create();
    $future = regService()->today()->copy()->addDay();

    $response = $this->actingAs($user->fresh())->post(route('employee.attendance-regularizations.store'), [
        'attendance_date' => $future->toDateString(), 'requested_status' => 'present', 'reason' => 'x',
    ]);

    $response->assertSessionHasErrors('attendance_date');
    expect(EmployeeAttendanceRegularization::count())->toBe(0);
});
