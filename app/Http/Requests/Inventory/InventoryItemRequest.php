<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InventoryItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $unique = 'unique:inventory_items,sku';
        if ($this->route('item')) {
            $unique .= ',' . $this->route('item')->id;
        }

        return [
            'name'                  => 'required|string|max:255',
            'sku'                   => ['nullable', 'string', 'max:100', $unique],
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'unit'                  => 'required|in:kg,gram,litre,ml,packet,piece,box,bundle,cylinder,dozen',
            'minimum_stock'         => 'required|numeric|min:0',
            'maximum_stock'         => 'nullable|numeric|min:0',
            'average_cost'          => 'nullable|numeric|min:0',
            'description'           => 'nullable|string|max:1000',
            'status'                => 'required|in:active,inactive',
        ];
    }
}
