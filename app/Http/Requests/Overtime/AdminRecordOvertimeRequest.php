<?php

namespace App\Http\Requests\Overtime;

use App\Models\EmployeeOvertimeConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminRecordOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['required', 'integer', 'exists:users,id'],
            'ot_date'       => ['required', 'date', 'before_or_equal:today'],
            // No artificial precision cap beyond the column's own
            // decimal(4,2) — any decimal value (1.5, 2.25, 11.76, 12.01) is
            // accepted; the HTML input has no `step` restriction either
            // (see resources/views/admin/overtime/create.blade.php).
            'hours'         => ['required', 'numeric', 'gt:0', 'max:99.99'],
            'reason'        => ['required', 'string', 'max:1000'],
            // Combined record+approve fields — required because the admin
            // "Record Overtime" screen now always records AND approves in
            // one action (see OvertimeService::recordAndApprove()).
            'multiplier'    => ['required', 'numeric', 'gt:0'],
            'manual_amount' => ['nullable', 'numeric', 'gt:0', 'decimal:0,2'],
            'review_note'   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $userId = $this->input('user_id');
            if (! $userId || $this->multiplier === null) {
                return;
            }

            $employee = \App\Models\User::find($userId);
            if ($employee === null) {
                return;
            }

            $allowed = EmployeeOvertimeConfig::allowedMultipliersFor($employee);
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
