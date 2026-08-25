<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ManualLeaveAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            // Amount is signed — a positive credit or a negative deduction —
            // 0 is rejected by LeaveAllocationService::manualAdjustment() itself.
            'amount'        => ['required', 'numeric', 'not_in:0'],
            'reason'        => ['required', 'string', 'max:1000'],
        ];
    }
}
