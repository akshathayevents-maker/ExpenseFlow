<?php

use App\Models\EmployeeLeavePolicy;
use App\Models\LeavePolicyTemplate;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveAllocationService;
use App\Services\LeavePolicyAssignmentService;
use Carbon\Carbon;

function lptLeaveType(array $attrs = []): LeaveType
{
    return LeaveType::create(array_merge([
        'name' => 'Casual Leave', 'code' => 'CL-'.uniqid(), 'allow_half_day' => true,
        'is_active' => true, 'is_paid' => true,
    ], $attrs));
}

function lptTemplatePayload(array $itemsOverride = []): array
{
    return [
        'name' => 'Standard Template',
        'description' => 'Default entitlements',
        'items' => $itemsOverride,
    ];
}

// ── B/C: admin creates a template with items ──────────────────────────────

test('admin can create a leave policy template with a name and description', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lptLeaveType();

    $this->actingAs($admin->fresh())->post(route('admin.leave-policy-templates.store'), [
        'name' => 'Standard Template',
        'description' => 'Company default',
        'items' => [
            ['leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly'],
        ],
    ])->assertRedirect(route('admin.leave-policy-templates.index'));

    $template = LeavePolicyTemplate::where('name', 'Standard Template')->first();
    expect($template)->not->toBeNull();
    expect($template->description)->toBe('Company default');
    expect($template->items)->toHaveCount(1);
});

test('admin can add multiple leave-type items with different allocation modes in one create', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $cl = lptLeaveType(['name' => 'Casual', 'code' => 'CL-'.uniqid()]);
    $sl = lptLeaveType(['name' => 'Sick', 'code' => 'SL-'.uniqid()]);

    $this->actingAs($admin->fresh())->post(route('admin.leave-policy-templates.store'), [
        'name' => 'Multi Template',
        'items' => [
            ['leave_type_id' => $cl->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly'],
            ['leave_type_id' => $sl->id, 'annual_entitlement' => 6, 'allocation_mode' => 'monthly_accrual', 'monthly_accrual_amount' => 0.5],
        ],
    ])->assertRedirect(route('admin.leave-policy-templates.index'));

    $template = LeavePolicyTemplate::where('name', 'Multi Template')->first();
    expect($template->items)->toHaveCount(2);
    expect($template->items->firstWhere('leave_type_id', $sl->id)->allocation_mode)->toBe('monthly_accrual');
});

test('manager and employee cannot create leave policy templates', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lptLeaveType();

    $payload = [
        'name' => 'X', 'items' => [['leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']],
    ];

    $this->actingAs($manager->fresh())->post(route('admin.leave-policy-templates.store'), $payload)->assertForbidden();
    $this->actingAs($employee->fresh())->post(route('admin.leave-policy-templates.store'), $payload)->assertForbidden();
});

// ── D: assign a template to an existing employee ───────────────────────────

test('admin can assign a template to an existing employee, creating correct policy rows', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'employment_start_date' => '2025-01-01']);
    $cl = lptLeaveType(['name' => 'Casual', 'code' => 'CL-'.uniqid()]);
    $sl = lptLeaveType(['name' => 'Sick', 'code' => 'SL-'.uniqid()]);

    $template = LeavePolicyTemplate::create(['name' => 'T1', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $cl->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    $template->items()->create(['leave_type_id' => $sl->id, 'annual_entitlement' => 6, 'allocation_mode' => 'monthly_accrual', 'monthly_accrual_amount' => 0.5]);

    $this->actingAs($admin->fresh())->post(route('admin.employees.leave-policy-template.assign', $employee), [
        'leave_policy_template_id' => $template->id,
        'effective_from' => '2026-01-01',
    ])->assertRedirect(route('admin.employees.leave-policies.index', $employee));

    $employee->refresh();
    expect($employee->leave_policy_template_id)->toBe($template->id);
    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->count())->toBe(2);
    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->where('leave_type_id', $cl->id)->first()->annual_entitlement)->toEqual(12.0);
});

// ── E/F/G: new employee auto-assignment ────────────────────────────────────

test('creating a new employee with a default template auto-creates policy rows effective from employment start date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lptLeaveType();
    $template = LeavePolicyTemplate::create(['name' => 'Default T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 15, 'allocation_mode' => 'yearly']);
    app(LeavePolicyAssignmentService::class)->setDefault($template);

    $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
        'name' => 'New Emp', 'email' => 'newemp@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
        'employment_start_date' => '2026-03-15',
    ])->assertRedirect(route('admin.employees.index'));

    $employee = User::where('email', 'newemp@example.com')->first();
    expect($employee)->not->toBeNull();
    expect($employee->leave_policy_template_id)->toBe($template->id);

    $policy = EmployeeLeavePolicy::where('user_id', $employee->id)->where('leave_type_id', $type->id)->first();
    expect($policy)->not->toBeNull();
    expect($policy->effective_from->toDateString())->toBe('2026-03-15');
});

