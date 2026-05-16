<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'                       => 'required|string|max:255',
            'planned_date'                => 'required|date',
            'notes'                       => 'nullable|string|max:1000',
            'items'                       => 'required|array|min:1',
            'items.*.selected'            => 'sometimes|boolean',
            'items.*.inventory_item_id'   => 'required|exists:inventory_items,id',
            'items.*.quantity'            => 'required|numeric|min:0.001',
            'items.*.unit_cost'           => 'nullable|numeric|min:0',
            'items.*.priority'            => 'required|in:urgent,high,normal,low',
            'items.*.notes'               => 'nullable|string|max:255',
        ];
    }
}
