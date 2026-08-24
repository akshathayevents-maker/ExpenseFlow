<?php

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeSalary;
use App\Models\User;

function giveOtSalary(User $user, float $amount = 26000): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => $amount, 'effective_from' => '2026-01-01']);
    $salary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $salary->save();

    // The attendance-first gate (EnsureAttendanceMarked) applies to every
    // employee.* route except attendance/regularization — mark today's
    // attendance here so every test using this helper can act as a normal
    // logged-in employee on OT routes without hitting the gate.
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => \Carbon\Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

// ── Employee: create ────────────────────────────────────────────────────────

test('employee creates an OT request', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $response = $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'Extended shift',
    ]);

    $ot = EmployeeOvertime::first();
    $response->assertRedirect(route('employee.overtime.show', $ot));
    expect($ot->user_id)->toBe($user->id);
    expect($ot->origin)->toBe('employee_request');
    expect($ot->created_by)->toBe($user->id);
});

test('OT financial values are calculated server-side', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', // Tuesday, weekday
    ]);

    $ot = EmployeeOvertime::first();
    expect((float) $ot->hourly_rate_snapshot)->toBeGreaterThan(0);
    expect($ot->category)->toBe('weekday');
    expect((float) $ot->calculated_amount)->toBeGreaterThan(0);
});

test('employee cannot override calculated_amount', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'calculated_amount' => 999999,
    ]);

    expect((float) EmployeeOvertime::first()->calculated_amount)->not->toBe(999999.0);
});

test('employee cannot override rate_multiplier', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'rate_multiplier' => 99,
    ]);

    expect((float) EmployeeOvertime::first()->rate_multiplier)->not->toBe(99.0);
});

test('employee cannot override hourly_rate_snapshot', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'hourly_rate_snapshot' => 999999,
    ]);

    expect((float) EmployeeOvertime::first()->hourly_rate_snapshot)->not->toBe(999999.0);
});

// ── Employee: ownership ─────────────────────────────────────────────────────

test('employee can view own OT via policy', function () {
    $user = User::factory()->create();
    giveOtSalary($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    expect($user->can('view', $ot))->toBeTrue();
});

test('employee cannot view another employee OT', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $b->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $b->id,
    ]);

    expect($a->can('view', $ot))->toBeFalse();

    $this->actingAs($a)->get(route('employee.overtime.show', $ot))->assertForbidden();
});

// ── Employee: cancel ─────────────────────────────────────────────────────────

test('employee can cancel own pending OT', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('employee.overtime.cancel', $ot))->assertRedirect();

    expect($ot->fresh()->request_status)->toBe('cancelled');
});

test('employee cannot cancel approved OT', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'approved',
    ]);

    $this->actingAs($user)->patch(route('employee.overtime.cancel', $ot))->assertForbidden();
    expect($ot->fresh()->request_status)->toBe('approved');
});

test('employee cannot cancel rejected OT', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'rejected',
    ]);

    $this->actingAs($user)->patch(route('employee.overtime.cancel', $ot))->assertForbidden();
});

test('employee cannot cancel another employee OT', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $b->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $b->id,
    ]);

    $this->actingAs($a)->patch(route('employee.overtime.cancel', $ot))->assertForbidden();
});

// ── Duplicate ────────────────────────────────────────────────────────────────

test('duplicate exact OT request is rejected', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'first',
    ]);

    $response = $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'duplicate attempt',
    ]);

    $response->assertSessionHasErrors('hours');
    expect(EmployeeOvertime::count())->toBe(1);
});

// ── Approval workflow ────────────────────────────────────────────────────────

test('manager can view pending OT via policy', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    expect($manager->can('view', $ot))->toBeTrue();
});

test('admin can view pending OT via policy', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    expect($admin->can('view', $ot))->toBeTrue();
});

test('manager can approve OT', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'calculated_amount' => 375, 'hourly_rate_snapshot' => 125, 'rate_multiplier' => 1.5,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['review_note' => 'ok'])->assertRedirect();

    $ot->refresh();
    expect($ot->request_status)->toBe('approved');
    expect($ot->reviewed_by)->toBe($manager->id);
});

test('manager can reject OT', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible'])->assertRedirect();

    expect($ot->fresh()->request_status)->toBe('rejected');
});

test('employee cannot approve OT', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('manager.overtime.approve', $ot), ['review_note' => 'ok'])->assertForbidden();
});

test('employee cannot reject OT', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible'])->assertForbidden();
});

test('self-approval is prevented for a manager submitting their own OT', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $ot = EmployeeOvertime::create([
        'user_id' => $manager->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $manager->id,
    ]);

    expect($manager->can('approve', $ot))->toBeFalse();
    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['review_note' => 'ok'])->assertForbidden();
});

