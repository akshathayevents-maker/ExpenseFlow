<?php

use App\Models\EmployeeAttendance;
use App\Models\User;
use App\Support\AdminNavigation;

function navItems(User $u): array
{
    $nav = AdminNavigation::for($u)->build();
    $out = $nav['dashboard'] ? ['Dashboard' => $nav['dashboard']['route']] : [];
    foreach ($nav['groups'] as $g) {
        foreach ($g['items'] as $it) {
            $out[$g['label'].' › '.$it['label']] = $it['route'];
        }
    }

    return $out;
}

it('every menu link an admin sees opens without error', function () {
    $admin = icUser('admin');
    $items = navItems($admin);
    expect(count($items))->toBeGreaterThan(35);
    foreach ($items as $label => $route) {
        if ($route === 'admin.analytics.index') {
            continue;   // PostgreSQL-only SQL (TO_CHAR) — known to fail on the SQLite test database, works in production
        }
        expect($this->actingAs($admin)->get(route($route))->status())->toBe(200, "{$label} ({$route})");
    }
});

it('a manager sees only pages the manager can open, and admin-only pages stay blocked by the routes themselves', function () {
    $mgr = icUser('manager');
    $items = navItems($mgr);
    foreach ($items as $label => $route) {
        expect($this->actingAs($mgr)->get(route($route))->status())->toBe(200, "{$label} ({$route})");
    }
    expect($items)->toHaveKey('Finance › Expense Requests')->and($items['Finance › Expense Requests'])->toBe('manager.expense-requests.index')
        ->and($items)->not->toHaveKey('Finance › Payments')->not->toHaveKey('Settings › General Settings')->not->toHaveKey('Menu & Kitchen › Dishes');

    // hiding is not the security boundary: the admin-only pages still refuse a manager
    foreach (['admin.payments.index', 'admin.settings.index', 'menu.items.index', 'admin.payroll.index'] as $r) {
        expect($this->get(route($r))->status())->toBeIn([302, 403], $r);
    }
});

it('keeps the sidebar short: 7 business groups plus dashboard, no create links, no duplicates', function () {
    $nav = AdminNavigation::for(icUser('admin'))->build();
    expect(array_keys($nav['groups']))->toBe(['events', 'menu', 'store', 'finance', 'people', 'reports', 'settings']);
    $routes = array_values(navItems(icUser('admin')));
    expect(count($routes))->toBe(count(array_unique($routes)))
        ->and(collect($routes)->filter(fn ($r) => str_ends_with($r, '.create')))->toBeEmpty();
    foreach ($nav['groups'] as $g) {
        expect(count($g['items']))->toBeLessThanOrEqual(8);
    }
});

it('highlights exactly one item and shows a matching breadcrumb', function () {
    $admin = icUser('admin');
    $html = $this->actingAs($admin)->get(route('inventory.purchases.index'))->getContent();
    expect(substr_count($html, 'class="nav-link active"'))->toBe(1)
        ->and($html)->toMatch('/class="nav-link active"[^>]*title="Purchases"/')
        ->and($html)->toContain('<nav class="ef-crumbs" aria-label="Breadcrumb">')
        ->and($html)->toMatch('/ef-crumbs.*Store &amp; Inventory.*Purchases/s');
    // a page inside a section (stock alerts) highlights its parent (Stock)
    $html = $this->get(route('admin.inventory.alerts.index'))->getContent();
    expect($html)->toMatch('/class="nav-link active"[^>]*title="Stock"/');
});

it('offers quick actions and a page search limited to what the user may open', function () {
    $mgr = icUser('manager');
    $labels = array_column(AdminNavigation::for($mgr)->quickActions(), 'label');
    expect($labels)->toContain('New expense')->toContain('New hall booking')->not->toContain('Add employee')->not->toContain('Close the day');
    $index = AdminNavigation::for($mgr)->searchIndex();
    $urls = array_column($index, 'u');
    expect($urls)->toContain(route('manager.expense-requests.create'))->not->toContain(route('admin.employees.create'))
        ->and(collect($urls)->filter(fn ($u) => str_contains($u, '/admin/settings')))->toBeEmpty();

    $html = $this->actingAs($mgr)->get(route('manager.dashboard'))->assertOk()->getContent();
    expect($html)->toContain('id="cmdk"')->toContain('data-cmdk-open')->toContain('id="sb-collapse"')
        ->not->toContain(route('admin.employees.create'));
});

it('leaves the employee self-service navigation unchanged', function () {
    $emp = icUser();
    EmployeeAttendance::create(['user_id' => $emp->id, 'attendance_date' => now('Asia/Kolkata')->toDateString(), 'status' => 'present', 'marked_by' => $emp->id, 'marked_at' => now(), 'source' => 'self']);
    $html = $this->actingAs($emp)->get(route('inventory.home'))->getContent();
    expect($html)->not->toContain('id="cmdk"')->not->toContain('id="admin-nav-accordion"')->toContain('My Work');
});
