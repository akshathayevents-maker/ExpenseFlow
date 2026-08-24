<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SetEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'monthly_salary' => ['required', 'numeric', 'gt:0'],
            'effective_from' => ['required', 'date'],
        ];
    }
}
