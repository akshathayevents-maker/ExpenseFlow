<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id'           => ['nullable', 'exists:vendors,id'],
            'amount'              => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'priority'            => ['required', 'in:low,medium,high,urgent'],
            'notes'               => ['nullable', 'string', 'max:2000'],
            'bills'               => ['nullable', 'array', 'max:5'],
            'bills.*'             => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'bills.*.max'   => 'Each bill must be under 5MB.',
            'bills.*.mimes' => 'Bills must be JPG, PNG, or PDF.',
        ];
    }
}
