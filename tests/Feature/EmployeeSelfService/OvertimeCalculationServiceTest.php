<?php

use App\Models\EmployeeSalary;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\User;
use App\Services\OvertimeCalculationService;
use App\Services\PayableDaysCalculator;
use Carbon\Carbon;

/**
 * REDESIGN: OvertimeCalculationService no longer applies an automatic
 * date-category multiplier at request-creation time. hourlyRateFor()
 * carries the SAME hourly-rate derivation formula the old calculate()
 * method used; calculateForApproval() applies an explicitly-chosen
 * multiplier (as an Admin/Manager would supply at approval time) instead of
 * an automatic category lookup. Every test below that used to assert
 * "amount is calculated/frozen at creation" now asserts the equivalent
 * numeric result via calculateForApproval(), exercised as if at approval
 * time with the equivalent multiplier — preserving the original formula-
 * correctness coverage under the new timing.
 */
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

test('hourly rate derivation on a weekday', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000); // 26000/26 = 1000/day, /8 = 125/hr

    expect(otService()->hourlyRateFor($user, Carbon::parse('2026-08-25')))->toBe(125.0); // Tuesday
});

test('approval-time calculation with a 1.5x multiplier (was: weekday auto-multiplier)', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 2, 1.5);

    expect($result['hourly_rate_snapshot'])->toBe(125.0);
    expect($result['rate_multiplier'])->toBe(1.5);
    expect($result['calculated_amount'])->toBe(375.0); // 125*2*1.5
});

test('approval-time calculation with a 2.0x multiplier (was: weekend auto-multiplier)', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-23'), 2, 2.0); // Sunday

    expect($result['rate_multiplier'])->toBe(2.0);
    expect($result['calculated_amount'])->toBe(500.0); // 125*2*2.0
});

test('holiday date reduces the working-day denominator, raising the hourly rate (was: holiday auto-multiplier)', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Holiday::create(['holiday_date' => '2026-08-20', 'name' => 'Test Holiday', 'is_active' => true]); // Thursday

    // The holiday itself removes a day from the denominator: 31 - 5 Sundays
    // - 1 holiday = 25 working days => 26000/25=1040/day, /8=130/hr.
    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-20'), 1, 2.0);

    expect($result['hourly_rate_snapshot'])->toBe(130.0);
    expect($result['calculated_amount'])->toBe(260.0); // 130*1*2.0
});

test('categoryForDate on PayableDaysCalculator still classifies a Sunday-holiday as holiday (label only, no automatic multiplier)', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Holiday::create(['holiday_date' => '2026-08-23', 'name' => 'Sunday Holiday', 'is_active' => true]); // also Sunday

    $calc = new PayableDaysCalculator();
    expect($calc->categoryForDate(Carbon::parse('2026-08-23')))->toBe('holiday');
});

test('historical salary is resolved via currentSalaryAsOf, not the latest salary', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000, '2026-08-01', '2026-08-14');
    giveSalary($user, 39000, '2026-08-15', null); // changed the day after OT date in the scenario below

    $rate = otService()->hourlyRateFor($user, Carbon::parse('2026-08-14'));

    // 26000/26 = 1000/day, /8 = 125/hr — must use the early salary, not 39000
    expect($rate)->toBe(125.0);
});

test('missing salary is rejected with a domain error, not a silent zero', function () {
    $user = User::factory()->create();

    otService()->hourlyRateFor($user, Carbon::parse('2026-08-25'));
})->throws(DomainException::class, 'Employee does not have an active salary for the selected OT date.');

test('missing salary at approval time is rejected the same way', function () {
    $user = User::factory()->create();

    otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 2, 1.5);
})->throws(DomainException::class, 'Employee does not have an active salary for the selected OT date.');

test('partial OT hours are supported', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 1.25, 1.5);

    expect($result['calculated_amount'])->toBe(234.38); // 125*1.25*1.5 = 234.375 -> rounds to 234.38
});

test('zero hours are rejected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 0, 1.5);
})->throws(InvalidArgumentException::class);

