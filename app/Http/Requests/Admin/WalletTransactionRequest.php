<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WalletTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $type = $this->input('type', 'credit');

        $rules = [
            'type'   => ['required', 'in:credit,debit,adjustment'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ];

        if ($type === 'adjustment') {
            $rules['amount'] = ['required', 'numeric', 'min:0', 'max:9999999'];
        }

        return $rules;
    }
}
