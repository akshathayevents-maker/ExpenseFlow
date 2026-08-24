<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeSalary;
use App\Models\User;

function giveOtSalaryUi(User $user, float $amount = 26000): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => $amount, 'effective_from' => '2026-01-01']);
    $salary->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $salary->save();
}

// The attendance-first gate (EnsureAttendanceMarked) applies to every
// employee.* route except attendance/regularization itself — tests that
// exercise other employee pages must mark today's attendance first, exactly
// like a real employee would after logging in.
function markAttendanceTodayForUi(User $user): void
{
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => \Carbon\Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

test('employee OT index page loads', function () {
    $user = User::factory()->create();
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.index'))->assertOk();
});

test('employee OT create page loads', function () {
    $user = User::factory()->create();
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.create'))->assertOk();
});

test('employee OT show page loads for own record', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.show', $ot))->assertOk();
});

test('employee cannot access another employee OT show page', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $b->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $b->id,
    ]);
    markAttendanceTodayForUi($a);

    $this->actingAs($a->fresh())->get(route('employee.overtime.show', $ot))->assertForbidden();
});

test('manager OT index page loads', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)->get(route('manager.overtime.index'))->assertOk();
});

test('manager OT show page loads', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))->assertOk();
});

test('admin OT index page loads', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.overtime.index'))->assertOk();
});

test('admin OT create page loads with employee list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['name' => 'Jane Employee']);

    $this->actingAs($admin)->get(route('admin.overtime.create'))
        ->assertOk()
        ->assertSee('Jane Employee');
});

test('unauthenticated user is redirected away from OT pages', function () {
    $this->get(route('employee.overtime.index'))->assertRedirect(route('login'));
});

test('manager cannot access admin OT create page', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)->get(route('admin.overtime.create'))->assertForbidden();
});

test('pending OT show page exposes cancel action to its owner', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.show', $ot))
        ->assertOk()
        ->assertSee('Cancel Request');
});

test('approved OT show page does not expose cancel action', function () {
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'approved',
    ]);
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.show', $ot))
        ->assertOk()
        ->assertDontSee('Cancel Request');
});

test('rejected OT show page does not expose approve/reject actions to a manager', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'rejected',
    ]);

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))
        ->assertOk()
        ->assertDontSee('id="approveOtModal"', false)
        ->assertDontSee('id="rejectOtModal"', false);
});

test('cancelled OT show page does not expose any workflow action', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
        'request_status' => 'cancelled',
    ]);

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))
        ->assertOk()
        ->assertDontSee('id="approveOtModal"', false)
        ->assertDontSee('id="rejectOtModal"', false);
});

test('pending OT show page exposes approve/reject actions to a manager', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))
        ->assertOk()
        ->assertSee('id="approveOtModal"', false)
        ->assertSee('id="rejectOtModal"', false);
});