test('creating a new employee with an explicit different template overrides the default', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lptLeaveType();

    $default = LeavePolicyTemplate::create(['name' => 'Default T', 'created_by' => $admin->id]);
    $default->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 15, 'allocation_mode' => 'yearly']);
    app(LeavePolicyAssignmentService::class)->setDefault($default);

    $other = LeavePolicyTemplate::create(['name' => 'Other T', 'created_by' => $admin->id]);
    $other->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 20, 'allocation_mode' => 'yearly']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
        'name' => 'New Emp 2', 'email' => 'newemp2@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
        'employment_start_date' => '2026-03-15',
        'leave_policy_template_id' => $other->id,
    ])->assertRedirect(route('admin.employees.index'));

    $employee = User::where('email', 'newemp2@example.com')->first();
    expect($employee->leave_policy_template_id)->toBe($other->id);

    $policy = EmployeeLeavePolicy::where('user_id', $employee->id)->where('leave_type_id', $type->id)->first();
    expect((float) $policy->annual_entitlement)->toEqual(20.0);
});

test('creating a new employee with no template selected and no default configured creates zero policy rows', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
        'name' => 'New Emp 3', 'email' => 'newemp3@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
        'employment_start_date' => '2026-03-15',
    ])->assertRedirect(route('admin.employees.index'));

    $employee = User::where('email', 'newemp3@example.com')->first();
    expect($employee)->not->toBeNull();
    expect($employee->leave_policy_template_id)->toBeNull();
    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->count())->toBe(0);
});

// ── H: existing per-employee override path is unaffected ──────────────────

test('employee-specific override via the existing controller does not modify the template items', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lptLeaveType();

    $template = LeavePolicyTemplate::create(['name' => 'T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 10, 'allocation_mode' => 'yearly']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.leave-policies.store', $employee), [
        'leave_type_id' => $type->id, 'annual_entitlement' => 25,
        'allocation_mode' => 'yearly', 'effective_from' => '2026-01-01',
    ])->assertRedirect(route('admin.employees.leave-policies.index', $employee));

    $template->refresh();
    expect((float) $template->items->first()->annual_entitlement)->toEqual(10.0);

    $policy = EmployeeLeavePolicy::where('user_id', $employee->id)->first();
    expect((float) $policy->annual_entitlement)->toEqual(25.0);
});

// ── O: idempotency when running allocation twice for template-assigned employee ──

test('running generateForUser twice for a template-assigned employee produces no duplicate allocations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'employment_start_date' => '2025-01-01']);
    $type = lptLeaveType();

    $template = LeavePolicyTemplate::create(['name' => 'T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);

    app(LeavePolicyAssignmentService::class)->assignTemplate($employee, $template, $admin, Carbon::parse('2025-01-01'));

    $service = app(LeaveAllocationService::class);
    $first = $service->generateForUser($employee, Carbon::parse('2026-06-01'));
    $second = $service->generateForUser($employee, Carbon::parse('2026-06-01'));

    expect(count($first))->toBeGreaterThan(0);
    expect($second)->toBe([]);
    expect(\App\Models\EmployeeLeaveAllocation::where('user_id', $employee->id)->count())->toBe(count($first));
});

// ── W: employee creation + assignment is atomic ────────────────────────────

test('employee creation and default template assignment is atomic on failure', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lptLeaveType();

    $template = LeavePolicyTemplate::create(['name' => 'Broken T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    app(LeavePolicyAssignmentService::class)->setDefault($template);

    // Force a failure in the assignment step itself (simulating "an
    // invalid template item causing a DB error") by swapping in a mock
    // that throws when assignTemplate() is called — this isolates the
    // transaction-boundary assertion (no User row survives) from any
    // particular DB engine's constraint-enforcement quirks.
    $mock = Mockery::mock(LeavePolicyAssignmentService::class);
    $mock->shouldReceive('assignTemplate')->once()->andThrow(new \RuntimeException('Simulated assignment failure'));
    $this->app->instance(LeavePolicyAssignmentService::class, $mock);

    $countBefore = User::count();

    try {
        $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
            'name' => 'Doomed Emp', 'email' => 'doomed@example.com', 'password' => 'Password123!',
            'role' => 'employee', 'is_active' => 1,
            'employment_start_date' => '2026-03-15',
        ]);
    } catch (\Throwable $e) {
        // The exception propagating out of the transaction is expected —
        // the assertion below on User::count() is what actually matters.
    }

    expect(User::where('email', 'doomed@example.com')->exists())->toBeFalse();
    expect(User::count())->toBe($countBefore);
});

