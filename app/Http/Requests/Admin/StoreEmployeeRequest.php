<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'phone'                 => ['nullable', 'string', 'max:20'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', Password::defaults()],
            'role'                  => ['required', 'in:admin,manager,employee'],
            'is_active'             => ['boolean'],
            'employment_start_date' => ['nullable', 'date'],
            'employment_end_date'   => ['nullable', 'date'],

            // Optional explicit leave policy template choice. Blank means
            // "use the configured default if one exists" — see
            // EmployeeController::store(). An empty string is also
            // accepted from the <select> "No leave policy" option.
            'leave_policy_template_id' => ['nullable', 'integer', 'exists:leave_policy_templates,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $start = $this->input('employment_start_date');
            $end   = $this->input('employment_end_date');

            if ($start && $end && $end < $start) {
                $v->errors()->add('employment_end_date', 'Employment end date must be on or after the employment start date.');
            }

            // employment_start_date is intentionally nullable elsewhere in
            // the app — this does NOT make it required for every employee.
            // But EmployeeLeavePolicy.effective_from must never be silently
            // invented (e.g. defaulted to today()), since that would
            // corrupt the pro-rata calculation the moment a real start date
            // is later added. So the moment a leave policy template WOULD
            // be assigned at creation — either explicitly chosen, or
            // implicitly via a configured default — a start date becomes
            // required, and the whole employee creation is rejected rather
            // than silently creating a mis-dated policy.
            if (! $start) {
                $templateId = $this->input('leave_policy_template_id');
                $willAssignTemplate = $templateId
                    ? true
                    : \App\Models\LeavePolicyTemplate::where('is_default', true)->exists();

                if ($willAssignTemplate) {
                    $v->errors()->add(
                        'employment_start_date',
                        'Employment start date is required to assign a leave policy template (explicitly selected or the configured default).',
                    );
                }
            }
        });
    }
}
