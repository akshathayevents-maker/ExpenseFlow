<?php

use App\Models\AdvanceTransaction;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeaveAllocation;
use App\Models\EmployeeLeaveLedger;
use App\Models\EmployeeLeavePolicy;
use App\Models\EmployeeSalary;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\QueryException;

// ── Settings reuse (no new company_settings table) ──────────────────────

test('weekly off days setting is seeded and readable via existing Setting model', function () {
    expect(Setting::get('weekly_off_days'))->toBe([0]);
});

// ── Employee identity additions ──────────────────────────────────────────

test('user has nullable employment dates', function () {
    $user = User::factory()->create();
    expect($user->employment_start_date)->toBeNull();
    expect($user->employment_end_date)->toBeNull();

    $user->update([
        'employment_start_date' => '2026-01-15',
        'employment_end_date'   => null,
    ]);

    expect($user->fresh()->employment_start_date->toDateString())->toBe('2026-01-15');
});

// ── Salary history: effective-dated, never overwritten ───────────────────

test('salary history never overwrites — a change closes the old row and inserts a new one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $first = new EmployeeSalary();
    $first->fill(['user_id' => $user->id, 'monthly_salary' => 30000, 'effective_from' => '2026-01-01']);
    $first->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $first->save();

    // Simulate a salary change on 15 Aug: close the old row, insert the new one.
    $first->forceFill(['effective_to' => '2026-08-14'])->save();
    $second = new EmployeeSalary();
    $second->fill(['user_id' => $user->id, 'monthly_salary' => 35000, 'effective_from' => '2026-08-15']);
    $second->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $second->save();

    expect($user->salaries()->count())->toBe(2);
    expect($first->fresh()->monthly_salary)->toEqual('30000.00'); // old row untouched, just closed

    $before = $user->currentSalaryAsOf(\Carbon\Carbon::parse('2026-06-01'));
    $after  = $user->currentSalaryAsOf(\Carbon\Carbon::parse('2026-08-20'));
    $onChangeDay = $user->currentSalaryAsOf(\Carbon\Carbon::parse('2026-08-15'));

    expect($before->id)->toBe($first->id);
    expect($after->id)->toBe($second->id);
    expect($onChangeDay->id)->toBe($second->id); // change day belongs to the new period
});

test('user with no salary record resolves to null, not an exception', function () {
    $user = User::factory()->create();
    expect($user->currentSalaryAsOf(now()))->toBeNull();
});

// ── Attendance: one row per employee per date ─────────────────────────────

test('duplicate attendance for the same user and date is rejected at the database level', function () {
    $user = User::factory()->create();

    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => '2026-08-24',
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    expect(fn () => EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => '2026-08-24',
        'status' => 'half_day', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]))->toThrow(QueryException::class);
});

test('same date is allowed for two different users', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    foreach ([$a, $b] as $u) {
        EmployeeAttendance::create([
            'user_id' => $u->id, 'attendance_date' => '2026-08-24',
            'status' => 'present', 'marked_by' => $u->id, 'marked_at' => now(), 'source' => 'self',
        ]);
    }

    expect(EmployeeAttendance::whereDate('attendance_date', '2026-08-24')->count())->toBe(2);
});

test('attendance correction preserves previous_status and provenance columns', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $row = EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => '2026-08-20',
        'status' => 'present', 'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);

    $row->update([
        'previous_status'   => $row->status,
        'status'            => 'absent',
        'corrected_by'      => $admin->id,
        'corrected_at'      => now(),
        'correction_reason' => 'Employee confirmed they were on unpaid leave, not present.',
    ]);

    $row->refresh();
    expect($row->status)->toBe('absent');
    expect($row->previous_status)->toBe('present');
    expect($row->correctedBy->id)->toBe($admin->id);
});

