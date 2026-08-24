<?php

use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use App\Services\PayableDaysCalculator;
use Carbon\Carbon;

beforeEach(function () {
    $this->calc = new PayableDaysCalculator();
});

function markAttendance(User $user, string $date, string $status): void
{
    EmployeeAttendance::create([
        'user_id' => $user->id, 'attendance_date' => $date, 'status' => $status,
        'marked_by' => $user->id, 'marked_at' => now(), 'source' => 'self',
    ]);
}

// 1. Full month, no holidays, no weekly offs (weekly_off_days emptied for this test)
test('full month with no weekly offs and no holidays counts every calendar day as working', function () {
    Setting::set('weekly_off_days', '[]');
    $user = User::factory()->create();

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(31);
});

// 2. Sunday weekly off (default seeded config)
test('sunday weekly off is excluded from working days', function () {
    $user = User::factory()->create();
    // August 2026: Sundays are 2, 9, 16, 23, 30 = 5 Sundays
    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(31 - 5);
});

// 3. Multiple weekly offs (Sunday + Saturday)
test('multiple configured weekly off weekdays are all excluded', function () {
    Setting::set('weekly_off_days', json_encode([0, 6])); // Sunday + Saturday
    $user = User::factory()->create();

    // August 2026: 5 Sundays (2,9,16,23,30) + 4 Saturdays (1,8,15,22,29 -> actually 5: 1,8,15,22,29)
    $sundays   = collect(range(1, 31))->filter(fn ($d) => Carbon::parse("2026-08-$d")->dayOfWeek === 0)->count();
    $saturdays = collect(range(1, 31))->filter(fn ($d) => Carbon::parse("2026-08-$d")->dayOfWeek === 6)->count();

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(31 - $sundays - $saturdays);
});

// 4. One holiday
test('one active holiday is excluded from working days', function () {
    $user = User::factory()->create();
    Holiday::create(['holiday_date' => '2026-08-15', 'name' => 'Independence Day', 'is_active' => true]);

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(31 - 5 /* Sundays */ - 1 /* holiday, 15 Aug is a Saturday, not a Sunday */);
});

// 5. Holiday + weekly off on the SAME date — must not double-subtract
test('a date that is both a holiday and a weekly off is excluded exactly once', function () {
    $user = User::factory()->create();
    // 2 Aug 2026 is a Sunday (already a weekly off) — also mark it a holiday
    Holiday::create(['holiday_date' => '2026-08-02', 'name' => 'Coincidental Holiday', 'is_active' => true]);

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    // Still only 5 Sundays excluded total — the holiday on a Sunday adds nothing extra
    expect($days)->toBe(31 - 5);
});

// 6. Employee joins mid-month
test('employee joining mid-month only counts working days from the join date onward', function () {
    $user = User::factory()->create(['employment_start_date' => '2026-08-15']);

    // 15-31 Aug: Sundays in that range = 16, 23, 30 = 3
    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe((31 - 14) - 3); // 17 calendar days from 15-31, minus 3 Sundays
});

// 7. Employee leaves mid-month
test('employee leaving mid-month only counts working days up to the leave date', function () {
    $user = User::factory()->create(['employment_end_date' => '2026-08-20']);

    // 1-20 Aug: Sundays = 2, 9, 16 = 3
    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(20 - 3);
});

// 8. Employee joins and leaves within the same month
test('employee joining and leaving within the same requested range is fully clamped both sides', function () {
    $user = User::factory()->create(['employment_start_date' => '2026-08-10', 'employment_end_date' => '2026-08-20']);

    // 10-20 Aug: Sundays = 16 = 1
    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(11 - 1); // 11 calendar days (10..20 inclusive), minus 1 Sunday
});

// 9. Full-day present
test('a full present day contributes exactly 1.0 payable day', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-03', 'present'); // Monday, a working day

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-03'));

    expect($payable)->toBe(1.0);
});

// 10. Half-day attendance
test('a half day contributes exactly 0.5 payable day', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-03', 'half_day');

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-03'));

    expect($payable)->toBe(0.5);
});

// 11. Approved (paid) leave — per the locked business decision, all approved leave is payable
test('approved full-day leave contributes 1.0 payable day (all approved leave is payable per locked decision)', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-03', 'leave'); // as written by leave approval

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-03'));

    expect($payable)->toBe(1.0);
});

// 12. Approved unpaid leave — SCHEMA GAP, documented rather than invented
test('the schema has no paid/unpaid leave distinction — this test documents that gap rather than assuming a behaviour', function () {
    expect(Schema::hasColumn('leave_types', 'is_paid'))->toBeFalse();
    expect(Schema::hasColumn('employee_leave_policies', 'is_paid'))->toBeFalse();
    // If this distinction is introduced later, payableDaysSoFar() is the
    // one method that must change — see its docblock.
});

