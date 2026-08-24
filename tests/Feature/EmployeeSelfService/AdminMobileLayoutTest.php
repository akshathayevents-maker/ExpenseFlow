<?php

use App\Models\User;

// ── Mobile sidebar / layout system ──────────────────────────────────────

test('employee create page still renders for admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.employees.create'))->assertOk();
});

test('admin layout still renders the sidebar drawer and overlay markup on the employee create page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.employees.create'))
        ->assertOk()
        ->assertSee('id="sidebar"', false)
        ->assertSee('id="sidebar-overlay"', false)
        ->assertSee('id="sidebar-toggle"', false);
});

test('sidebar and backdrop z-index come from the single shared CSS scale, not a page-local override', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.create'));

    $response->assertOk();
    $response->assertSee('--ef-z-sidebar:', false);
    $response->assertSee('--ef-z-sidebar-backdrop:', false);
});

test('admin layout renders consistently across other admin pages using the same layout', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('id="sidebar"', false);
    $this->actingAs($admin)->get(route('admin.employees.index'))->assertOk()->assertSee('id="sidebar"', false);
    $this->actingAs($admin)->get(route('admin.salaries.index'))->assertOk()->assertSee('id="sidebar"', false);
});

test('mobile.css no longer declares a competing sidebar z-index', function () {
    $css = file_get_contents(public_path('css/mobile.css'));

    expect($css)->not->toContain('#sidebar { z-index: 1055; }');
    expect($css)->not->toContain('#sidebar-overlay { z-index: 1050; }');
});
