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
        ->assertSee('Attendance Corrections')
        ->assertSee(route('admin.attendance-regularizations.index'), false);
});

test('admin navigation exposes Employee Salaries', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Salaries')->assertSee(route('admin.salaries.index'), false);
});

test('admin navigation exposes Overtime under People & Payroll', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('People &amp; Payroll', false)
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
    $groupIds = ['nav-events', 'nav-menu', 'nav-store', 'nav-finance', 'nav-people', 'nav-reports', 'nav-settings'];

    $response->assertSee('id="admin-nav-accordion"', false);

    foreach ($groupIds as $id) {
        expect($html)->toContain('data-bs-parent="#admin-nav-accordion" id="'.$id.'"');
    }
});

test('a route that matches both the employees wildcard and the salary section highlights only Salaries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    // admin.employees.salaries.index also matches the broad
    // 'admin.employees.*' route-name wildcard that People / HR used to key
    // off directly — this was the reported bug: both sections rendered
    // with the 'show' class simultaneously on this exact page.
    $response = $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee));
    $response->assertOk();

    $html = $response->getContent();

    // exactly one active link, and it is Salaries — not Employees (whose admin.employees.* pattern also matches)
    expect(substr_count($html, 'aria-current="page"'))->toBe(2)   // sidebar link + breadcrumb
        ->and($html)->toMatch('/class="nav-link active"[^>]*title="Salaries"/')
        ->and($html)->not->toMatch('/class="nav-link active"[^>]*title="Employees"/')
        ->and(substr($html, strpos($html, 'id="nav-people"') - 120, 200))->toContain('show');
});

test('admin sidebar group buttons expose aria-controls matching their collapse target', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertOk();

    foreach (['events', 'menu', 'store', 'finance', 'people', 'reports', 'settings'] as $g) {
        $response->assertSee('data-bs-target="#nav-'.$g.'" aria-controls="nav-'.$g.'"', false);
    }
});

test('visiting an Overtime page opens only People & Payroll', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.overtime.index'));
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, 'collapse sidebar-group-body show'))->toBe(1)
        ->and(substr($html, strpos($html, 'id="nav-people"') - 120, 200))->toContain('show');
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

test('employee sidebar shows the everyday items and keeps store-side tools for storekeepers only', function () {
    $emp = User::factory()->create(['role' => 'employee']);
    $html = $this->actingAs($emp->fresh())->get(route('employee.attendance.index'))->assertOk()->getContent();
    expect($html)->toContain('Kitchen &amp; Events')->toContain(route('employee.kitchen.calculator'))
        ->toContain(route('meal-register.entries.create'))->toContain(route('employee.wallet.show'))
        ->toContain(route('inventory.material-requests.create'))->toContain('My Material Requests')
        ->not->toContain('href="'.route('inventory.stock').'"')
        ->not->toContain('href="'.route('inventory.purchases.index').'"');

    $store = User::factory()->create(['role' => 'employee', 'is_storekeeper' => true]);
    $html = $this->actingAs($store->fresh())->get(route('employee.attendance.index'))->assertOk()->getContent();
    expect($html)->toContain('href="'.route('inventory.stock').'"')->toContain('href="'.route('inventory.purchases.index').'"')
        ->toContain('Store Overview');
});
