<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01|max:999999999.999999',
            'customer_id' => 'nullable|exists:customers,id',
            'paid_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exchange_rate' => 'nullable|numeric|min:0.00000001|max:999999.99999999',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'payment_method_id' => 'payment method',
            'currency_id' => 'currency',
            'customer_id' => 'customer',
            'paid_at' => 'payment date',
        ];
    }
}
