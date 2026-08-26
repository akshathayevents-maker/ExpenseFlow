<?php

use App\Models\EmployeeOvertime;
use App\Models\EmployeeOvertimeConfig;
use App\Models\EmployeeSalary;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\QueryException;

// ── Configuration reuse (no new config table) ─────────────────────────────

test('standard working hours and OT multipliers are seeded via the existing settings table', function () {
    expect((float) Setting::get('standard_working_hours_per_day'))->toBe(8.0);
    expect(Setting::get('ot_multipliers'))->toBe(['weekday' => 1.5, 'weekend' => 2.0, 'holiday' => 2.0]);
});

// ── Multiple claims per day allowed (locked decision #3) ──────────────────

test('multiple OT claims for the same user and date are allowed, not blocked at the DB level', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    foreach ([2.0, 1.5] as $hours) {
        EmployeeOvertime::create([
            'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => $hours,
            'category' => 'weekday', 'reason' => 'Shift extension',
            'origin' => 'employee_request', 'created_by' => $user->id,
        ]);
    }

    expect(EmployeeOvertime::whereDate('ot_date', '2026-08-24')->where('user_id', $user->id)->count())->toBe(2);
});

// ── Financial fields are not mass-assignable ──────────────────────────────

test('hourly_rate_snapshot, rate_multiplier and calculated_amount are not fillable', function () {
    $user = User::factory()->create();

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'Test',
        'hourly_rate_snapshot' => 999999, 'rate_multiplier' => 99, 'calculated_amount' => 999999, // attempted injection
        'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    expect($ot->fresh()->hourly_rate_snapshot)->toBeNull();
    expect($ot->fresh()->rate_multiplier)->toBeNull();
    expect($ot->fresh()->calculated_amount)->toBeNull();
});

// ── Category snapshot survives a later holiday-config change ──────────────

test('category is snapshotted and does not change if holidays are edited afterward', function () {
    $user = User::factory()->create();
    Holiday::create(['holiday_date' => '2026-08-24', 'name' => 'Test Holiday', 'is_active' => true]);

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'holiday', 'reason' => 'Worked on a holiday',
        'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    Holiday::where('holiday_date', '2026-08-24')->update(['is_active' => false]);

    expect($ot->fresh()->category)->toBe('holiday'); // unchanged despite the holiday being deactivated
});

// ── Financial lock semantics ───────────────────────────────────────────────

test('isFinancialsLocked is true once approved, even before payment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'Test',
        'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    expect($ot->isFinancialsLocked())->toBeFalse();

    $ot->forceFill([
        'request_status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now(),
        'hourly_rate_snapshot' => 125.00, 'rate_multiplier' => 1.5, 'calculated_amount' => 375.00,
    ])->save();

    expect($ot->fresh()->isFinancialsLocked())->toBeTrue();
});

// ── Salary-basis correctness across a mid-month change (locked decision #8) ──

test('OT on an earlier date resolves the salary that was effective on that date, not the current one', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $s1 = new EmployeeSalary();
    $s1->fill(['user_id' => $user->id, 'monthly_salary' => 30000, 'effective_from' => '2026-08-01']);
    $s1->forceFill(['effective_to' => '2026-08-14', 'created_by' => $admin->id]);
    $s1->save();

    $s2 = new EmployeeSalary();
    $s2->fill(['user_id' => $user->id, 'monthly_salary' => 35000, 'effective_from' => '2026-08-15']);
    $s2->forceFill(['effective_to' => null, 'created_by' => $admin->id]);
    $s2->save();

    $earlySalary = $user->currentSalaryAsOf(\Carbon\Carbon::parse('2026-08-10'));
    $laterSalary = $user->currentSalaryAsOf(\Carbon\Carbon::parse('2026-08-20'));

    expect((float) $earlySalary->monthly_salary)->toBe(30000.0);
    expect((float) $laterSalary->monthly_salary)->toBe(35000.0);
});

// ── Origin / provenance ─────────────────────────────────────────────────────

test('admin can record a historical OT entry with origin admin_recorded', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-07-01', 'hours' => 3,
        'category' => 'weekday', 'reason' => 'Recorded retroactively by admin',
        'origin' => 'admin_recorded', 'created_by' => $admin->id,
    ]);

    expect($ot->origin)->toBe('admin_recorded');
    expect($ot->creator->id)->toBe($admin->id);
});

// ── Cross-employee isolation (same pattern as leave/advance) ───────────────

test('one employee cannot see another employee OT via the relationship scope', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    EmployeeOvertime::create([
        'user_id' => $a->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'Test', 'origin' => 'employee_request', 'created_by' => $a->id,
    ]);

    expect($a->overtimeRecords()->count())->toBe(1);
    expect($b->overtimeRecords()->count())->toBe(0);
});

// ── Future payroll pickup query shape (index-served, no payroll engine needed) ──

