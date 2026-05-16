<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InventoryTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'      => 'required|in:purchase,usage,adjustment,wastage,transfer',
            'quantity'  => 'required|numeric|min:0.001',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes'     => 'nullable|string|max:500',
        ];
    }
}
