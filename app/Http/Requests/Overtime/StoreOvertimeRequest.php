<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            // No future-dated employee OT — no existing business rule allows
            // it, so we default to the conservative today-or-past bound.
            'ot_date' => ['required', 'date', 'before_or_equal:today'],
            // gt:0 + max matches the employee_overtime.hours decimal(4,2)
            // column capacity — not an invented business maximum.
            'hours'   => ['required', 'numeric', 'gt:0', 'max:99.99'],
            'reason'  => ['required', 'string', 'max:1000'],
        ];
    }

    // category/hourly_rate_snapshot/rate_multiplier/calculated_amount are
    // deliberately absent from rules() — validated() only ever returns the
    // three keys above, so even if an employee posts those fields, they are
    // silently dropped, never reaching the service/model.
}
