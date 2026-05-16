<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:150'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'address'   => ['nullable', 'string', 'max:500'],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }
}
