<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuoteCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_to' => ['required', 'string', 'exists:customers,uuid'],
            'ship_to' => ['nullable', 'string', 'exists:customers,uuid'],
        ];
    }
}
