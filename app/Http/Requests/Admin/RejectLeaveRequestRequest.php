<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RejectLeaveRequestRequest extends FormRequest
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
