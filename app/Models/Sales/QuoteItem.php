<?php

namespace App\Models\Sales;

use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',
        'product_variant_id',
        'product_name',
        'variant_description',
        'unit_price',
        'quantity',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:6',
        'quantity' => 'decimal:6',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:6',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:6',
        'line_total' => 'decimal:6',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
