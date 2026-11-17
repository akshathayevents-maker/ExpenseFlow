<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InventoryTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Purchases enter stock only through goods receipt; issues only through
            // material requests. This form is for counts, corrections and store wastage.
            'type' => 'required|in:adjustment,correction,wastage',
            'direction' => 'required_if:type,correction|nullable|in:in,out',
            'quantity' => 'required|numeric|min:0|max:9999999',
            'notes' => 'required|string|min:3|max:500',
        ];
    }
}
