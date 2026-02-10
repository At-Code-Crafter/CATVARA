<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinalizeSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency' => ['required', 'string', 'max:10'],
            'payment_term_id' => [
                'nullable',
                Rule::exists('payment_terms', 'id')->where('company_id', active_company_id()),
            ],
            'due_date' => ['nullable', 'date'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'tax_group_id' => [
                'nullable',
                'integer',
                Rule::exists('tax_groups', 'id')->where('company_id', active_company_id()),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],

            'items.*.type' => ['required', 'in:variant,custom'],
            'items.*.variant_id' => [
                'nullable',
                'string',
                Rule::exists('product_variants', 'uuid')->where('company_id', active_company_id()),
            ],
            'items.*.custom_name' => ['nullable', 'string', 'max:255'],
            'items.*.custom_sku' => ['nullable', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_group_id' => [
                'nullable',
                'integer',
                Rule::exists('tax_groups', 'id')->where('company_id', active_company_id()),
            ],
        ];
    }
}