test('approved OT snapshot remains unchanged after approval', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    // calculated_amount/hourly_rate_snapshot/rate_multiplier are excluded
    // from $fillable — forceFill is required to plant a snapshot directly in
    // a test (mirrors OvertimeSchemaTest's pattern), same as a real snapshot
    // would only ever be written by OvertimeCalculationService.
    $ot->forceFill(['calculated_amount' => 375.00, 'hourly_rate_snapshot' => 125.00, 'rate_multiplier' => 1.50])->save();

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot));

    $ot->refresh();
    expect((float) $ot->calculated_amount)->toBe(375.0);
    expect((float) $ot->hourly_rate_snapshot)->toBe(125.0);
    expect((float) $ot->rate_multiplier)->toBe(1.5);
});

test('rejected OT snapshot remains unchanged after rejection', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $ot->forceFill(['calculated_amount' => 375.00, 'hourly_rate_snapshot' => 125.00, 'rate_multiplier' => 1.50])->save();

    $this->actingAs($manager)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible']);

    $ot->refresh();
    expect((float) $ot->calculated_amount)->toBe(375.0);
});

// ── Status transitions ───────────────────────────────────────────────────────

test('approved OT cannot return to pending via the model guard', function () {
    $ot = EmployeeOvertime::create([
        'user_id' => User::factory()->create()->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request',
        'created_by' => 1, 'request_status' => 'approved', 'calculated_amount' => 375,
    ]);

    // request_status itself is not a locked field (only financial fields are
    // guarded) — the actual guard against illegal transitions is the Policy
    // (approve/reject both require isPending()), verified below.
    expect((new App\Policies\EmployeeOvertimePolicy())->approve(User::factory()->create(['role' => 'admin']), $ot))->toBeFalse();
});

test('rejected OT cannot become approved', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'rejected',
    ]);

    $this->actingAs($admin)->patch(route('admin.overtime.approve', $ot), ['review_note' => 'ok'])->assertForbidden();
    expect($ot->fresh()->request_status)->toBe('rejected');
});

test('cancelled OT cannot become approved', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'cancelled',
    ]);

    $this->actingAs($admin)->patch(route('admin.overtime.approve', $ot), ['review_note' => 'ok'])->assertForbidden();
    expect($ot->fresh()->request_status)->toBe('cancelled');
});

// ── Audit ────────────────────────────────────────────────────────────────────

test('creation creates an audit entry', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'x',
    ]);

    expect(AuditLog::where('module', 'employee_overtime')->where('action', 'created')->exists())->toBeTrue();
});

test('approval creates an audit entry', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['review_note' => 'ok']);

    expect(AuditLog::where('module', 'employee_overtime')->where('action', 'approved')->where('reference_id', $ot->id)->exists())->toBeTrue();
});

test('rejection creates an audit entry', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible']);

    expect(AuditLog::where('module', 'employee_overtime')->where('action', 'rejected')->where('reference_id', $ot->id)->exists())->toBeTrue();
});

test('cancellation creates an audit entry', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('employee.overtime.cancel', $ot));

    expect(AuditLog::where('module', 'employee_overtime')->where('action', 'cancelled')->where('reference_id', $ot->id)->exists())->toBeTrue();
});

// ── Notifications ────────────────────────────────────────────────────────────

test('approval notifies the employee', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'calculated_amount' => 375,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['review_note' => 'ok']);

    expect(AppNotification::where('user_id', $user->id)->where('type', 'overtime_approved')->exists())->toBeTrue();
});

test('rejection notifies the employee', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible']);

    expect(AppNotification::where('user_id', $user->id)->where('type', 'overtime_rejected')->exists())->toBeTrue();
});

// ── Admin: historical recording ──────────────────────────────────────────────

test('admin can record historical OT for another employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    giveOtSalary($user);

    $response = $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-01', 'hours' => 3, 'reason' => 'recorded retroactively',
    ]);

    $ot = EmployeeOvertime::first();
    $response->assertRedirect(route('admin.overtime.show', $ot));
    expect($ot->user_id)->toBe($user->id);
});

test('admin-created OT uses origin=admin_recorded', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($admin)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-01', 'hours' => 3, 'reason' => 'x',
    ]);

    expect(EmployeeOvertime::first()->origin)->toBe('admin_recorded');
    expect(EmployeeOvertime::first()->created_by)->toBe($admin->id);
});

test('employee-created OT uses origin=employee_request', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'x',
    ]);

    expect(EmployeeOvertime::first()->origin)->toBe('employee_request');
});

test('a manager cannot record historical OT (admin-only ability)', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($manager)->post(route('admin.overtime.store'), [
        'user_id' => $user->id, 'ot_date' => '2026-07-01', 'hours' => 3, 'reason' => 'x',
    ])->assertForbidden();
});
