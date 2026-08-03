<?php

namespace App\Http\Requests\EventRequest;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageMenu', \App\Models\EventRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id'          => ['required', 'exists:event_request_menu_categories,id'],
            'name'                 => ['required', 'string', 'max:150'],
            'description'          => ['nullable', 'string', 'max:500'],
            'is_veg'               => ['boolean'],
            'price_per_person'     => ['required', 'numeric', 'min:0'],
            'image_path'           => ['nullable', 'string', 'max:255'],
            'is_popular'           => ['boolean'],
            'is_chef_recommended'  => ['boolean'],
            'display_order'        => ['nullable', 'integer', 'min:0'],
            'is_active'            => ['boolean'],
        ];
    }
}
