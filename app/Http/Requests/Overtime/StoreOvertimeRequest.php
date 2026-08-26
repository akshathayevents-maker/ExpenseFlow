<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()->is_active;
    }

    /**
     * The Hours field is submitted as separate hours_h (whole hours) +
     * hours_m (minutes, 0/15/30/45) inputs — a plain
     * <input type="number" step="0.25"> produced a confusing native
     * "nearest valid values are 0.76 and 1.01" browser error whenever the
     * step (0.25) didn't line up with min (0.01). Converting to a decimal
     * `hours` value here, before validation runs, keeps the stored/validated
     * value and the calculation service completely unchanged — only the
     * input UX differs. 15-minute granularity (0.25h) matches the
     * employee_overtime.hours decimal(4,2) column and the existing
     * OvertimeCalculationServiceTest coverage of quarter-hour values (e.g.
     * 1.25h), which a coarser half-hour-only input would have been unable
     * to express.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('hours_h') || $this->filled('hours_m')) {
            $h = (float) $this->input('hours_h', 0);
            $m = (float) $this->input('hours_m', 0);

            $this->merge([
                'hours' => round($h + ($m / 60), 2),
            ]);
        }
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
            // Overtime reason is REQUIRED (unlike Leave/Regularization, which
            // stay optional) — this is an intentional, OT-specific rule.
            'reason'  => ['required', 'string', 'max:1000'],
        ];
    }

    // category/hourly_rate_snapshot/rate_multiplier/calculated_amount are
    // deliberately absent from rules() — validated() only ever returns the
    // three keys above, so even if an employee posts those fields, they are
    // silently dropped, never reaching the service/model.
}
