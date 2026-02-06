<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class GenerateDeliveryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'inventory_location_id' => ['required', 'exists:inventory_locations,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
