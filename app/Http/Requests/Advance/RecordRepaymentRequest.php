<?php

namespace App\Http\Requests\Advance;

use Illuminate\Foundation\Http\FormRequest;

class RecordRepaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isManager();
    }

    public function rules(): array
    {
        return [
            'amount'    => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
