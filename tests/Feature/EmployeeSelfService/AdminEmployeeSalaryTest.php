<?php

use App\Models\EmployeeSalary;
use App\Models\User;

// ── Salary ───────────────────────────────────────────────────────────────

test('admin can create salary for an employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);

    $response->assertRedirect(route('admin.employees.salaries.index', $employee));
    expect(EmployeeSalary::where('user_id', $employee->id)->count())->toBe(1);
});

test('salary is associated with the correct employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employeeA = User::factory()->create();
    $employeeB = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employeeA), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);

    expect(EmployeeSalary::where('user_id', $employeeA->id)->count())->toBe(1);
    expect(EmployeeSalary::where('user_id', $employeeB->id)->count())->toBe(0);
});

test('created_by comes from the authenticated admin, not request input', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $otherAdmin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01', 'created_by' => $otherAdmin->id,
    ]);

    expect(EmployeeSalary::first()->created_by)->toBe($admin->id);
});

test('new salary closes the previous salary correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 35000, 'effective_from' => '2026-06-01',
    ]);

    $first = EmployeeSalary::where('monthly_salary', 30000)->first();
    expect($first->effective_to->toDateString())->toBe('2026-05-31');
});

test('salary history preserves the previous salary amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 35000, 'effective_from' => '2026-06-01',
    ]);

    expect(EmployeeSalary::where('user_id', $employee->id)->count())->toBe(2);
    expect((float) EmployeeSalary::where('monthly_salary', 30000)->first()->monthly_salary)->toBe(30000.0);
});

test('overlapping salary periods are rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-06-01',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 25000, 'effective_from' => '2026-01-01', // backdated before an already-scheduled change
    ]);

    $response->assertSessionHasErrors('effective_from');
    expect(EmployeeSalary::where('user_id', $employee->id)->count())->toBe(1);
});

test('a same-day salary correction is rejected under the existing overlap rule', function () {
    // Documents the current, unchanged intended behavior (see the docblock
    // in EmployeeSalaryService): a salary already effective today blocks a
    // second salary also effective today — same-day amendment is not
    // supported, correction can only take effect starting tomorrow.
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();
    $today = now()->toDateString();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => $today,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 32000, 'effective_from' => $today,
    ]);

    $response->assertSessionHasErrors('effective_from');

    $rows = EmployeeSalary::where('user_id', $employee->id)->get();
    expect($rows)->toHaveCount(1); // no overlapping row was created
    expect((float) $rows->first()->monthly_salary)->toBe(30000.0); // original untouched
    expect($rows->first()->effective_from->toDateString())->toBe($today);
});

// ── Mass-assignment hardening ───────────────────────────────────────────────

test('effective_to and created_by cannot be set via direct mass assignment', function () {
    $employee = User::factory()->create();
    $someUserId = User::factory()->create()->id;

    // created_by is NOT NULL at the schema level and excluded from
    // $fillable — a caller that tries to mass-assign it (instead of using
    // forceFill(), the one legitimate path) gets neither a forged value NOR
    // a silently-null row: the injected value is dropped and the insert
    // fails outright, since nothing supplied a valid created_by.
    expect(fn () => EmployeeSalary::create([
        'user_id' => $employee->id, 'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
        'effective_to' => '2026-12-31', 'created_by' => $someUserId,
    ]))->toThrow(\Illuminate\Database\QueryException::class);

    expect(EmployeeSalary::count())->toBe(0);
});

test('effective_to injected via mass assignment is dropped even when created_by is supplied correctly', function () {
    $employee = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $salary = new EmployeeSalary();
    $salary->fill([
        'user_id' => $employee->id, 'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
        'effective_to' => '2026-12-31', // attempted mass-assignment — must be dropped
    ]);
    $salary->forceFill(['created_by' => $admin->id])->save();

    expect($salary->fresh()->effective_to)->toBeNull();
});

test('legitimate salary creation through the service still works after hardening', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 40000, 'effective_from' => '2026-01-01',
    ]);

    $response->assertRedirect(route('admin.employees.salaries.index', $employee));

    $salary = EmployeeSalary::where('user_id', $employee->id)->first();
    expect((float) $salary->monthly_salary)->toBe(40000.0);
    expect($salary->effective_to)->toBeNull();
    expect($salary->created_by)->toBe($admin->id);
});

test('currentSalaryAsOf returns the correct salary before and after a change', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 35000, 'effective_from' => '2026-06-01',
    ]);

    $employee->refresh();
    expect((float) $employee->currentSalaryAsOf(\Carbon\Carbon::parse('2026-03-01'))->monthly_salary)->toBe(30000.0);
    expect((float) $employee->currentSalaryAsOf(\Carbon\Carbon::parse('2026-07-01'))->monthly_salary)->toBe(35000.0);
});

// ── Security ─────────────────────────────────────────────────────────────

test('employee cannot access salary management', function () {
    $employee = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($employee->fresh())->get(route('admin.employees.salaries.index', $target))->assertForbidden();
});

test('manager cannot access salary management', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $target = User::factory()->create();

    $this->actingAs($manager)->get(route('admin.employees.salaries.index', $target))->assertForbidden();
});

