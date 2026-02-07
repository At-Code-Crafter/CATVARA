<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|string',
            'brand_id' => 'nullable|string',
            'description' => 'nullable|string',
            'variants' => 'required|array',
            'image' => 'nullable|image|max:5120',
        ];
    }
}
