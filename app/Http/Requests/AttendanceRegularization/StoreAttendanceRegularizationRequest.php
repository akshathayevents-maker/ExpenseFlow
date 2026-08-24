<?php

namespace App\Http\Requests\AttendanceRegularization;

use App\Models\EmployeeAttendanceRegularization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRegularizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'attendance_date'   => ['required', 'date', 'before_or_equal:today'],
            'requested_status'  => ['required', 'string', Rule::in(EmployeeAttendanceRegularization::requestableStatuses())],
            'reason'            => ['nullable', 'string', 'max:1000'],
        ];
    }
}
