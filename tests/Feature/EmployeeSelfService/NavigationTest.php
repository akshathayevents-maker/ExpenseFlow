<?php

use App\Models\User;

// ── Admin navigation ─────────────────────────────────────────────────────

test('admin navigation contains Employees', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Employees')
        ->assertSee(route('admin.employees.index'), false);
});

test('admin navigation exposes Attendance Regularization', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Attendance Regularization')
        ->assertSee(route('admin.attendance-regularizations.index'), false);
});

test('admin navigation exposes Employee Salaries', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Employee Salaries');
});

test('admin navigation exposes Overtime under Compensation Payroll', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Compensation / Payroll')
        ->assertSee(route('admin.overtime.index'), false);
});

test('admin can reach salary management from employee details', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    $this->actingAs($admin)->get(route('admin.employees.show', $employee))
        ->assertOk()
        ->assertSee('Salary')
        ->assertSee(route('admin.employees.salaries.index', $employee), false);
});

// ── Admin sidebar: single-open accordion ─────────────────────────────────

test('admin sidebar wires every top-level group into one shared accordion parent', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertOk();

    $html = $response->getContent();
    $groupIds = [
        'grp-expenses', 'grp-people-hr', 'grp-payroll', 'grp-inventory', 'grp-setup',
        'grp-analytics', 'grp-ops', 'grp-hall', 'grp-event-requests',
        'grp-kitchen-admin', 'grp-corp-meals-admin',
    ];

    $response->assertSee('id="admin-nav-accordion"', false);

    foreach ($groupIds as $id) {
        expect($html)->toContain('data-bs-parent="#admin-nav-accordion" id="' . $id . '"');
    }
});

test('a route that matches both an employees wildcard and the salary section only opens Compensation / Payroll', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    // admin.employees.salaries.index also matches the broad
    // 'admin.employees.*' route-name wildcard that People / HR used to key
    // off directly — this was the reported bug: both sections rendered
    // with the 'show' class simultaneously on this exact page.
    $response = $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee));
    $response->assertOk();

    $html = $response->getContent();

    $payrollBody = substr($html, strpos($html, 'id="grp-payroll"') - 200, 260);
    $peopleHrBody = substr($html, strpos($html, 'id="grp-people-hr"') - 200, 260);

    expect($payrollBody)->toContain('show');
    expect($peopleHrBody)->not->toContain('show');
});

test('admin sidebar group buttons expose aria-controls matching their collapse target', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertOk();

    $response->assertSee('aria-controls="grp-people-hr"', false);
    $response->assertSee('aria-controls="grp-payroll"', false);
});

test('visiting an Overtime page (Compensation / Payroll) does not also mark People / HR open', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.overtime.index'));
    $response->assertOk();

    $html = $response->getContent();
    $peopleHrBody = substr($html, strpos($html, 'id="grp-people-hr"') - 200, 260);

    expect($peopleHrBody)->not->toContain('show');
});

// ── Employee navigation ──────────────────────────────────────────────────

test('employee navigation contains Attendance', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.attendance.index'))
        ->assertOk()
        ->assertSee('My Work')
        ->assertSee(route('employee.attendance.index'), false);
});

test('employee navigation does not expose salary management', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.attendance.index'))
        ->assertOk()
        ->assertDontSee('Employee Salaries')
        ->assertDontSee('Compensation / Payroll');
});
