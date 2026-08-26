<?php

namespace App\Http\Requests\Overtime;

use App\Models\EmployeeOvertimeConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            // Required — the approver must explicitly choose a multiplier;
            // no automatic date-category lookup happens anymore. Membership
            // in the employee's configured allowed multipliers is checked
            // in withValidator() below, since it depends on the route-bound
            // {overtime} model, not a static rule.
            'multiplier'    => ['required', 'numeric', 'gt:0'],
            // Optional manual override. "Leave blank to use the calculated
            // amount" — when present it must be a strictly positive amount
            // with at most 2 decimal places.
            'manual_amount' => ['nullable', 'numeric', 'gt:0', 'decimal:0,2'],
            'review_note'   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $overtime = $this->route('overtime');
            if ($overtime === null || $this->multiplier === null) {
                return;
            }

            $allowed = EmployeeOvertimeConfig::allowedMultipliersFor($overtime->user);
            $submitted = round((float) $this->multiplier, 2);

            $matches = array_filter($allowed, fn ($m) => abs(round((float) $m, 2) - $submitted) < 0.001);

            if (empty($matches)) {
                $validator->errors()->add(
                    'multiplier',
                    'The selected multiplier is not configured for this employee.',
                );
            }
        });
    }
}
