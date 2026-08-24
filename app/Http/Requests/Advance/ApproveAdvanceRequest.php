<?php

namespace App\Http\Requests\Advance;

use Illuminate\Foundation\Http\FormRequest;

class ApproveAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'approved_amount' => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
        ];
    }
}
