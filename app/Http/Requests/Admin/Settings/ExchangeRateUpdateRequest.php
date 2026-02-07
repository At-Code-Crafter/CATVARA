<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeRateUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_currency_id' => 'required|exists:currencies,id',
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'rate' => 'required|numeric|min:0.00000001',
            'effective_date' => 'nullable|date',
            'source' => 'nullable|string|max:100',
        ];
    }
}
