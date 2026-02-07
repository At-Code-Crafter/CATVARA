<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:10|unique:states,code,NULL,id,country_id,' . $this->input('country_id'),
            'type' => 'nullable|string|max:50',
        ];
    }
}
