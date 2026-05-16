<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()->isAdmin(); }

    public function rules(): array
    {
        $id = $this->route('category')->id;

        return [
            'name'        => ['required', 'string', 'max:100', "unique:expense_categories,name,{$id}"],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ];
    }
}