test('direct POST attempts by non-admin users are rejected server-side', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $target = User::factory()->create();

    $this->actingAs($manager)->post(route('admin.employees.salaries.store', $target), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ])->assertForbidden();

    expect(EmployeeSalary::count())->toBe(0);
});

// ── Salary scope: valid workforce accounts only ────────────────────────────

test('admin cannot set salary for an admin user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $targetAdmin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $targetAdmin))->assertForbidden();

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $targetAdmin), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $response->assertForbidden();
    expect(EmployeeSalary::count())->toBe(0);
});

test('admin cannot set salary for an inactive employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $inactiveEmployee = User::factory()->create(['role' => 'employee', 'is_active' => false]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $inactiveEmployee))->assertForbidden();

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $inactiveEmployee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $response->assertForbidden();
    expect(EmployeeSalary::count())->toBe(0);
});

test('admin can set salary for an active employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))->assertOk();

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $response->assertRedirect(route('admin.employees.salaries.index', $employee));
    expect(EmployeeSalary::where('user_id', $employee->id)->count())->toBe(1);
});

test('admin can set salary for an active manager', function () {
    // Managers are intentionally included in the existing employee-management
    // scope (EmployeeController::index() lists whereIn('role', ['employee',
    // 'manager'])) — salary management follows the same scope.
    $admin = User::factory()->create(['role' => 'admin']);
    $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $manager))->assertOk();

    $response = $this->actingAs($admin)->post(route('admin.employees.salaries.store', $manager), [
        'monthly_salary' => 45000, 'effective_from' => '2026-01-01',
    ]);
    $response->assertRedirect(route('admin.employees.salaries.index', $manager));
    expect(EmployeeSalary::where('user_id', $manager->id)->count())->toBe(1);
});

// ── Employment dates (Admin Employee) ─────────────────────────────────────

test('employment_start_date is stored when creating an employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.employees.store'), [
        'name' => 'Jane Doe', 'email' => 'jane@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
        'employment_start_date' => '2026-01-15',
    ]);

    $employee = User::where('email', 'jane@example.com')->first();
    expect($employee->employment_start_date->toDateString())->toBe('2026-01-15');
});

test('employment_end_date is stored when updating an employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($admin)->put(route('admin.employees.update', $employee), [
        'name' => $employee->name, 'email' => $employee->email, 'role' => 'employee', 'is_active' => 1,
        'employment_start_date' => '2026-01-01', 'employment_end_date' => '2026-12-31',
    ]);

    expect($employee->fresh()->employment_end_date->toDateString())->toBe('2026-12-31');
});

test('invalid employment date range is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.employees.store'), [
        'name' => 'Bad Range', 'email' => 'badrange@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
        'employment_start_date' => '2026-06-01', 'employment_end_date' => '2026-01-01',
    ]);

    $response->assertSessionHasErrors('employment_end_date');
    expect(User::where('email', 'badrange@example.com')->exists())->toBeFalse();
});

// ── Salary screen UI ────────────────────────────────────────────────────

test('salary page loads for an eligible employee', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee($employee->email);
});

test('salary page shows the current salary amount and effective date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 27500, 'effective_from' => '2026-02-01',
    ]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()
        ->assertSee('27,500.00')
        ->assertSee('01 Feb 2026');
});

test('salary page shows salary history entries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 35000, 'effective_from' => '2026-06-01',
    ]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()
        ->assertSee('Salary History')
        ->assertSee('30,000.00')
        ->assertSee('35,000.00');
});

test('salary page shows a no-salary empty state and Set Salary label', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()
        ->assertSee('No salary configured')
        ->assertSee('Set Salary');
});

test('salary page shows Change Salary label once a salary already exists', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()
        ->assertSee('Change Salary')
        ->assertDontSee('Set Salary');
});

test('setting salary from the salary page redirects back to the same page with the new amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 22000, 'effective_from' => '2026-01-01',
    ])->assertRedirect(route('admin.employees.salaries.index', $employee));

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()->assertSee('22,000.00');
});

test('changing salary from the salary page reflects the new current salary and preserves history', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 30000, 'effective_from' => '2026-01-01',
    ]);
    $this->actingAs($admin)->post(route('admin.employees.salaries.store', $employee), [
        'monthly_salary' => 40000, 'effective_from' => '2026-07-01',
    ]);

    $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee))
        ->assertOk()
        ->assertSee('40,000.00') // current
        ->assertSee('30,000.00'); // history
});

test('salary page markup does not force a wide fixed-width table on mobile', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.employees.salaries.index', $employee));
    $response->assertOk();

    $html = $response->getContent();
    // Mobile stacked-card list must be present, and the table wrapper must
    // be hidden below the 576px breakpoint (display:none via CSS class),
    // not a table forced to overflow the viewport.
    expect($html)->toContain('sal-hist-cards');
    expect($html)->toContain('sal-hist-table-wrap');
});

test('non-admin cannot open the salary page UI', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create();

    $this->actingAs($manager)->get(route('admin.employees.salaries.index', $employee))->assertForbidden();
});
