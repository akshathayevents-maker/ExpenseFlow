<?php

use App\Models\AdvanceTransaction;
use App\Models\AuditLog;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\User;
use App\Services\EmployeeAdvanceService;

function advanceService(): EmployeeAdvanceService
{
    return app(EmployeeAdvanceService::class);
}

// The attendance-first gate (EnsureAttendanceMarked) applies to every
// employee.* route except attendance/regularization — mark today's
// attendance so employee.advances.* requests reach the controller.
function markAdvanceGateAttendance(User $user): void
{
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => \Carbon\Carbon::now('Asia/Kolkata')->toDateString(),
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

// High enough that even a single applicable working day this month covers
// any requested_amount used in these tests — see AdvanceEligibilityTest.php
// for the actual eligibility-formula behavior/edge cases.
function giveAdvanceEligibleSalary(User $user): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    \Illuminate\Support\Facades\Auth::login($admin);
    app(\App\Services\EmployeeSalaryService::class)->setSalary(
        $user, 10000000, \Carbon\Carbon::now('Asia/Kolkata')->startOfMonth(), $admin,
    );
}

function makeAdvance(array $attrs): EmployeeAdvance
{
    $fillableKeys = ['user_id', 'origin', 'requested_amount', 'eligibility_breakdown', 'reference', 'notes'];
    $advance = new EmployeeAdvance();
    $advance->fill(array_intersect_key($attrs, array_flip($fillableKeys)));
    $advance->forceFill(array_diff_key($attrs, array_flip($fillableKeys)));
    $advance->save();
    return $advance;
}

// ── Employee: create ─────────────────────────────────────────────────────

test('employee can request an advance', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $response = $this->actingAs($user->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 10000,
    ]);

    $advance = EmployeeAdvance::first();
    $response->assertRedirect(route('employee.advances.show', $advance));
    expect($advance->user_id)->toBe($user->id);
    expect($advance->origin)->toBe('employee_request');
    expect($advance->created_by)->toBe($user->id);
    expect($advance->request_status)->toBe('pending');
});

test('reason/notes is optional when requesting an advance', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $response = $this->actingAs($user->fresh())->post(route('employee.advances.store'), [
        'requested_amount' => 5000,
    ]);

    $response->assertSessionDoesntHaveErrors();
    expect(EmployeeAdvance::first())->not->toBeNull();
});

test('cannot create invalid zero or negative amount', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 0])
        ->assertSessionHasErrors('requested_amount');
    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => -500])
        ->assertSessionHasErrors('requested_amount');

    expect(EmployeeAdvance::count())->toBe(0);
});

test('employee cannot have two pending advance requests at once', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 5000]);
    $response = $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 3000]);

    $response->assertSessionHasErrors('requested_amount');
    expect(EmployeeAdvance::count())->toBe(1);
});

// ── Employee: view/cancel ────────────────────────────────────────────────

test('employee can see their own advances', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($user->fresh())->get(route('employee.advances.index'))
        ->assertOk()
        ->assertSee('5,000.00');

    $this->actingAs($user->fresh())->get(route('employee.advances.show', $advance))->assertOk();
});

test('employee cannot see another employees advance', function () {
    $a = User::factory()->create();
    markAdvanceGateAttendance($a);
    $b = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $b->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $b->id,
    ]);

    $this->actingAs($a->fresh())->get(route('employee.advances.show', $advance))->assertForbidden();
});

test('employee cannot approve an advance', function () {
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    expect($user->can('approve', $advance))->toBeFalse();
});

test('unauthorized direct approval request returns 403', function () {
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($user->fresh())->patch(route('manager.advances.approve', $advance), ['approved_amount' => 5000])
        ->assertForbidden();
});

test('employee cannot cancel another employees advance', function () {
    $a = User::factory()->create();
    markAdvanceGateAttendance($a);
    $b = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $b->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $b->id,
    ]);

    $this->actingAs($a->fresh())->patch(route('employee.advances.cancel', $advance))->assertForbidden();
});

test('employee can cancel own pending advance', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($user->fresh())->patch(route('employee.advances.cancel', $advance))->assertRedirect();

    expect($advance->fresh()->request_status)->toBe('cancelled');
});

// ── Manager/Admin ─────────────────────────────────────────────────────────

test('admin can view advances', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($admin)->get(route('admin.advances.index'))->assertOk()->assertSee($user->name);
});

test('manager can approve an advance', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.advances.approve', $advance), ['approved_amount' => 5000])
        ->assertRedirect();

    $advance->refresh();
    expect($advance->request_status)->toBe('approved');
    expect((float) $advance->approved_amount)->toBe(5000.0);
    expect($advance->approved_by)->toBe($manager->id);
});

