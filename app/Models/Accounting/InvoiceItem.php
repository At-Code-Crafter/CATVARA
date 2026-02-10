<?php

namespace App\Models\Accounting;

use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_variant_id',
        'is_custom',
        'custom_sku',
        'product_name',
        'description',
        'variant_description',
        'unit_price',
        'quantity',
        'line_subtotal',
        'discount_amount',
        'discount_percent',
        'line_discount_total',
        'tax_group_id',
        'tax_rate',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:6',
        'line_subtotal' => 'decimal:6',
        'discount_amount' => 'decimal:6',
        'line_discount_total' => 'decimal:6',
        'tax_rate' => 'decimal:6',
        'tax_amount' => 'decimal:6',
        'line_total' => 'decimal:6',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function taxGroup()
    {
        return $this->belongsTo(\App\Models\Tax\TaxGroup::class, 'tax_group_id');
    }
}
