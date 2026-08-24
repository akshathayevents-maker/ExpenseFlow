<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

// LeaveRequest::status/reviewed_by/reviewed_at/review_note are server-only
// and excluded from $fillable — fixtures across multiple test files need to
// set them directly (e.g. a pre-approved leave request), so this shared
// helper does the fill()+forceFill()+save() split once instead of repeating
// it at every call site.
function hardenedLeaveRequest(array $attrs): \App\Models\LeaveRequest
{
    $fillableKeys = ['user_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day', 'half_day_period', 'days_requested', 'reason'];
    $leaveRequest = new \App\Models\LeaveRequest();
    $leaveRequest->fill(array_intersect_key($attrs, array_flip($fillableKeys)));
    $leaveRequest->forceFill(array_diff_key($attrs, array_flip($fillableKeys)));
    $leaveRequest->save();

    return $leaveRequest;
}