test('approved and unpaid OT is queryable for a future payroll pickup without a payroll table', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $approvedUnpaid = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-10', 'hours' => 2, 'category' => 'weekday',
        'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $approvedUnpaid->forceFill(['request_status' => 'approved', 'calculated_amount' => 375])->save();

    $approvedPaid = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-05', 'hours' => 1, 'category' => 'weekday',
        'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $approvedPaid->forceFill(['request_status' => 'approved', 'calculated_amount' => 187.5, 'paid_at' => now()])->save();

    $pending = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-20', 'hours' => 1, 'category' => 'weekday',
        'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    $pickupQuery = EmployeeOvertime::where('request_status', 'approved')->whereNull('paid_at')->get();

    expect($pickupQuery)->toHaveCount(1);
    expect($pickupQuery->first()->id)->toBe($approvedUnpaid->id);
});

// ── Duplicate detection (application-level, no unique DB constraint) ──────

test('exact duplicate user_id+ot_date+hours is detected', function () {
    $user = User::factory()->create();

    EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);

    expect(EmployeeOvertime::duplicateExists($user->id, '2026-08-24', 2))->toBeTrue();
    expect(EmployeeOvertime::duplicateExists($user->id, '2026-08-24', 3))->toBeFalse(); // different hours, not a duplicate
});

// ── Financial immutability once approved/paid ──────────────────────────────

test('approved OT financial fields cannot be changed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user  = User::factory()->create();

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $ot->forceFill(['request_status' => 'approved', 'reviewed_by' => $admin->id, 'calculated_amount' => 375])->save();

    $ot->calculated_amount = 999;
    $ot->save();
})->throws(RuntimeException::class);

test('paid OT financial fields cannot be changed', function () {
    $user = User::factory()->create();

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $ot->forceFill(['request_status' => 'approved', 'calculated_amount' => 375, 'paid_at' => now()])->save();

    $ot->hours = 5;
    $ot->save();
})->throws(RuntimeException::class);

test('approved OT approved_amount and used_manual_override cannot be changed once approved', function () {
    $user = User::factory()->create();

    $ot = EmployeeOvertime::create([
        'user_id' => $user->id, 'ot_date' => '2026-08-24', 'hours' => 2,
        'category' => 'weekday', 'reason' => 'x', 'origin' => 'employee_request', 'created_by' => $user->id,
    ]);
    $ot->forceFill([
        'request_status' => 'approved', 'calculated_amount' => 375, 'approved_amount' => 375,
        'used_manual_override' => false,
    ])->save();

    $ot->approved_amount = 999;
    $ot->save();
})->throws(RuntimeException::class);

// ── Overtime redesign: per-employee OT multiplier configuration ───────────
// (new table, replacing an automatic date-category multiplier lookup at
// creation time with an explicit, per-employee-configurable choice made at
// approval time)

test('employee_overtime_configs table stores one row per employee with allowed and default multipliers', function () {
    $user = User::factory()->create();

    $config = EmployeeOvertimeConfig::create([
        'user_id' => $user->id,
        'allowed_multipliers' => [1.0, 1.5, 2.0],
        'default_multiplier' => 1.5,
    ]);

    expect(array_map('floatval', $config->fresh()->allowed_multipliers))->toBe([1.0, 1.5, 2.0]);
    expect((float) $config->fresh()->default_multiplier)->toBe(1.5);
});

test('a second config row for the same user is rejected by the unique constraint', function () {
    $user = User::factory()->create();
    EmployeeOvertimeConfig::create(['user_id' => $user->id, 'allowed_multipliers' => [1.5], 'default_multiplier' => 1.5]);

    EmployeeOvertimeConfig::create(['user_id' => $user->id, 'allowed_multipliers' => [2.0], 'default_multiplier' => 2.0]);
})->throws(QueryException::class);

test('an employee with no config row gets the implicit default of [1.5] / 1.5', function () {
    $user = User::factory()->create();

    expect(EmployeeOvertimeConfig::allowedMultipliersFor($user))->toBe([1.5]);
    expect(EmployeeOvertimeConfig::defaultMultiplierFor($user))->toBe(1.5);
});

test('an employee WITH a config row uses their configured values, not the implicit default', function () {
    $user = User::factory()->create();
    EmployeeOvertimeConfig::create([
        'user_id' => $user->id, 'allowed_multipliers' => [1.0, 2.0], 'default_multiplier' => 2.0,
    ]);

    expect(array_map('floatval', EmployeeOvertimeConfig::allowedMultipliersFor($user)))->toBe([1.0, 2.0]);
    expect(EmployeeOvertimeConfig::defaultMultiplierFor($user))->toBe(2.0);
});

test('no row is backfilled for existing employees — absence is a valid state, not a data-integrity gap', function () {
    User::factory()->count(5)->create();

    expect(EmployeeOvertimeConfig::count())->toBe(0);
});
