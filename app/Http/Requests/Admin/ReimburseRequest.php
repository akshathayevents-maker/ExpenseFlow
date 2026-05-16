<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReimburseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'payment_mode'          => ['required', 'in:cash,upi,bank_transfer'],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'payment_notes'         => ['nullable', 'string', 'max:500'],
            'paid_at'               => ['nullable', 'date'],
        ];
    }
}
