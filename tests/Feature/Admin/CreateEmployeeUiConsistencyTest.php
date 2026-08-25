<?php

use App\Models\User;

test('create employee page loads', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.employees.create'))->assertOk();
});

test('authorized admin can create an employee with role, dates, and active status', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.employees.store'), [
        'name' => 'Priya Sharma', 'email' => 'priya@example.com', 'password' => 'Password123!',
        'role' => 'manager', 'is_active' => 1,
        'employment_start_date' => '2026-01-01',
    ]);

    $response->assertRedirect();
    $employee = User::where('email', 'priya@example.com')->first();
    expect($employee)->not->toBeNull();
    expect($employee->role)->toBe('manager');
    expect($employee->is_active)->toBeTrue();
    expect($employee->employment_start_date->toDateString())->toBe('2026-01-01');
});

test('validation still rejects a missing required field', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.employees.store'), [
        'email' => 'noname@example.com', 'password' => 'Password123!', 'role' => 'employee', 'is_active' => 1,
    ])->assertSessionHasErrors('name');

    expect(User::where('email', 'noname@example.com')->exists())->toBeFalse();
});

test('inactive account can be created via the toggle', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.employees.store'), [
        'name' => 'Inactive Guy', 'email' => 'inactive@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 0,
    ])->assertRedirect();

    expect(User::where('email', 'inactive@example.com')->first()->is_active)->toBeFalse();
});

// ── Sticky footer removal / single action set ────────────────────────────

test('there is no fixed/sticky bottom action bar on the create employee page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.create'));
    $html = $response->getContent();

    // The old mobile sticky footer's own class/markup must be gone — this
    // is the precise signal for "was the duplicate fixed action bar
    // removed", rather than a generic position:fixed text search (which
    // would also match admin-layout's own unrelated topbar/sidebar CSS
    // and JS comments elsewhere on the page).
    expect($html)->not->toContain('ef-emp-sticky');
});

test('there is exactly one Cancel button and one Create Employee button', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.create'));
    $html = $response->getContent();

    expect(substr_count($html, '>Cancel<'))->toBe(1);
    expect(substr_count($html, 'Create Employee'))->toBe(1);
});

// ── Color/token standardization ───────────────────────────────────────────

test('create employee page no longer uses the old gold hero/progress-rail markup', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.create'));
    $html = $response->getContent();

    expect($html)->not->toContain('ef-emp-hero');
    expect($html)->not->toContain('ef-emp-progress-rail');
    expect($html)->not->toContain('ef-emp-section-num');
    expect($html)->not->toContain('ef-emp-btn-submit');
    expect($html)->not->toContain('ef-emp-toggle-switch');
});

test('create employee page reuses the shared form-page/card/input/switch classes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.create'));
    $html = $response->getContent();

    expect($html)->toContain('ef-form-page');
    expect($html)->toContain('ef-form-page-header');
    expect($html)->toContain('ef-ds-card');
    expect($html)->toContain('class="ef-input');
    expect($html)->toContain('ef-switch');
    expect($html)->toContain('ef-btn ef-btn-dark');
});

test('create employee page role selector uses emerald for selection, not gold', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.employees.create'));
    $html = $response->getContent();

    expect($html)->toContain('var(--ef-emerald)');
    // Manager role icon legitimately keeps gold as a role-color accent
    // (matches the Manager badge color used elsewhere in the app), but the
    // selected/checked state itself must be emerald, not gold.
    expect($html)->not->toContain('checked + .ef-emp-role-face {' . "\n" . '    border-color: var(--ef-gold)');
});