// ── X: reassigning a template leaves old policy rows untouched ────────────

test('assigning a new template to an already-assigned employee leaves old policy rows queryable for past dates', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee']);
    $type = lptLeaveType();

    $templateA = LeavePolicyTemplate::create(['name' => 'A', 'created_by' => $admin->id]);
    $templateA->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 10, 'allocation_mode' => 'yearly']);

    $templateB = LeavePolicyTemplate::create(['name' => 'B', 'created_by' => $admin->id]);
    $templateB->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 20, 'allocation_mode' => 'yearly']);

    $service = app(LeavePolicyAssignmentService::class);
    $service->assignTemplate($employee, $templateA, $admin, Carbon::parse('2025-01-01'));
    $service->assignTemplate($employee, $templateB, $admin, Carbon::parse('2026-01-01'));

    $employee->refresh();
    expect($employee->leave_policy_template_id)->toBe($templateB->id);

    // Past date still resolves to the OLD policy row (10 days), and current
    // date resolves to the NEW one (20 days) — effective_from-only
    // selection, untouched.
    $old = EmployeeLeavePolicy::currentFor($employee, $type, Carbon::parse('2025-06-01'));
    $new = EmployeeLeavePolicy::currentFor($employee, $type, Carbon::parse('2026-06-01'));

    expect((float) $old->annual_entitlement)->toEqual(10.0);
    expect((float) $new->annual_entitlement)->toEqual(20.0);
    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->count())->toBe(2);
});

// ── Set default / clear default ────────────────────────────────────────────

test('setting a template as default clears the previous default', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $t1 = LeavePolicyTemplate::create(['name' => 'T1', 'created_by' => $admin->id, 'is_default' => true]);
    $t2 = LeavePolicyTemplate::create(['name' => 'T2', 'created_by' => $admin->id]);

    $this->actingAs($admin->fresh())->patch(route('admin.leave-policy-templates.set-default', $t2))
        ->assertRedirect();

    expect($t1->fresh()->is_default)->toBeFalse();
    expect($t2->fresh()->is_default)->toBeTrue();
});

// ── Bulk assignment to existing employees ──────────────────────────────────

