<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'code'                 => ['required', 'string', 'max:50', Rule::unique('leave_types', 'code')->ignore($this->route('leaveType'))],
            'is_active'            => ['nullable', 'boolean'],
            'allow_half_day'       => ['nullable', 'boolean'],
            'is_paid'              => ['nullable', 'boolean'],
            'allow_carry_forward'  => ['nullable', 'boolean'],
            'max_carry_forward'    => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
