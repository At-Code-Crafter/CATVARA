<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:payment_methods,code|alpha_dash:ascii',
            'name' => 'required|string|max:255',
            'type' => 'required|in:CASH,CARD,GATEWAY,BANK,WALLET,CREDIT',
            'is_active' => 'boolean',
            'allow_refund' => 'boolean',
            'requires_reference' => 'boolean',
        ];
    }
}