test('attendance rows written by leave approval carry source and leave_request_id', function () {
    $user = User::factory()->create();
    $type = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'allow_half_day' => true, 'is_active' => true]);

    $request = hardenedLeaveRequest([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'start_date' => '2026-08-20', 'end_date' => '2026-08-20',
        'is_half_day' => false, 'days_requested' => 1, 'reason' => 'Personal', 'status' => 'approved',
    ]);

    $attendance = EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => '2026-08-20',
        'status' => 'leave', 'source' => 'leave_approval', 'leave_request_id' => $request->id,
    ]);

    expect($attendance->leaveRequest->id)->toBe($request->id);
    expect($request->attendanceRows()->count())->toBe(1);
});

// ── Leave allocation idempotency (the actual DB-level guard) ──────────────

test('duplicate yearly allocation for the same user/type/year is rejected', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();
    $type  = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'allow_half_day' => true, 'is_active' => true]);

    EmployeeLeaveAllocation::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'period_year' => 2026, 'period_month' => 0, // 0 = yearly grant sentinel
        'allocated_amount' => 12, 'source' => 'yearly_grant', 'created_by' => $admin->id,
    ]);

    expect(fn () => EmployeeLeaveAllocation::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'period_year' => 2026, 'period_month' => 0,
        'allocated_amount' => 12, 'source' => 'yearly_grant', 'created_by' => $admin->id,
    ]))->toThrow(QueryException::class);
});

test('duplicate monthly accrual for the same user/type/period is rejected but distinct months are allowed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();
    $type  = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'allow_half_day' => true, 'is_active' => true]);

    EmployeeLeaveAllocation::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'period_year' => 2026, 'period_month' => 8,
        'allocated_amount' => 1, 'source' => 'monthly_accrual', 'created_by' => $admin->id,
    ]);

    // Same month again — rejected (this is the guard that makes the
    // month-end command safely re-runnable).
    expect(fn () => EmployeeLeaveAllocation::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'period_year' => 2026, 'period_month' => 8,
        'allocated_amount' => 1, 'source' => 'monthly_accrual', 'created_by' => $admin->id,
    ]))->toThrow(QueryException::class);

    // A different month is a distinct allocation — allowed.
    EmployeeLeaveAllocation::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id,
        'period_year' => 2026, 'period_month' => 9,
        'allocated_amount' => 1, 'source' => 'monthly_accrual', 'created_by' => $admin->id,
    ]);

    expect($user->leaveAllocations()->count())->toBe(2);
});

test('leave ledger balance is derivable from SUM(amount), never a stored running total', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();
    $type  = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'allow_half_day' => true, 'is_active' => true]);

    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-01-01', 'type' => 'allocation', 'amount' => 12, 'created_by' => $admin->id]);
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-03-05', 'type' => 'usage', 'amount' => -1.5, 'created_by' => $admin->id]);
    EmployeeLeaveLedger::create(['user_id' => $user->id, 'leave_type_id' => $type->id, 'entry_date' => '2026-04-01', 'type' => 'reversal', 'amount' => 0.5, 'created_by' => $admin->id]);

    $balance = $user->leaveLedgerEntries()->where('leave_type_id', $type->id)->sum('amount');
    expect((float) $balance)->toBe(11.0);
});

// ── Leave policy effective-dating ─────────────────────────────────────────

