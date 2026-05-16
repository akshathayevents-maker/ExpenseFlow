<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', Password::defaults()],
            'role'      => ['required', 'in:admin,manager,employee'],
            'is_active' => ['boolean'],
        ];
    }
}
