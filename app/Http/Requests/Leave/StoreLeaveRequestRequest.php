<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

// Deliberately NO before_or_equal/after_or_equal:today restriction on
// start_date. Overtime and Attendance Regularization enforce
// before_or_equal:today because they correct/log events that already
// happened; a Leave request is the opposite — a forward-looking request for
// planned time off. Nothing in the leave_requests schema, migrations, or
// existing services restricts leave dates in either direction, so no
// past/future rule is invented here.
class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'leave_type_id'   => ['required', 'integer', 'exists:leave_types,id'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day'     => ['nullable', 'boolean'],
            'half_day_period' => ['required_if:is_half_day,1', 'nullable', 'in:first_half,second_half'],
            'reason'          => ['required', 'string', 'max:1000'],
        ];
    }
}
