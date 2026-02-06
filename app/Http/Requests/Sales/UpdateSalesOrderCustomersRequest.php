<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesOrderCustomersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_to' => [
                'required',
                Rule::exists('customers', 'uuid')->where('company_id', active_company_id()),
            ],
            'ship_to' => [
                'nullable',
                Rule::exists('customers', 'uuid')->where('company_id', active_company_id()),
            ],
        ];
    }
}
