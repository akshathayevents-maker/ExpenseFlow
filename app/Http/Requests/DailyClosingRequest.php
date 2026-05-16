<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyClosingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // On update, ignore the record being edited in the unique check.
        $closingId = optional($this->route('dailyClosing'))->id;

        return [
            'date'   => [
                'required',
                'date',
                'before_or_equal:today',
                Rule::unique('daily_closings', 'date')->ignore($closingId),
            ],
            'notes'  => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'in:draft,verified,closed'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.before_or_equal' => 'Cannot create a closing for a future date.',
            'date.unique'          => 'A closing already exists for this date.',
        ];
    }
}
