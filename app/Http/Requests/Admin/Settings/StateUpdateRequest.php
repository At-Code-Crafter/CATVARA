<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StateUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $state = $this->route('state');
        $stateId = is_object($state) ? $state->id : $state;

        return [
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:10|unique:states,code,' . $stateId . ',id,country_id,' . $this->input('country_id'),
            'type' => 'nullable|string|max:50',
        ];
    }
}
