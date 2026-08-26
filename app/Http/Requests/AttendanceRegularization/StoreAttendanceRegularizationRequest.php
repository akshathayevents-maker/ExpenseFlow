<?php

namespace App\Http\Requests\AttendanceRegularization;

use App\Models\EmployeeAttendanceRegularization;
use App\Services\EmployeeAttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRegularizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_active;
    }

    /**
     * Laravel's built-in `before_or_equal:today` resolves "today" using the
     * app's default timezone (UTC), not the business timezone
     * (EmployeeAttendanceService::BUSINESS_TIMEZONE / ->today()). That made
     * legitimate same-day submissions near the UTC/IST day boundary get
     * rejected here even though the service layer already treats them as
     * valid. Compare plain calendar-date strings against the service's
     * business "today" instead of relying on the timezone-naive keyword.
     */
    public function rules(EmployeeAttendanceService $attendanceService): array
    {
        $businessToday = $attendanceService->today()->toDateString();

        return [
            'attendance_date'   => ['required', 'date', function ($attribute, $value, $fail) use ($businessToday) {
                if (Carbon::parse($value)->toDateString() > $businessToday) {
                    $fail('The attendance date cannot be in the future.');
                }
            }],
            'requested_status'  => ['required', 'string', Rule::in(EmployeeAttendanceRegularization::requestableStatuses())],
            'half_day_period'   => ['required_if:requested_status,half_day', 'nullable', 'in:first_half,second_half'],
            'reason'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
