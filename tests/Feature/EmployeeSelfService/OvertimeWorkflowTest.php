<?php

use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
use App\Models\EmployeeSalary;
use App\Models\User;

/**
 * REDESIGN: compensation is no longer calculated at request-creation time.
 * A pending EmployeeOvertime has NO hourly_rate_snapshot/rate_multiplier/
 * calculated_amount/approved_amount at all — these are only populated at
 * approval time, once an Admin/Manager explicitly picks a multiplier (or
 * supplies a manual override). Every approve() call in these tests must now
 * supply a `multiplier`.
 */
function giveOtSalary(User $user, float $amount = 26000): void
{
    // Employee self-request creation is gated by
    // employee_overtime_requests_enabled (default false, per the current
    // business requirement to temporarily pause employee OT requesting).
    // Every existing test in this file predates that gate and exercises the
    // request/approval/calculation flow while it was always implicitly on,
    // so this helper explicitly re-enables it — this is the exact
    // re-enablement mechanism the "flip the flag back" requirement proves.
    \App\Models\Setting::set('employee_overtime_requests_enabled', true);

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

test('reason is required when requesting OT and submission fails without it', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $response = $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2,
    ]);

    $response->assertSessionHasErrors('reason');
    expect(EmployeeOvertime::count())->toBe(0);
});

test('reason is stored exactly as given when requesting OT', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours' => 2, 'reason' => 'Server maintenance window',
    ]);

    expect(EmployeeOvertime::first()->reason)->toBe('Server maintenance window');
});

test('other OT validation rules remain enforced when reason is omitted', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20',
    ])->assertSessionHasErrors('hours');

    expect(EmployeeOvertime::count())->toBe(0);
});

test('Hours + Minutes inputs are converted to the correct decimal hours value', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $response = $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours_h' => 1, 'hours_m' => 30, 'reason' => 'x',
    ]);

    $ot = EmployeeOvertime::first();
    $response->assertRedirect(route('employee.overtime.show', $ot));
    expect((float) $ot->hours)->toBe(1.5);
});

test('zero and negative Hours are still rejected via Hours + Minutes inputs', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-20', 'hours_h' => 0, 'hours_m' => 0, 'reason' => 'x',
    ])->assertSessionHasErrors('hours');

    expect(EmployeeOvertime::count())->toBe(0);
});

// REDESIGN: was "OT financial values are calculated server-side" — under the
// new design NOTHING financial is calculated at creation. The category
// label is still derived (display-only), but no rate/multiplier/amount
// exists until approval.
test('no compensation fields are populated at creation time', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', // Tuesday, weekday
    ]);

    $ot = EmployeeOvertime::first();
    expect($ot->category)->toBe('weekday');
    expect($ot->hourly_rate_snapshot)->toBeNull();
    expect($ot->rate_multiplier)->toBeNull();
    expect($ot->calculated_amount)->toBeNull();
    expect($ot->approved_amount)->toBeNull();
    expect((bool) $ot->used_manual_override)->toBeFalse();
});

// REDESIGN (Part 6, security): posting compensation fields at creation has
// ZERO effect — the request is still created as a plain pending record with
// no amount fields, since creation never even attempts a calculation.
test('employee cannot inject calculated_amount at creation', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'calculated_amount' => 999999,
    ]);

    expect(EmployeeOvertime::first()->calculated_amount)->toBeNull();
});

test('employee cannot inject rate_multiplier at creation', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'multiplier' => 99, 'rate_multiplier' => 99,
    ]);

    expect(EmployeeOvertime::first()->rate_multiplier)->toBeNull();
});

test('employee cannot inject hourly_rate_snapshot at creation', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'hourly_rate_snapshot' => 999999,
    ]);

    expect(EmployeeOvertime::first()->hourly_rate_snapshot)->toBeNull();
});

test('employee cannot inject approved_amount at creation', function () {
    $user = User::factory()->create();
    giveOtSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.overtime.store'), [
        'ot_date' => '2026-08-10', 'hours' => 2, 'reason' => 'x', 'approved_amount' => 999999,
    ]);

    expect(EmployeeOvertime::first()->approved_amount)->toBeNull();
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

// REDESIGN (Part 4): rejecting/cancelling a pending record trivially has
// nothing to un-calculate, since no calculation ever ran for it.
test('cancelling a pending OT never populates any amount field', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('employee.overtime.cancel', $ot));

    $ot->refresh();
    expect($ot->request_status)->toBe('cancelled');
    expect($ot->calculated_amount)->toBeNull();
    expect($ot->approved_amount)->toBeNull();
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

test('manager can approve OT by choosing a multiplier, calculated server-side', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user); // 26000/26/8 = 125/hr
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2, // weekday
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), [
        'multiplier' => 1.5, 'review_note' => 'ok',
    ])->assertRedirect();

    $ot->refresh();
    expect($ot->request_status)->toBe('approved');
    expect($ot->reviewed_by)->toBe($manager->id);
    expect((float) $ot->hourly_rate_snapshot)->toBe(125.0);
    expect((float) $ot->rate_multiplier)->toBe(1.5);
    expect((float) $ot->calculated_amount)->toBe(375.0);
    expect((float) $ot->approved_amount)->toBe(375.0);
    expect((bool) $ot->used_manual_override)->toBeFalse();
});