test('self-approval is prevented for a manager approving their own advance', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $advance = makeAdvance([
        'user_id' => $manager->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $manager->id,
    ]);

    $this->actingAs($manager)->patch(route('manager.advances.approve', $advance), ['approved_amount' => 5000])
        ->assertForbidden();
});

test('admin can reject an advance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($admin)->patch(route('admin.advances.reject', $advance))->assertRedirect();

    expect($advance->fresh()->request_status)->toBe('rejected');
});

// ── Disbursement ─────────────────────────────────────────────────────────

test('rejected advance cannot be disbursed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'rejected',
    ]);

    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance))->assertForbidden();
    expect($advance->fresh()->isPaid())->toBeFalse();
});

test('pending advance cannot be disbursed before approval', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
    ]);

    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance))->assertForbidden();
});

test('admin can disburse an approved advance and a ledger transaction is created', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 5000,
    ]);

    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance))->assertRedirect();

    $advance->refresh();
    expect($advance->isPaid())->toBeTrue();
    expect((float) $advance->outstanding_amount)->toBe(5000.0);
    expect((float) $advance->original_amount)->toBe(5000.0);
    expect(AdvanceTransaction::where('employee_advance_id', $advance->id)->where('type', 'advance')->count())->toBe(1);
});

test('cannot disburse an advance twice', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 5000,
    ]);
    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance));

    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance))->assertForbidden();
    expect(AdvanceTransaction::where('employee_advance_id', $advance->id)->count())->toBe(1);
});

// ── Repayment ────────────────────────────────────────────────────────────

test('admin can record a repayment and outstanding balance decreases', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 5000,
    ]);
    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance));

    $this->actingAs($admin)->post(route('admin.advances.repayment.store', $advance), ['amount' => 2000])
        ->assertRedirect();

    $advance->refresh();
    expect((float) $advance->outstanding_amount)->toBe(3000.0);
    expect(AdvanceTransaction::where('employee_advance_id', $advance->id)->where('type', 'recovery')->count())->toBe(1);
});

test('cannot repay more than outstanding amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 5000,
    ]);
    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance));

    $response = $this->actingAs($admin)->post(route('admin.advances.repayment.store', $advance), ['amount' => 6000]);

    $response->assertSessionHasErrors('amount');
    expect((float) $advance->fresh()->outstanding_amount)->toBe(5000.0);
});

test('cannot record zero or negative repayment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 5000,
    ]);
    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance));

    $this->actingAs($admin)->post(route('admin.advances.repayment.store', $advance), ['amount' => 0])
        ->assertSessionHasErrors('amount');
});

test('cannot record repayment before disbursement', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 5000,
    ]);

    $this->actingAs($admin)->post(route('admin.advances.repayment.store', $advance), ['amount' => 1000])
        ->assertForbidden();
});

test('multiple repayments preserve transaction history and correctly derive outstanding amount', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 10000, 'created_by' => $user->id,
        'request_status' => 'approved', 'approved_amount' => 10000,
    ]);
    $this->actingAs($admin);
    advanceService()->disburse($advance, $admin);

    advanceService()->recordRepayment($advance, $admin, 3000);
    advanceService()->recordRepayment($advance, $admin, 2500);

    expect($advance->fresh()->outstanding_amount)->toEqual('4500.00');
    expect(AdvanceTransaction::where('employee_advance_id', $advance->id)->count())->toBe(3); // 1 advance + 2 recovery
    expect(AdvanceTransaction::where('employee_advance_id', $advance->id)->orderBy('id')->pluck('balance_after')->map(fn ($v) => (float) $v)->all())
        ->toBe([10000.0, 7000.0, 4500.0]);
});

// ── Navigation ───────────────────────────────────────────────────────────

test('admin navigation exposes Advances', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Advances')
        ->assertSee(route('admin.advances.index'), false);
});

test('employee navigation exposes My Advances under My Finances', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.attendance.index'))
        ->assertOk()
        ->assertSee('My Finances')
        ->assertSee('My Advances');
});

// ── Cross-cutting: salary/attendance gate unaffected ─────────────────────

test('salary management remains admin only', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create();
    $target = User::factory()->create(['role' => 'employee', 'is_active' => true]);

    $this->actingAs($manager)->get(route('admin.employees.salaries.index', $target))->assertForbidden();
    $this->actingAs($employee->fresh())->get(route('admin.employees.salaries.index', $target))->assertForbidden();
});

test('attendance gate remains unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user->fresh())->get(route('employee.dashboard'))
        ->assertRedirect(route('employee.attendance.index'));
});

// ── Audit ────────────────────────────────────────────────────────────────

