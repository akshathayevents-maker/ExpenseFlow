<?php

namespace App\Http\Requests\AttendanceRegularization;

use Illuminate\Foundation\Http\FormRequest;

class RejectAttendanceRegularizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'review_note' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
