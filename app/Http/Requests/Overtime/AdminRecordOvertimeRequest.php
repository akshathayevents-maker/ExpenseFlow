<?php

namespace App\Http\Requests\Overtime;

use Illuminate\Foundation\Http\FormRequest;

class AdminRecordOvertimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'ot_date' => ['required', 'date', 'before_or_equal:today'],
            'hours'   => ['required', 'numeric', 'gt:0', 'max:99.99'],
            'reason'  => ['required', 'string', 'max:1000'],
        ];
    }
}
