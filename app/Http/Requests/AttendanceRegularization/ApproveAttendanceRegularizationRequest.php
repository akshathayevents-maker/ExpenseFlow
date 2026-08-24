<?php

namespace App\Http\Requests\AttendanceRegularization;

use Illuminate\Foundation\Http\FormRequest;

class ApproveAttendanceRegularizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'review_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
