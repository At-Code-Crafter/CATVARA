<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentMethod = $this->route('id') ?? $this->route('payment_method');
        $paymentMethodId = is_object($paymentMethod) ? $paymentMethod->id : $paymentMethod;

        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash:ascii', Rule::unique('payment_methods')->ignore($paymentMethodId)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:CASH,CARD,GATEWAY,BANK,WALLET,CREDIT',
            'is_active' => 'boolean',
            'allow_refund' => 'boolean',
            'requires_reference' => 'boolean',
        ];
    }
}
