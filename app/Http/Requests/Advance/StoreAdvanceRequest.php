<?php

namespace App\Http\Requests\Advance;

use App\Services\AdvanceEligibilityService;
use App\Services\EmployeeAttendanceService;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_active;
    }

    /**
     * Eligibility is recalculated server-side from the authenticated user
     * only — the browser never supplies salary/eligible-amount as trusted
     * input, only the requested_amount itself.
     */
    public function rules(AdvanceEligibilityService $eligibilityService, EmployeeAttendanceService $attendanceService): array
    {
        $eligibility = $eligibilityService->evaluate($this->user(), $attendanceService->today());

        return [
            'requested_amount' => [
                'required', 'numeric', 'gt:0', 'max:9999999.99',
                function ($attribute, $value, $fail) use ($eligibility) {
                    if (! $eligibility['salary_configured']) {
                        $fail('Advance requests are unavailable because your salary has not been configured.');

                        return;
                    }

                    if ((float) $value > $eligibility['eligible_advance_amount']) {
                        $fail('Requested amount exceeds your eligible advance of ₹'
                            . number_format($eligibility['eligible_advance_amount'], 2) . '.');
                    }
                },
            ],
            // No business requirement (schema or otherwise) mandates a
            // reason for an advance request — kept optional, matching
            // employee_advances.notes being a nullable free-text column.
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
