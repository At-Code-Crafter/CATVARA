<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            'payment_term_id' => [
                'nullable',
                'integer',
                Rule::exists('payment_terms', 'id')->where('company_id', active_company_id()),
            ],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'tax_group_id' => [
                'nullable',
                'integer',
                Rule::exists('tax_groups', 'id')->where('company_id', active_company_id()),
            ],
            'global_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'global_discount_amount' => ['nullable', 'numeric', 'min:0'],

            'currency' => ['required', 'string'],

            'items' => ['nullable', 'array'],
            'items.*.type' => ['nullable', 'string', 'in:variant,custom'],

            // variant UUID (NOT integer)
            'items.*.variant_id' => [
                'nullable',
                'string',
                Rule::exists('product_variants', 'uuid')->where('company_id', active_company_id()),
            ],

            'items.*.custom_name' => ['nullable', 'string'],
            'items.*.custom_sku' => ['nullable', 'string'],

            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],

            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_group_id' => [
                'nullable',
                'integer',
                Rule::exists('tax_groups', 'id')->where('company_id', active_company_id()),
            ],
        ];
    }
}