test('admin can bulk-assign a template to multiple existing employees', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $e1 = User::factory()->create(['role' => 'employee']);
    $e2 = User::factory()->create(['role' => 'employee']);
    $type = lptLeaveType();

    $template = LeavePolicyTemplate::create(['name' => 'Bulk T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 8, 'allocation_mode' => 'yearly']);

    $this->actingAs($admin->fresh())->post(route('admin.leave-policy-templates.bulk-assign'), [
        'leave_policy_template_id' => $template->id,
        'effective_from' => '2026-01-01',
        'employee_ids' => [$e1->id, $e2->id],
    ])->assertRedirect();

    expect($e1->fresh()->leave_policy_template_id)->toBe($template->id);
    expect($e2->fresh()->leave_policy_template_id)->toBe($template->id);
    expect(EmployeeLeavePolicy::where('user_id', $e1->id)->count())->toBe(1);
    expect(EmployeeLeavePolicy::where('user_id', $e2->id)->count())->toBe(1);
});

// ── Authoritative template: leave-type removal on switch ───────────────────

test('switching templates closes a leave type present in the old template but absent from the new one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'employment_start_date' => '2025-01-01']);
    $casual = lptLeaveType(['name' => 'Casual Leave']);
    $sick = lptLeaveType(['name' => 'Sick Leave']);
    $earned = lptLeaveType(['name' => 'Earned Leave']);

    $templateA = LeavePolicyTemplate::create(['name' => 'A', 'created_by' => $admin->id]);
    $templateA->items()->create(['leave_type_id' => $casual->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    $templateA->items()->create(['leave_type_id' => $sick->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    $templateA->items()->create(['leave_type_id' => $earned->id, 'annual_entitlement' => 15, 'allocation_mode' => 'yearly']);

    $templateB = LeavePolicyTemplate::create(['name' => 'B', 'created_by' => $admin->id]);
    $templateB->items()->create(['leave_type_id' => $sick->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    $templateB->items()->create(['leave_type_id' => $earned->id, 'annual_entitlement' => 15, 'allocation_mode' => 'yearly']);

    $service = app(LeavePolicyAssignmentService::class);
    $service->assignTemplate($employee, $templateA, $admin, Carbon::parse('2026-01-01'));
    $service->assignTemplate($employee, $templateB, $admin, Carbon::parse('2026-09-01'));

    // Before the switch: all three leave types resolve to Template A's rows.
    expect((float) EmployeeLeavePolicy::currentFor($employee, $casual, Carbon::parse('2026-06-01'))->annual_entitlement)->toBe(12.0);
    expect((float) EmployeeLeavePolicy::currentFor($employee, $sick, Carbon::parse('2026-06-01'))->annual_entitlement)->toBe(12.0);

    // From the switch date on: Casual Leave is closed (removed), Sick/Earned carry Template B's rows.
    $casualAfter = EmployeeLeavePolicy::currentFor($employee, $casual, Carbon::parse('2026-09-01'));
    expect($casualAfter)->not->toBeNull();
    expect($casualAfter->isRemoved())->toBeTrue();
    expect((float) $casualAfter->annual_entitlement)->toBe(0.0);

    $sickAfter = EmployeeLeavePolicy::currentFor($employee, $sick, Carbon::parse('2026-09-01'));
    expect($sickAfter->isRemoved())->toBeFalse();
    expect((float) $sickAfter->annual_entitlement)->toBe(12.0);

    // History is untouched — a date before the switch still resolves Casual Leave correctly.
    $casualBefore = EmployeeLeavePolicy::currentFor($employee, $casual, Carbon::parse('2026-06-01'));
    expect($casualBefore->isRemoved())->toBeFalse();
    expect((float) $casualBefore->annual_entitlement)->toBe(12.0);

    // No historical row was mutated or deleted.
    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->where('leave_type_id', $casual->id)->count())->toBe(2);
});

test('a removed leave type generates no further allocations after the closing date, but leaves prior allocations intact', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $employee = User::factory()->create(['role' => 'employee', 'employment_start_date' => '2025-01-01']);
    $casual = lptLeaveType(['name' => 'Casual Leave']);

    $templateA = LeavePolicyTemplate::create(['name' => 'A', 'created_by' => $admin->id]);
    $templateA->items()->create(['leave_type_id' => $casual->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    $templateB = LeavePolicyTemplate::create(['name' => 'B', 'created_by' => $admin->id]);

    $service = app(LeavePolicyAssignmentService::class);
    $service->assignTemplate($employee, $templateA, $admin, Carbon::parse('2025-01-01'));

    $allocationsBeforeSwitch = app(LeaveAllocationService::class)->generateForUser($employee, Carbon::parse('2026-01-01'));
    expect($allocationsBeforeSwitch)->not->toBeEmpty();
    $countBefore = \App\Models\EmployeeLeaveAllocation::where('user_id', $employee->id)->where('leave_type_id', $casual->id)->count();

    $service->assignTemplate($employee, $templateB, $admin, Carbon::parse('2026-06-01'));

    $allocationsAfterSwitch = app(LeaveAllocationService::class)->generateForUser($employee, Carbon::parse('2027-01-01'));
    expect($allocationsAfterSwitch)->toBe([]); // 'removed' mode isn't recognized -> LeaveAllocationService's default arm produces nothing

    $countAfter = \App\Models\EmployeeLeaveAllocation::where('user_id', $employee->id)->where('leave_type_id', $casual->id)->count();
    expect($countAfter)->toBe($countBefore); // no new allocation, but the 2026 one already granted remains
});

// ── employment_start_date must not be silently invented ────────────────────

test('creating an employee with a leave policy template but no employment_start_date is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lptLeaveType();
    $template = LeavePolicyTemplate::create(['name' => 'T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
        'name' => 'No Start Date', 'email' => 'nostart@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
        'leave_policy_template_id' => $template->id,
    ])->assertSessionHasErrors('employment_start_date');

    expect(User::where('email', 'nostart@example.com')->exists())->toBeFalse();
});

test('creating an employee with no employment_start_date is rejected when a default template is configured', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $type = lptLeaveType();
    $template = LeavePolicyTemplate::create(['name' => 'Default T', 'created_by' => $admin->id]);
    $template->items()->create(['leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly']);
    app(LeavePolicyAssignmentService::class)->setDefault($template);

    $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
        'name' => 'No Start Date 2', 'email' => 'nostart2@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
    ])->assertSessionHasErrors('employment_start_date');

    expect(User::where('email', 'nostart2@example.com')->exists())->toBeFalse();
});

test('creating an employee with no employment_start_date succeeds when no template will be assigned', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin->fresh())->post(route('admin.employees.store'), [
        'name' => 'No Leave At All', 'email' => 'noleave@example.com', 'password' => 'Password123!',
        'role' => 'employee', 'is_active' => 1,
    ])->assertRedirect(route('admin.employees.index'));

    $employee = User::where('email', 'noleave@example.com')->first();
    expect($employee)->not->toBeNull();
    expect($employee->leave_policy_template_id)->toBeNull();
    expect(EmployeeLeavePolicy::where('user_id', $employee->id)->count())->toBe(0);
});
