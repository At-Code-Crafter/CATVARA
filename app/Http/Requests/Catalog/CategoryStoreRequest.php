<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'parent_id' => ['nullable', 'exists:categories,id'],

            'tax_group_id' => ['nullable', 'exists:tax_groups,id'],

            'attributes' => ['required', 'array', 'min:1'],
            'attributes.*' => ['required', 'exists:attributes,id'],

            'is_active' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'attributes.required' => 'Please select at least one attribute.',
            'attributes.min' => 'Please select at least one attribute.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? 1 : 0,
        ]);
    }
}
