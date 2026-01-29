<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['nullable', 'string'],

            'payment_term_id' => ['nullable', 'integer'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'currency' => ['required', 'string'],

            'items' => ['nullable', 'array'],
            'items.*.type' => ['nullable', 'string', 'in:variant,custom'],

            // variant UUID (NOT integer)
            'items.*.variant_id' => ['nullable', 'string'],

            'items.*.custom_name' => ['nullable', 'string'],
            'items.*.custom_sku' => ['nullable', 'string'],

            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],

            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