test('leave policy is employee-specific and resolves the correct effective row as of a given date', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();
    $otherUser = User::factory()->create();
    $type  = LeaveType::create(['name' => 'Casual Leave', 'code' => 'CL', 'allow_half_day' => true, 'is_active' => true]);

    $old = EmployeeLeavePolicy::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'annual_entitlement' => 12, 'allocation_mode' => 'yearly',
        'effective_from' => '2025-01-01', 'is_active' => true, 'created_by' => $admin->id,
    ]);
    $new = EmployeeLeavePolicy::create([
        'user_id' => $user->id, 'leave_type_id' => $type->id, 'annual_entitlement' => 15, 'allocation_mode' => 'yearly',
        'effective_from' => '2026-07-01', 'is_active' => true, 'created_by' => $admin->id,
    ]);
    // Different employee, different entitlement entirely — proves policies
    // are per-user, not global per leave type.
    $otherPolicy = EmployeeLeavePolicy::create([
        'user_id' => $otherUser->id, 'leave_type_id' => $type->id, 'annual_entitlement' => 20, 'allocation_mode' => 'yearly',
        'effective_from' => '2025-01-01', 'is_active' => true, 'created_by' => $admin->id,
    ]);

    expect(EmployeeLeavePolicy::currentFor($user, $type, \Carbon\Carbon::parse('2026-03-01'))->id)->toBe($old->id);
    expect(EmployeeLeavePolicy::currentFor($user, $type, \Carbon\Carbon::parse('2026-08-01'))->id)->toBe($new->id);
    expect(EmployeeLeavePolicy::currentFor($otherUser, $type, \Carbon\Carbon::parse('2026-08-01'))->id)->toBe($otherPolicy->id);
    expect((float) EmployeeLeavePolicy::currentFor($otherUser, $type, \Carbon\Carbon::parse('2026-08-01'))->annual_entitlement)->toBe(20.0);
});

// ── Advance ledger integrity ───────────────────────────────────────────────

test('outstanding_amount is not mass-assignable on EmployeeAdvance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $advance = new EmployeeAdvance();
    $advance->fill([
        'user_id' => $user->id, 'origin' => 'admin_recorded',
        'outstanding_amount' => 999999, // attempted injection via fill()
    ]);
    $advance->forceFill([
        'request_status' => 'approved', 'payment_status' => 'paid',
        'original_amount' => 20000, 'created_by' => $admin->id,
    ]);
    $advance->save();

    // outstanding_amount silently ignored by mass assignment; DB default (0) applies.
    expect((float) $advance->fresh()->outstanding_amount)->toBe(0.0);
});

test('advance transactions are linked and balance_after is stored per row, not recomputed on read', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $advance = new EmployeeAdvance();
    $advance->fill(['user_id' => $user->id, 'origin' => 'admin_recorded']);
    $advance->forceFill([
        'request_status' => 'approved', 'payment_status' => 'paid',
        'original_amount' => 20000, 'created_by' => $admin->id,
    ]);
    $advance->save();
    $advance->forceFill(['outstanding_amount' => 20000])->save();

    AdvanceTransaction::create([
        'employee_advance_id' => $advance->id, 'user_id' => $user->id,
        'transaction_date' => '2026-08-24', 'type' => 'recovery', 'amount' => 5000,
        'balance_after' => 15000, 'created_by' => $admin->id,
    ]);
    $advance->forceFill(['outstanding_amount' => 15000])->save();

    expect($advance->transactions()->count())->toBe(1);
    expect((float) $advance->transactions()->latest('id')->first()->balance_after)->toBe(15000.0);
    expect((float) $advance->fresh()->outstanding_amount)->toBe(15000.0);
});

test('employee cannot see another employee advance via the relationship scope', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $a = User::factory()->create();
    $b = User::factory()->create();

    $advance = new EmployeeAdvance();
    $advance->fill(['user_id' => $a->id, 'origin' => 'admin_recorded']);
    $advance->forceFill(['request_status' => 'approved', 'payment_status' => 'paid', 'original_amount' => 5000, 'created_by' => $admin->id]);
    $advance->save();

    expect($a->advances()->count())->toBe(1);
    expect($b->advances()->count())->toBe(0);
});

// ── Holidays ───────────────────────────────────────────────────────────────

test('holiday date is unique', function () {
    Holiday::create(['holiday_date' => '2026-08-15', 'name' => 'Independence Day', 'is_active' => true]);

    expect(fn () => Holiday::create(['holiday_date' => '2026-08-15', 'name' => 'Duplicate', 'is_active' => true]))
        ->toThrow(QueryException::class);
});
