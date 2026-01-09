<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $companyId = $this->route('company')->id ?? $this->company->id ?? null;

        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:stores,code,NULL,id,company_id,' . $companyId,
            ],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Store name is required.',
            'code.required' => 'Store code is required.',
            'code.unique' => 'This store code already exists for your company.',
        ];
    }
}