// 13. Leave + attendance interaction (half_day_leave)
test('half_day_leave status contributes 0.5, matching half_day exactly', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-03', 'half_day_leave');

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-03'));

    expect($payable)->toBe(0.5);
});

// 14. Holiday + leave on the same date — holiday wins, contributes 0 regardless of the attendance row
test('a holiday date contributes 0 payable days even if an attendance/leave row exists for it', function () {
    $user = User::factory()->create();
    Holiday::create(['holiday_date' => '2026-08-03', 'name' => 'Surprise Holiday', 'is_active' => true]);
    markAttendance($user, '2026-08-03', 'leave'); // e.g. a stale/corrected row

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-03'));

    expect($payable)->toBe(0.0);
});

// 15. Weekly off + leave on the same date — weekly-off wins, contributes 0
test('a weekly-off date contributes 0 payable days even if an attendance/leave row exists for it', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-02', 'leave'); // 2 Aug 2026 is a Sunday

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-02'));

    expect($payable)->toBe(0.0);
});

// 16. Current month calculation up to "today" (caller passes the range — service itself never calls now())
test('service has no implicit "today" — caller controls the end of the range entirely', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-01', 'present');
    markAttendance($user, '2026-08-24', 'present'); // outside the requested range below

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-01'));

    expect($payable)->toBe(1.0); // the 24 Aug row must NOT be included
});

// 17. Historical month calculation
test('a fully historical month range calculates identically to a current one', function () {
    $user = User::factory()->create();
    markAttendance($user, '2020-01-01', 'present'); // Wednesday

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2020-01-01'), Carbon::parse('2020-01-01'));
    $working = $this->calc->applicableWorkingDays($user, Carbon::parse('2020-01-01'), Carbon::parse('2020-01-01'));

    expect($payable)->toBe(1.0);
    expect($working)->toBe(1);
});

// 18. Empty/invalid date range
test('a $to date before $from throws instead of silently returning zero', function () {
    $user = User::factory()->create();

    expect(fn () => $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-10'), Carbon::parse('2026-08-01')))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-10'), Carbon::parse('2026-08-01')))
        ->toThrow(InvalidArgumentException::class);
});

test('a single-day range (from == to) is valid and counts that one day', function () {
    $user = User::factory()->create();
    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-03'), Carbon::parse('2026-08-03'));

    expect($days)->toBe(1);
});

// 19. NULL employment_start_date — treated as always employed
test('a null employment_start_date is treated as always employed (no lower clamp)', function () {
    $user = User::factory()->create(['employment_start_date' => null, 'employment_end_date' => null]);

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-01'), Carbon::parse('2026-08-31'));

    expect($days)->toBe(31 - 5); // identical to the "full month" case — no clamping applied
});

// 20. Multiple holiday records on the same date collapse to one exclusion
test('duplicate holiday rows for the same date still only exclude that date once', function () {
    $user = User::factory()->create();
    Holiday::create(['holiday_date' => '2026-08-15', 'name' => 'Holiday A', 'is_active' => true]);
    // A second, differently-named holiday row somehow exists for the same date.
    DB::table('holidays')->insert([
        'holiday_date' => '2026-08-15', 'name' => 'Holiday B (duplicate)', 'is_active' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-15'), Carbon::parse('2026-08-15'));

    expect($days)->toBe(0); // excluded once, not "twice" (there's only one day to exclude anyway —
                            // this proves the dedup logic doesn't error/double-count internally)
});

// ── Extra: weekly-off numbering convention, verified rather than assumed ──
test('weekly_off_days uses Carbon dayOfWeek numbering (0=Sunday..6=Saturday), matching the seeded default', function () {
    expect(Carbon::parse('2026-08-02')->dayOfWeek)->toBe(0); // 2 Aug 2026 is a Sunday
    expect(Setting::get('weekly_off_days'))->toBe([0]);
});

// ── Extra: inactive holiday must NOT exclude a date ───────────────────────
test('an inactive holiday does not exclude the date', function () {
    $user = User::factory()->create();
    Holiday::create(['holiday_date' => '2026-08-04', 'name' => 'Cancelled Holiday', 'is_active' => false]); // Tuesday

    $days = $this->calc->applicableWorkingDays($user, Carbon::parse('2026-08-04'), Carbon::parse('2026-08-04'));

    expect($days)->toBe(1); // still counted as a working day
});

// ── Extra: absent and unmarked both contribute zero, distinctly from each other in intent ──
test('absent and unmarked (no row) both contribute 0 payable days', function () {
    $user = User::factory()->create();
    markAttendance($user, '2026-08-03', 'absent'); // Monday
    // 2026-08-04 (Tuesday) has no attendance row at all — unmarked

    $payable = $this->calc->payableDaysSoFar($user, Carbon::parse('2026-08-03'), Carbon::parse('2026-08-04'));

    expect($payable)->toBe(0.0);
});
