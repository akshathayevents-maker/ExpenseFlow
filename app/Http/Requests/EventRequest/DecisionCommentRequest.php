<?php

namespace App\Http\Requests\EventRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared shape for "Need Changes" and "Reject" — both require a comment
 * explaining the decision to the client.
 */
class DecisionCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('decide', $this->route('event_request')) ?? false;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:2000'],
        ];
    }
}
