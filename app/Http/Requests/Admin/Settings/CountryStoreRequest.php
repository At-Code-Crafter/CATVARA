<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class CountryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'iso_code_2' => 'required|string|size:2|unique:countries,iso_code_2',
            'iso_code_3' => 'required|string|size:3|unique:countries,iso_code_3',
            'numeric_code' => 'nullable|string|max:3',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'capital' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'subregion' => 'nullable|string|max:100',
        ];
    }
}
