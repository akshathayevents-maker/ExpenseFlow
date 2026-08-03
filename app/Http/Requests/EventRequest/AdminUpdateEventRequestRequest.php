<?php

namespace App\Http\Requests\EventRequest;

use App\Models\EventRequest;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateEventRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('event_request')) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_name'           => ['required', 'string', 'max:150'],
            'client_mobile'         => ['required', 'digits:10'],
            'client_email'          => ['nullable', 'email', 'max:150'],
            'event_name'            => ['nullable', 'string', 'max:150'],
            'event_date'            => ['required', 'date'],
            'meal_type'             => ['required', 'in:'.implode(',', array_keys(EventRequest::mealTypes()))],
            'guest_count'           => ['required', 'integer', 'min:1', 'max:20000'],
            'menu_type'             => ['required', 'in:'.implode(',', array_keys(EventRequest::menuTypes()))],
            'special_instructions'  => ['nullable', 'string', 'max:2000'],
            'menu_item_ids'         => ['nullable', 'array'],
            'menu_item_ids.*'       => ['integer', 'exists:event_request_menu_items,id'],
        ];
    }
}
