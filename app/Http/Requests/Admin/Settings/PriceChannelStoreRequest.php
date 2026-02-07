<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class PriceChannelStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:price_channels,code|alpha_dash:ascii',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