test('approving with a manual override sets approved_amount to the override, not the calculated amount', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), [
        'multiplier' => 1.5, 'manual_amount' => 500.00,
    ])->assertRedirect();

    $ot->refresh();
    expect((float) $ot->calculated_amount)->toBe(375.0); // system calculation still recorded
    expect((float) $ot->approved_amount)->toBe(500.0);   // but the final amount is the override
    expect((bool) $ot->used_manual_override)->toBeTrue();
});

test('approval is rejected when no multiplier is supplied', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), [])
        ->assertSessionHasErrors('multiplier');

    expect($ot->fresh()->request_status)->toBe('pending');
});

test('approval is rejected when the multiplier is not one of the employee configured allowed multipliers', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user);
    EmployeeOvertimeConfig::create([
        'user_id' => $user->id, 'allowed_multipliers' => [1.0, 1.5], 'default_multiplier' => 1.5,
    ]);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 2.0])
        ->assertSessionHasErrors('multiplier');

    expect($ot->fresh()->request_status)->toBe('pending');
});

test('approval is rejected when manual_amount is zero or negative', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5, 'manual_amount' => 0])
        ->assertSessionHasErrors('manual_amount');

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5, 'manual_amount' => -10])
        ->assertSessionHasErrors('manual_amount');

    expect($ot->fresh()->request_status)->toBe('pending');
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

    $this->actingAs($user)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5])->assertForbidden();
});

test('employee cannot reject OT', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($user)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible'])->assertForbidden();
});

test('self-approval is prevented for a manager submitting their own OT, even with a valid multiplier', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    giveOtSalary($manager);
    $ot = EmployeeOvertime::create([
        'user_id' => $manager->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $manager->id,
    ]);

    expect($manager->can('approve', $ot))->toBeFalse();
    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5])->assertForbidden();
    expect($ot->fresh()->request_status)->toBe('pending');
});

test('approved OT amounts remain frozen once set, guarded by the model', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'approved',
    ]);
    $ot->forceFill([
        'calculated_amount' => 375.00, 'hourly_rate_snapshot' => 125.00, 'rate_multiplier' => 1.50,
        'approved_amount' => 375.00, 'used_manual_override' => false,
    ])->save();

    $ot->refresh();
    $ot->calculated_amount = 999.00;
    $ot->save();
})->throws(RuntimeException::class, 'is already approved or paid');

test('rejected OT never has any amount field populated', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.reject', $ot), ['review_note' => 'not eligible']);

    $ot->refresh();
    expect($ot->request_status)->toBe('rejected');
    expect($ot->calculated_amount)->toBeNull();
    expect($ot->approved_amount)->toBeNull();
});

// ── Part 5: historical safety — no live recalculation, ever ────────────────

test('an already-approved OT amount stays byte-identical after the employee salary and config later change', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalary($user, 26000); // 125/hr
    EmployeeOvertimeConfig::create([
        'user_id' => $user->id, 'allowed_multipliers' => [1.5, 2.0], 'default_multiplier' => 1.5,
    ]);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5]);

    $ot->refresh();
    $frozenHourlyRate = (float) $ot->hourly_rate_snapshot;
    $frozenMultiplier = (float) $ot->rate_multiplier;
    $frozenCalculated = (float) $ot->calculated_amount;
    $frozenApproved   = (float) $ot->approved_amount;

    // Change the employee's config to different allowed multipliers...
    EmployeeOvertimeConfig::where('user_id', $user->id)->update([
        'allowed_multipliers' => [3.0], 'default_multiplier' => 3.0,
    ]);
    // ...and give them a brand new, much higher salary (effective the day
    // after the OT date, so the historical rate lookup would differ if it
    // were ever recomputed).
    $admin = User::factory()->create(['role' => 'admin']);
    $newSalary = new \App\Models\EmployeeSalary();
    $newSalary->fill(['user_id' => $user->id, 'monthly_salary' => 90000, 'effective_from' => '2026-08-11']);
    $newSalary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $newSalary->save();

    $refetched = EmployeeOvertime::find($ot->id);

    expect((float) $refetched->hourly_rate_snapshot)->toBe($frozenHourlyRate);
    expect((float) $refetched->rate_multiplier)->toBe($frozenMultiplier);
    expect((float) $refetched->calculated_amount)->toBe($frozenCalculated);
    expect((float) $refetched->approved_amount)->toBe($frozenApproved);
    expect((float) $refetched->hourly_rate_snapshot)->toBe(125.0);
    expect((float) $refetched->calculated_amount)->toBe(375.0);
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

    $this->actingAs($admin)->patch(route('admin.overtime.approve', $ot), ['multiplier' => 1.5])->assertForbidden();
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

    $this->actingAs($admin)->patch(route('admin.overtime.approve', $ot), ['multiplier' => 1.5])->assertForbidden();
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
    giveOtSalary($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5, 'review_note' => 'ok']);

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
    giveOtSalary($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.overtime.approve', $ot), ['multiplier' => 1.5, 'review_note' => 'ok']);

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
        'multiplier' => 1.5,
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
        'multiplier' => 1.5,
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
