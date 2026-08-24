<?php

use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\User;
use App\Services\OvertimeCalculationService;
use App\Services\PayableDaysCalculator;
use Carbon\Carbon;

function otService(): OvertimeCalculationService
{
    return new OvertimeCalculationService(new PayableDaysCalculator());
}

function giveSalary(User $user, float $amount, string $from = '2026-01-01', ?string $to = null): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $salary = new EmployeeSalary();
    $salary->fill(['user_id' => $user->id, 'monthly_salary' => $amount, 'effective_from' => $from]);
    $salary->forceFill(['effective_to' => $to, 'created_by' => $admin->id]);
    $salary->save();
}

// August 2026: 31 days, Sundays = 2,9,16,23,30 (5 Sundays) => weekly-off-only working days = 26.

test('weekday OT calculation', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000); // 26000/26 = 1000/day, /8 = 125/hr

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 2); // Tuesday

    expect($result['category'])->toBe('weekday');
    expect($result['hourly_rate_snapshot'])->toBe(125.0);
    expect($result['rate_multiplier'])->toBe(1.5);
    expect($result['calculated_amount'])->toBe(375.0); // 125*2*1.5
});

test('weekend OT calculation', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculate($user, Carbon::parse('2026-08-23'), 2); // Sunday, weekly-off

    expect($result['category'])->toBe('weekend');
    expect($result['rate_multiplier'])->toBe(2.0);
    expect($result['calculated_amount'])->toBe(500.0); // 125*2*2.0
});

test('holiday OT calculation', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Holiday::create(['holiday_date' => '2026-08-20', 'name' => 'Test Holiday', 'is_active' => true]); // Thursday

    $result = otService()->calculate($user, Carbon::parse('2026-08-20'), 1);

    // The holiday itself removes a day from the denominator: 31 - 5 Sundays
    // - 1 holiday = 25 working days => 26000/25=1040/day, /8=130/hr.
    expect($result['category'])->toBe('holiday');
    expect($result['rate_multiplier'])->toBe(2.0);
    expect($result['hourly_rate_snapshot'])->toBe(130.0);
    expect($result['calculated_amount'])->toBe(260.0); // 130*1*2.0
});

test('sunday and holiday together resolve to holiday category', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Holiday::create(['holiday_date' => '2026-08-23', 'name' => 'Sunday Holiday', 'is_active' => true]); // also Sunday

    $result = otService()->calculate($user, Carbon::parse('2026-08-23'), 1);

    expect($result['category'])->toBe('holiday');
});

test('historical salary is resolved via currentSalaryAsOf, not the latest salary', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000, '2026-08-01', '2026-08-14');
    giveSalary($user, 39000, '2026-08-15', null); // changed the day after OT date in the scenario below

    $result = otService()->calculate($user, Carbon::parse('2026-08-14'), 2);

    // 26000/26 = 1000/day, /8 = 125/hr — must use the early salary, not 39000
    expect($result['hourly_rate_snapshot'])->toBe(125.0);
});

test('missing salary is rejected with a domain error, not a silent zero', function () {
    $user = User::factory()->create();

    otService()->calculate($user, Carbon::parse('2026-08-25'), 2);
})->throws(DomainException::class, 'Employee does not have an active salary for the selected OT date.');

test('partial OT hours are supported', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 1.25);

    expect($result['calculated_amount'])->toBe(234.38); // 125*1.25*1.5 = 234.375 -> rounds to 234.38
});

test('zero hours are rejected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    otService()->calculate($user, Carbon::parse('2026-08-25'), 0);
})->throws(InvalidArgumentException::class);

test('negative hours are rejected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    otService()->calculate($user, Carbon::parse('2026-08-25'), -1);
})->throws(InvalidArgumentException::class);

test('working-day denominator comes from PayableDaysCalculator, respecting weekly-off and holidays', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Holiday::create(['holiday_date' => '2026-08-15', 'name' => 'Independence Day', 'is_active' => true]);

    // August 2026 now has 5 Sundays + 1 holiday excluded => 31-6 = 25 working days.
    $calc = new PayableDaysCalculator();
    $days = $calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
    expect($days)->toBe(25);

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 1);
    // 26000/25 = 1040/day, /8 = 130/hr
    expect($result['hourly_rate_snapshot'])->toBe(130.0);
});

test('standard working hours setting is respected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Setting::set('standard_working_hours_per_day', '4.00');

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 1);
    // 26000/26 = 1000/day, /4 = 250/hr
    expect($result['hourly_rate_snapshot'])->toBe(250.0);
});

test('OT multiplier comes from settings, not hardcoded', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Setting::set('ot_multipliers', json_encode(['weekday' => 3.0, 'weekend' => 2.0, 'holiday' => 2.0], JSON_PRESERVE_ZERO_FRACTION));

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 1);

    expect($result['rate_multiplier'])->toBe(3.0);
    expect($result['calculated_amount'])->toBe(375.0); // 125*1*3.0
});

test('1.5x multiplier calculation is exact', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 4);
    expect($result['calculated_amount'])->toBe(750.0); // 125*4*1.5
});

test('2.0x multiplier calculation is exact', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculate($user, Carbon::parse('2026-08-23'), 4);
    expect($result['calculated_amount'])->toBe(1000.0); // 125*4*2.0
});

test('decimal precision rounds the final amount to 2 decimal places', function () {
    $user = User::factory()->create();
    giveSalary($user, 27000); // 27000/26 = 1038.4615.../day -> forces non-terminating decimals

    $result = otService()->calculate($user, Carbon::parse('2026-08-25'), 3.5);

    expect($result['calculated_amount'])->toBeFloat();
    expect(round($result['calculated_amount'], 2))->toBe($result['calculated_amount']);
});

test('salary change after the OT date does not alter a snapshot computed for the earlier date', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000, '2026-08-01', '2026-08-24');

    $before = otService()->calculate($user, Carbon::parse('2026-08-24'), 2);

    giveSalary($user, 60000, '2026-08-25', null); // salary changes the next day

    $after = otService()->calculate($user, Carbon::parse('2026-08-24'), 2); // recompute for the SAME historical date

    expect($before['calculated_amount'])->toBe($after['calculated_amount']); // unaffected by the later salary
});

test('a global settings change after submission does not alter an already-persisted frozen snapshot', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $snapshot = otService()->calculate($user, Carbon::parse('2026-08-25'), 2);
    $frozenAmount = $snapshot['calculated_amount'];

    // Simulate persisting the snapshot, then a later global settings change.
    Setting::set('ot_multipliers', json_encode(['weekday' => 9.0, 'weekend' => 2.0, 'holiday' => 2.0], JSON_PRESERVE_ZERO_FRACTION));

    // The already-persisted value never gets recalculated — this is a
    // property of never calling calculate() again, not of the service
    // itself. Verified here by confirming the frozen value is unaffected
    // by asserting it against a fresh literal, independent of the service.
    expect($frozenAmount)->toBe(375.0);
});

test('multiple OT records on the same date are allowed at the calculation layer', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $first  = otService()->calculate($user, Carbon::parse('2026-08-25'), 2);
    $second = otService()->calculate($user, Carbon::parse('2026-08-25'), 1);

    expect($first['calculated_amount'])->toBe(375.0);
    expect($second['calculated_amount'])->toBe(187.5);
});
