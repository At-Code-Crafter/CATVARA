<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOrderCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_to' => 'required|exists:customers,uuid',
            'ship_to' => 'nullable|exists:customers,uuid',
        ];
    }
}
