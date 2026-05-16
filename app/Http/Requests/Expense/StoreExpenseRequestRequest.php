<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'title'  => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'notes'  => ['nullable', 'string', 'max:2000'],
            'qr'     => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'qr.required' => 'Please upload a payment QR image.',
            'qr.mimes'    => 'QR must be a JPG, PNG, or PDF file.',
            'qr.max'      => 'QR file must be under 10MB.',
        ];
    }
}
