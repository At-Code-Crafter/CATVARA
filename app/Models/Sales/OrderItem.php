<?php

namespace App\Models\Sales;

use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_name',
        'variant_description',
        'unit_price',
        'quantity',
        'fulfilled_quantity',
        'line_total',
        'tax_amount',
        'discount_amount',
        'tax_rate'
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
