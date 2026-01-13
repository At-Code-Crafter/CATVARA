<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class ApplyPaymentRequest extends FormRequest
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
            'paymentable_type' => 'required|in:order,invoice',
            'paymentable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01|max:999999999.999999',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'paymentable_type' => 'document type',
            'paymentable_id' => 'document',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'paymentable_type.required' => 'Please select a document type.',
            'paymentable_id.required' => 'Please select a document to apply payment to.',
            'amount.min' => 'The application amount must be at least 0.01.',
        ];
    }
}
