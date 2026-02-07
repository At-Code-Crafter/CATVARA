<?php

namespace App\Http\Requests\Admin\Company;

use Illuminate\Foundation\Http\FormRequest;

class CompanyProfileUpdateRequest extends FormRequest
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
            'password_expiry_days' => 'nullable|integer|min:0',

            // Details
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',

            // Sequences
            'sequences' => 'nullable|array',
            'sequences.*.prefix' => 'nullable|string|max:20',
            'sequences.*.postfix' => 'nullable|string|max:20',
        ];
    }
}