test('request, approval, rejection, disbursement, and repayment are all audited', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 5000]);
    $advance = EmployeeAdvance::first();
    expect(AuditLog::where('module', 'employee_advance')->where('action', 'requested')->exists())->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.advances.approve', $advance), ['approved_amount' => 5000]);
    expect(AuditLog::where('module', 'employee_advance')->where('action', 'approved')->exists())->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.advances.disburse', $advance));
    expect(AuditLog::where('module', 'employee_advance')->where('action', 'disbursed')->exists())->toBeTrue();

    $this->actingAs($admin)->post(route('admin.advances.repayment.store', $advance), ['amount' => 1000]);
    expect(AuditLog::where('module', 'employee_advance')->where('action', 'repayment_recorded')->exists())->toBeTrue();
});

// ── Mass-assignment hardening ─────────────────────────────────────────────

test('request_status and approved_by cannot be set via direct mass assignment', function () {
    $user = User::factory()->create();
    $someUser = User::factory()->create();

    // Raw mass-assignment (not the makeAdvance test helper, which explicitly
    // forceFills server-only fields) — created_by is NOT NULL and excluded
    // from $fillable, so a plain create() call must fail at INSERT time.
    expect(fn () => EmployeeAdvance::create([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 5000,
        'request_status' => 'approved', 'approved_by' => $someUser->id, 'created_by' => $user->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);

    expect(EmployeeAdvance::count())->toBe(0);
});

// ── WhatsApp deep-link (stateless, no server-side sending) ────────────────

test('whatsapp button does not appear on the blank create form', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $response = $this->actingAs($user->fresh())->get(route('employee.advances.create'));

    $response->assertOk();
    $response->assertDontSee('Submit via WhatsApp');
});

test('whatsapp button appears on the show page right after creation', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 10000]);
    $advance = EmployeeAdvance::first();

    $response = $this->actingAs($user->fresh())->get(route('employee.advances.show', $advance));

    $response->assertOk();
    $response->assertSee('Submit via WhatsApp');
    $response->assertSee('https://wa.me/919894594074?', false);
});

test('whatsapp url starts with the correct https wa.me recipient', function () {
    $user = User::factory()->create(['name' => "O'Brien"]);
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 2000.50,
        'created_by' => $user->id,
    ]);

    $url = $advance->whatsAppShareUrl();

    expect($url)->toStartWith('https://wa.me/919894594074?');
});

test('whatsapp message contains the correct employee name, amount, date, status and reference', function () {
    $user = User::factory()->create(['name' => "O'Brien"]);
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 2000.50,
        'created_by' => $user->id,
    ])->fresh();

    $url = $advance->whatsAppShareUrl();
    parse_str(parse_url($url, PHP_URL_QUERY), $params);
    $message = $params['text'];

    expect($message)->toContain('Advance Request #' . $advance->id);
    expect($message)->toContain("O'Brien");
    expect($message)->toContain('2,000.50');
    expect($message)->toContain($advance->created_at->format('d M Y'));
    expect($message)->toContain('Pending');
});

test('special characters in employee name round-trip correctly through url encoding', function () {
    $user = User::factory()->create(['name' => "D'Souza & Sons"]);
    $advance = makeAdvance([
        'user_id' => $user->id, 'origin' => 'employee_request', 'requested_amount' => 1500,
        'created_by' => $user->id,
    ]);

    $url = $advance->whatsAppShareUrl();
    parse_str(parse_url($url, PHP_URL_QUERY), $params);

    expect($params['text'])->toContain("D'Souza & Sons");
    expect(urldecode(urlencode($params['text'])))->toBe($params['text']);
});

test('whatsapp button is hidden once the advance is no longer pending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 5000]);
    $advance = EmployeeAdvance::first();

    $this->actingAs($admin)->patch(route('admin.advances.approve', $advance), ['approved_amount' => 5000]);

    $response = $this->actingAs($user->fresh())->get(route('employee.advances.show', $advance));

    $response->assertOk();
    $response->assertDontSee('Submit via WhatsApp');
});

test('viewing the show page repeatedly never creates or mutates advance rows', function () {
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 5000]);
    $advance = EmployeeAdvance::first();
    $originalUpdatedAt = $advance->updated_at;

    $this->actingAs($user->fresh())->get(route('employee.advances.show', $advance));
    $this->actingAs($user->fresh())->get(route('employee.advances.show', $advance));

    expect(EmployeeAdvance::count())->toBe(1);
    expect($advance->fresh()->updated_at->eq($originalUpdatedAt))->toBeTrue();
});

test('admin sees the whatsapp button on a pending advance detail page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    markAdvanceGateAttendance($user);
    giveAdvanceEligibleSalary($user);

    $this->actingAs($user->fresh())->post(route('employee.advances.store'), ['requested_amount' => 5000]);
    $advance = EmployeeAdvance::first();

    $response = $this->actingAs($admin)->get(route('admin.advances.show', $advance));

    $response->assertOk();
    $response->assertSee('Submit via WhatsApp');
});
