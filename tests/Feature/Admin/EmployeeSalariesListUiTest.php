<?php

use App\Models\User;
use App\Services\EmployeeSalaryService;
use Carbon\Carbon;

function eslSetSalary(User $employee, User $admin, float $amount, string $effectiveFrom): void
{
    Illuminate\Support\Facades\Auth::login($admin);
    app(EmployeeSalaryService::class)->setSalary($employee, $amount, Carbon::parse($effectiveFrom), $admin);
}

test('employee salaries list page loads', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.salaries.index'))->assertOk();
});

test('payroll summary shows total, configured and not-configured counts from real data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $withSalary = User::factory()->create(['role' => 'employee']);
    $withoutSalary = User::factory()->create(['role' => 'manager']);
    eslSetSalary($withSalary, $admin, 25000, '2026-01-01');

    $response = $this->actingAs($admin)->get(route('admin.salaries.index'));
    $response->assertOk();

    expect($response->getContent())->toContain('Total Employees');
    expect($response->getContent())->toContain('Salary Configured');
    expect($response->getContent())->toContain('Need Salary Setup');
    $response->assertSee('2', false); // total employees
    $response->assertSee('25,000'); // configured payroll total reflects the real salary
});

test('salary_status=set filters to only employees with a currently effective salary', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $configured = User::factory()->create(['name' => 'Has Salary']);
    $unconfigured = User::factory()->create(['name' => 'No Salary']);
    eslSetSalary($configured, $admin, 30000, '2026-01-01');

    $this->actingAs($admin)->get(route('admin.salaries.index', ['salary_status' => 'set']))
        ->assertOk()
        ->assertSee('Has Salary')
        ->assertDontSee('No Salary');
});

test('salary_status=not_set filters to only employees without a currently effective salary', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $configured = User::factory()->create(['name' => 'Has Salary']);
    $unconfigured = User::factory()->create(['name' => 'No Salary']);
    eslSetSalary($configured, $admin, 30000, '2026-01-01');

    $this->actingAs($admin)->get(route('admin.salaries.index', ['salary_status' => 'not_set']))
        ->assertOk()
        ->assertSee('No Salary')
        ->assertDontSee('Has Salary');
});

test('a fully expired (closed) salary period counts as not configured', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['name' => 'Ex Employee']);
    eslSetSalary($employee, $admin, 20000, '2020-01-01');
    // Close it out so it's no longer current as of today.
    $employee->salaries()->first()->forceFill(['effective_to' => '2020-06-01'])->save();

    $this->actingAs($admin)->get(route('admin.salaries.index', ['salary_status' => 'not_set']))
        ->assertOk()
        ->assertSee('Ex Employee');
});

test('role filter still narrows the list', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'manager', 'name' => 'The Manager']);
    User::factory()->create(['role' => 'employee', 'name' => 'The Employee']);

    $this->actingAs($admin)->get(route('admin.salaries.index', ['role' => 'manager']))
        ->assertOk()
        ->assertSee('The Manager')
        ->assertDontSee('The Employee');
});

test('Change Salary action is shown for a configured employee and Set Salary for an unconfigured one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $configured = User::factory()->create();
    $unconfigured = User::factory()->create();
    eslSetSalary($configured, $admin, 30000, '2026-01-01');

    $response = $this->actingAs($admin)->get(route('admin.salaries.index'));
    $html = $response->getContent();

    expect($html)->toContain('Change Salary');
    expect($html)->toContain('Set Salary');
});

test('each employee row links to the existing per-employee salary management screen', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.salaries.index'))
        ->assertOk()
        ->assertSee(route('admin.employees.salaries.index', $employee), false);
});

test('empty state shows Clear Filters when a filter matches nothing', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'employee']);

    $this->actingAs($admin)->get(route('admin.salaries.index', ['role' => 'manager']))
        ->assertOk()
        ->assertSee('No employees found')
        ->assertSee('Try changing your search or filters.')
        ->assertSee('Clear Filters');
});

test('page reuses the shared hero/kpi components rather than a page-specific palette', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.salaries.index'));
    $html = $response->getContent();

    expect($html)->toContain('ef-ds-hero');
    expect($html)->toContain('ef-ds-kpi-grid');
});

test('non-admin cannot access the employee salaries list', function () {
    $employee = User::factory()->create();
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($employee->fresh())->get(route('admin.salaries.index'))->assertForbidden();
    $this->actingAs($manager)->get(route('admin.salaries.index'))->assertForbidden();
});
