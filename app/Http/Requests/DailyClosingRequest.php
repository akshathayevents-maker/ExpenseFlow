<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyClosingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date'  => 'required|date|unique:daily_closings,date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
