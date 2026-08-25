<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignLeavePolicyTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'leave_policy_template_id' => ['required', 'integer', 'exists:leave_policy_templates,id'],
            'effective_from'           => ['required', 'date'],
            'employee_ids'             => ['required', 'array', 'min:1'],
            'employee_ids.*'           => ['integer', 'exists:users,id'],
        ];
    }
}
