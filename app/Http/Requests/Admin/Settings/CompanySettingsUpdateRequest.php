<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class CompanySettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|max:2048',

            // Details
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'invoice_prefix' => 'nullable|string|max:10',
            'quote_prefix' => 'nullable|string|max:10',
        ];
    }
}
