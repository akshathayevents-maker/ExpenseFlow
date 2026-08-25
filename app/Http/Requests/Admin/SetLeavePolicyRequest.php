<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SetLeavePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'leave_type_id'          => ['required', 'integer', 'exists:leave_types,id'],
            'annual_entitlement'     => ['required', 'numeric', 'min:0'],
            'allocation_mode'        => ['required', 'in:yearly,monthly_accrual,quarterly_accrual'],
            'monthly_accrual_amount' => ['required_unless:allocation_mode,yearly', 'nullable', 'numeric', 'min:0'],
            'effective_from'         => ['required', 'date'],
        ];
    }
}
