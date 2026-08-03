<?php

namespace App\Http\Requests\EventRequest;

use App\Models\EventRequest;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Admin creates the initial shell — usually right after a phone call.
 * Everything is optional here; the client fills in the rest via the
 * public link.
 */
class CreateEventRequestShellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EventRequest::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'client_name'   => ['nullable', 'string', 'max:150'],
            'client_mobile' => ['nullable', 'digits:10'],
            'client_email'  => ['nullable', 'email', 'max:150'],
            'event_name'    => ['nullable', 'string', 'max:150'],
            'event_date'    => ['nullable', 'date'],
            'meal_type'     => ['nullable', 'in:'.implode(',', array_keys(EventRequest::mealTypes()))],
            'guest_count'   => ['nullable', 'integer', 'min:1'],
            'menu_type'     => ['nullable', 'in:'.implode(',', array_keys(EventRequest::menuTypes()))],
        ];
    }
}
