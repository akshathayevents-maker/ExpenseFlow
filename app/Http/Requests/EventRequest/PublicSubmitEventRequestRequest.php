<?php

namespace App\Http\Requests\EventRequest;

use App\Models\EventRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The client's Step 1 + Step 2 submission. No auth — access is gated by the
 * token resolution happening before this request is even built (route model
 * binding via the public controller).
 */
class PublicSubmitEventRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name'           => ['required', 'string', 'max:150'],
            'client_mobile'         => ['required', 'digits:10'],
            'client_email'          => ['nullable', 'email', 'max:150'],
            'event_name'            => ['nullable', 'string', 'max:150'],
            'event_date'            => ['required', 'date', 'after_or_equal:today'],
            'meal_type'             => ['required', 'in:'.implode(',', array_keys(EventRequest::mealTypes()))],
            'guest_count'           => ['required', 'integer', 'min:1', 'max:20000'],
            'menu_type'             => ['required', 'in:'.implode(',', array_keys(EventRequest::menuTypes()))],
            'special_instructions'  => ['nullable', 'string', 'max:2000'],
            'menu_item_ids'         => ['required', 'array', 'min:1'],
            'menu_item_ids.*'       => ['integer', 'exists:event_request_menu_items,id'],
        ];
    }
}
