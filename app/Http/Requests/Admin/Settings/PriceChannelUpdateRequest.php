<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class PriceChannelUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $priceChannel = $this->route('price_channel') ?? $this->route('priceChannel');
        $priceChannelId = is_object($priceChannel) ? $priceChannel->id : $priceChannel;

        return [
            'code' => 'required|string|max:50|alpha_dash:ascii|unique:price_channels,code,' . $priceChannelId,
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
