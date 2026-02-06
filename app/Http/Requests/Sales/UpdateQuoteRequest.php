<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['nullable', 'string', 'in:send,accept,reject'],

            'payment_term_id' => ['nullable', 'integer'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],

            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'tax_group_id' => ['nullable', 'integer'],
            'global_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'global_discount_amount' => ['nullable', 'numeric', 'min:0'],

            'currency' => ['required_without:action', 'string', 'max:10'],

            'items' => ['required_without:action', 'array', 'min:1'],
            'items.*.type' => ['required_with:items', 'in:variant,custom'],
            'items.*.variant_id' => ['nullable', 'string'],
            'items.*.custom_name' => ['nullable', 'string', 'max:255'],
            'items.*.custom_sku' => ['nullable', 'string', 'max:255'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_group_id' => ['nullable', 'integer'],
        ];
    }
}
