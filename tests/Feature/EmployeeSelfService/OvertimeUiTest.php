<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
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

// ── Overtime redesign: no compensation visible before approval ────────────

test('employee never sees any compensation figure on a pending OT they created', function () {
    $user = User::factory()->create();
    giveOtSalaryUi($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.show', $ot))
        ->assertOk()
        ->assertDontSee('Hourly Rate')
        ->assertDontSee('Multiplier')
        ->assertDontSee('Calculated Amount')
        ->assertDontSee('Final Approved Amount');
});

test('employee OT create page never shows a multiplier or amount field', function () {
    $user = User::factory()->create();
    markAttendanceTodayForUi($user);

    $this->actingAs($user->fresh())->get(route('employee.overtime.create'))
        ->assertOk()
        ->assertDontSee('name="multiplier"', false)
        ->assertDontSee('name="calculated_amount"', false);
});

test('pending OT show page exposes a multiplier selector to an approving manager', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalaryUi($user);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))
        ->assertOk()
        ->assertSee('name="multiplier"', false)
        ->assertSee('Salary / Hour')
        ->assertSee('Manual Override Amount', false);
});

test('pending OT approval UI shows the employee configured allowed multipliers, not a hardcoded list', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    giveOtSalaryUi($user);
    EmployeeOvertimeConfig::create([
        'user_id' => $user->id, 'allowed_multipliers' => [1.0], 'default_multiplier' => 1.0,
    ]);
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $response = $this->actingAs($manager)->get(route('manager.overtime.show', $ot))->assertOk();

    $response->assertSee('value="1"', false);
    $response->assertDontSee('value="1.5"', false);
    $response->assertDontSee('value="2"', false);
});

test('approved OT show page displays the frozen hourly rate, multiplier and final approved amount', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $ot->forceFill([
        'request_status' => 'approved',
        'hourly_rate_snapshot' => 125.00, 'rate_multiplier' => 1.50, 'calculated_amount' => 375.00,
        'approved_amount' => 375.00, 'used_manual_override' => false,
    ])->save();

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))
        ->assertOk()
        ->assertSee('Hourly Rate')
        ->assertSee('Final Approved Amount');
});

test('approved OT with a manual override visibly distinguishes it in the UI', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $ot->forceFill([
        'request_status' => 'approved',
        'hourly_rate_snapshot' => 125.00, 'rate_multiplier' => 1.50, 'calculated_amount' => 375.00,
        'approved_amount' => 500.00, 'used_manual_override' => true,
    ])->save();

    $this->actingAs($manager)->get(route('manager.overtime.show', $ot))
        ->assertOk()
        ->assertSee('Manual Override');
});

// ── Overtime redesign: admin per-employee configuration UI ────────────────

test('admin employee compensation page shows an overtime configuration section', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $user))
        ->assertOk()
        ->assertSee('Overtime Configuration')
        ->assertSee('Allowed Multipliers');
});

test('admin can save per-employee overtime configuration', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.overtime-config.store', $user), [
        'allowed_multipliers' => [1.0, 2.0],
        'default_multiplier' => 2.0,
    ])->assertRedirect();

    $config = EmployeeOvertimeConfig::where('user_id', $user->id)->first();
    expect($config)->not->toBeNull();
    expect(array_map('floatval', $config->allowed_multipliers))->toBe([1.0, 2.0]);
    expect((float) $config->default_multiplier)->toBe(2.0);
});

test('admin cannot save a default multiplier that is not in the checked allowed list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.overtime-config.store', $user), [
        'allowed_multipliers' => [1.0],
        'default_multiplier' => 2.0,
    ])->assertSessionHasErrors('default_multiplier');

    expect(EmployeeOvertimeConfig::where('user_id', $user->id)->exists())->toBeFalse();
});
