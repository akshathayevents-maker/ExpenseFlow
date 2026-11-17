<?php

namespace App\Http\Requests\Inventory;

use App\Models\Purchase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $purchase = $this->route('purchase');

        return $purchase ? $this->user()->can('update', $purchase) : $this->user()->can('create', Purchase::class);
    }

    public function rules(): array
    {
        $max = (int) config('inventory.max_lines', 100);

        return [
            'purchase_date' => ['required', 'date', 'before_or_equal:'.now()->addDay()->toDateString()],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'supplier_name' => ['nullable', 'string', 'max:150', 'required_without:vendor_id'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'supplier_reference' => ['nullable', 'string', 'max:100'],
            'payment_mode' => ['nullable', Rule::in(array_keys(Purchase::PAYMENT_MODES))],
            'amount_paid' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'bill_id' => [$this->user()->isAdmin() ? 'nullable' : 'prohibited', 'integer', 'exists:inventory_bill_uploads,id'],
            'client_token' => ['nullable', 'uuid'],
            'action' => ['nullable', 'in:draft,submit'],
            'lines' => ['required', 'array', 'min:1', "max:{$max}"],
            'lines.*.inventory_item_id' => ['nullable', 'integer'],
            'lines.*.description' => ['nullable', 'string', 'max:200'],
            'lines.*.quantity' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'lines.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.remember_alias' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['supplier_name.required_without' => 'Choose a supplier or type the shop name.'];
    }
}