test('negative hours are rejected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), -1, 1.5);
})->throws(InvalidArgumentException::class);

test('zero or negative multiplier is rejected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 1, 0);
})->throws(InvalidArgumentException::class);

test('working-day denominator comes from PayableDaysCalculator, respecting weekly-off and holidays', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Holiday::create(['holiday_date' => '2026-08-15', 'name' => 'Independence Day', 'is_active' => true]);

    // August 2026 now has 5 Sundays + 1 holiday excluded => 31-6 = 25 working days.
    $calc = new PayableDaysCalculator();
    $days = $calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));
    expect($days)->toBe(25);

    $rate = otService()->hourlyRateFor($user, Carbon::parse('2026-08-25'));
    // 26000/25 = 1040/day, /8 = 130/hr
    expect($rate)->toBe(130.0);
});

test('standard working hours setting is respected', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    Setting::set('standard_working_hours_per_day', '4.00');

    $rate = otService()->hourlyRateFor($user, Carbon::parse('2026-08-25'));
    // 26000/26 = 1000/day, /4 = 250/hr
    expect($rate)->toBe(250.0);
});

test('the multiplier used is whatever the caller explicitly passes, never derived automatically from settings', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);
    // Changing the (now-unused-for-auto-lookup) global setting must have
    // zero effect on calculateForApproval()'s result.
    Setting::set('ot_multipliers', json_encode(['weekday' => 3.0, 'weekend' => 2.0, 'holiday' => 2.0], JSON_PRESERVE_ZERO_FRACTION));

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 1, 1.5);

    expect($result['rate_multiplier'])->toBe(1.5);
    expect($result['calculated_amount'])->toBe(187.5); // 125*1*1.5, NOT influenced by the settings change
});

test('1.5x multiplier calculation is exact', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 4, 1.5);
    expect($result['calculated_amount'])->toBe(750.0); // 125*4*1.5
});

test('2.0x multiplier calculation is exact', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-23'), 4, 2.0);
    expect($result['calculated_amount'])->toBe(1000.0); // 125*4*2.0
});

test('decimal precision rounds the final amount to 2 decimal places', function () {
    $user = User::factory()->create();
    giveSalary($user, 27000); // 27000/26 = 1038.4615.../day -> forces non-terminating decimals

    $result = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 3.5, 1.5);

    expect($result['calculated_amount'])->toBeFloat();
    expect(round($result['calculated_amount'], 2))->toBe($result['calculated_amount']);
});

test('salary change after the OT date does not alter a hourly-rate derived for the earlier date', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000, '2026-08-01', '2026-08-24');

    $before = otService()->calculateForApproval($user, Carbon::parse('2026-08-24'), 2, 1.5);

    giveSalary($user, 60000, '2026-08-25', null); // salary changes the next day

    $after = otService()->calculateForApproval($user, Carbon::parse('2026-08-24'), 2, 1.5); // recompute for the SAME historical date

    expect($before['calculated_amount'])->toBe($after['calculated_amount']); // unaffected by the later salary
});

test('a global settings change after an approval does not alter an already-persisted frozen snapshot', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $snapshot = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 2, 1.5);
    $frozenAmount = $snapshot['calculated_amount'];

    // Simulate persisting the snapshot, then a later global settings change.
    Setting::set('ot_multipliers', json_encode(['weekday' => 9.0, 'weekend' => 2.0, 'holiday' => 2.0], JSON_PRESERVE_ZERO_FRACTION));

    // The already-persisted value never gets recalculated — this is a
    // property of never calling calculateForApproval() again for an
    // approved record, not of the service itself.
    expect($frozenAmount)->toBe(375.0);
});

test('multiple OT records on the same date are allowed at the calculation layer', function () {
    $user = User::factory()->create();
    giveSalary($user, 26000);

    $first  = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 2, 1.5);
    $second = otService()->calculateForApproval($user, Carbon::parse('2026-08-25'), 1, 1.5);

    expect($first['calculated_amount'])->toBe(375.0);
    expect($second['calculated_amount'])->toBe(187.5);
});
