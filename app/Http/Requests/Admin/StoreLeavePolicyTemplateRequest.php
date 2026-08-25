<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeavePolicyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_default'  => ['nullable', 'boolean'],
            'is_active'   => ['nullable', 'boolean'],

            'items'                            => ['required', 'array', 'min:1'],
            'items.*.leave_type_id'            => ['required', 'integer', 'exists:leave_types,id', 'distinct'],
            'items.*.annual_entitlement'       => ['required', 'numeric', 'min:0'],
            'items.*.allocation_mode'          => ['required', 'in:yearly,monthly_accrual,quarterly_accrual'],
            'items.*.monthly_accrual_amount'   => ['required_unless:items.*.allocation_mode,yearly', 'nullable', 'numeric', 'min:0'],
        ];
    }
}
