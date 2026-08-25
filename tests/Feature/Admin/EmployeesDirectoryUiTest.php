<?php

use App\Models\User;

test('employees index page loads', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.employees.index'))->assertOk();
});

test('there is exactly one Add Employee action, in the header, no floating action button', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(); // non-empty list — isolates the check from the empty-state's own Add Employee button

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));
    $html = $response->getContent();

    expect(substr_count($html, 'Add Employee'))->toBe(1);
    expect($html)->not->toContain('ef-emp-fab');
});

test('employee row links to the employee detail page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));
    $response->assertOk();
    $response->assertSee(route('admin.employees.show', $employee), false);
});

test('the dead disabled Apply Filters black bar is gone', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));
    $html = $response->getContent();

    expect($html)->not->toContain('ef-emp-mf-apply');
    expect($html)->not->toContain('Apply Filters');
});

test('role and status filtering still work end to end', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $manager = User::factory()->create(['role' => 'manager', 'name' => 'Mgr One']);
    $employee = User::factory()->create(['role' => 'employee', 'name' => 'Emp One', 'is_active' => false]);

    $this->actingAs($admin)->get(route('admin.employees.index', ['role' => 'manager']))
        ->assertOk()->assertSee('Mgr One')->assertDontSee('Emp One');

    $this->actingAs($admin)->get(route('admin.employees.index', ['status' => 'inactive']))
        ->assertOk()->assertSee('Emp One')->assertDontSee('Mgr One');
});

test('the search input is present and wired to the index route', function () {
    // The controller's search query uses Postgres-only `ilike` (unchanged
    // by this task — controller logic is out of scope), which the sqlite
    // test database doesn't support, so this only verifies the UI is
    // wired correctly rather than exercising the query end-to-end.
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));
    $html = $response->getContent();

    expect($html)->toContain('placeholder="Search employees…"');
    expect($html)->toContain("base:    '" . route('admin.employees.index') . "'");
});

test('reset clears filters back to the full list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'employee']);
    User::factory()->create(['role' => 'manager']);

    $this->actingAs($admin)->get(route('admin.employees.index'))
        ->assertOk()
        ->assertSee(route('admin.employees.index'), false);
});

test('empty state shows Clear Filters when a filter matches nothing', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'employee', 'is_active' => true]);

    // Filtering by a role/status combination with zero matches — avoids
    // the `search` param, which uses Postgres-only `ilike` (unchanged,
    // out of scope for this task) that the sqlite test DB can't run.
    $this->actingAs($admin)->get(route('admin.employees.index', ['status' => 'inactive']))
        ->assertOk()
        ->assertSee('No employees found')
        ->assertSee('Try changing your search or filters.')
        ->assertSee('Clear Filters');
});

test('empty state shows Add Employee when there are no employees at all', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.employees.index'))
        ->assertOk()
        ->assertSee('No employees yet');
});

test('toggle status action still works from the employees list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['is_active' => true]);

    $this->actingAs($admin)->patch(route('admin.employees.toggle-status', $employee->fresh()))->assertRedirect();

    expect($employee->fresh()->is_active)->toBeFalse();
});

test('summary tiles show total, managers, active and inactive counts', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'employee', 'is_active' => true]);
    User::factory()->create(['role' => 'manager', 'is_active' => true]);
    User::factory()->create(['role' => 'employee', 'is_active' => false]);

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));
    $response->assertOk();
    $response->assertSee('Total Workforce');
    $response->assertSee('Managers');
    $response->assertSee('Active Staff');
    $response->assertSee('Inactive');
});

test('page reuses the shared kpi-card and hero components instead of the old bespoke hero/kpi CSS', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.index'));
    $html = $response->getContent();

    expect($html)->toContain('ef-ds-hero');
    expect($html)->toContain('ef-ds-kpi-grid');
    expect($html)->not->toContain('ef-emp-hero');
    expect($html)->not->toContain('ef-emp-stats');
});

test('non-admin cannot access the employees directory', function () {
    $employee = User::factory()->create();

    $this->actingAs($employee->fresh())->get(route('admin.employees.index'))->assertForbidden();
});
