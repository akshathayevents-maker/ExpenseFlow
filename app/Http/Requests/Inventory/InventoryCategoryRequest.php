<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InventoryCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $unique = 'unique:inventory_categories,name';
        if ($this->route('category')) {
            $unique .= ',' . $this->route('category')->id;
        }

        return [
            'name'        => ['required', 'string', 'max:255', $unique],
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ];
    }
}
