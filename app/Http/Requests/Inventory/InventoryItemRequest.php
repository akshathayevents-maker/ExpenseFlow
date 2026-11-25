<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name_ta'               => 'nullable|string|max:200',
            'confirm_duplicate'     => 'nullable|boolean',
            'sku'                   => ['nullable', 'string', 'max:100', $unique],
            'inventory_category_id' => 'required|exists:inventory_categories,id',
            'unit' => ['required', Rule::in(array_keys(config('inventory.units')))],
            'minimum_stock'         => 'required|numeric|min:0',
            'maximum_stock'         => 'nullable|numeric|min:0',
            'average_cost'          => 'nullable|numeric|min:0',
            'description'           => 'nullable|string|max:1000',
            'status'                => 'required|in:active,inactive',
        ];
    }

    /** Identical English or Tamil name to another item is never allowed (one stock record per item). */
    public function after(): array
    {
        return [function ($validator) {
            $id = $this->route('item')?->id;
            $q = \App\Models\InventoryItem::query()->when($id, fn ($q) => $q->where('id', '!=', $id));
            $name = trim((string) $this->input('name'));
            if ($name !== '' && (clone $q)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
                $validator->errors()->add('name', 'An item with this name already exists.');
            }
            $ta = trim((string) $this->input('name_ta'));
            if ($ta !== '' && (clone $q)->where('name_ta', $ta)->exists()) {
                $validator->errors()->add('name_ta', 'Another item already has this Tamil name.');
            }
        }];
    }
}
