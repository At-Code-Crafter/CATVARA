<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = $this->route('company')?->id;
        $customerId = $this->route('customer');

        return [
            'type' => ['required', Rule::in(['INDIVIDUAL', 'COMPANY'])],
            'display_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')
                    ->where('company_id', $companyId)
                    ->ignore($customerId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers', 'phone')
                    ->where('company_id', $companyId)
                    ->ignore($customerId),
            ],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'address_line_1' => ['nullable', 'string', 'max:1000'],
            'address_line_2' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'payment_term_id' => ['nullable', 'exists:payment_terms,id'],
            'percentage_discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:100', Rule::in(timezone_identifiers_list())],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered for another customer in this company.',
            'phone.unique' => 'This phone number is already registered for another customer in this company.',
        ];
    }
}
