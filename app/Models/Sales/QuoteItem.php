<?php

namespace App\Models\Sales;

use App\Models\Catalog\ProductVariant;
use App\Models\Tax\TaxGroup;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $fillable = [
        'quote_id',

        'product_variant_id',

        // Custom/manual item support
        'is_custom',
        'custom_sku',

        // Snapshot
        'product_name',
        'variant_description',

        // Pricing
        'unit_price',
        'quantity',
        'line_subtotal',

        // Discounts
        'discount_percent',
        'discount_amount',
        'line_discount_total',

        // Tax snapshot
        'tax_group_id',
        'tax_rate',
        'tax_amount',

        // Final
        'line_total',
    ];

    protected $casts = [
        'is_custom' => 'boolean',

        'unit_price' => 'decimal:6',
        'quantity' => 'integer',
        'line_subtotal' => 'decimal:6',

        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:6',
        'line_discount_total' => 'decimal:6',

        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:6',

        'line_total' => 'decimal:6',
    ];

    /* ================= Relations ================= */

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function taxGroup()
    {
        return $this->belongsTo(TaxGroup::class, 'tax_group_id');
    }

    /* ================= Helpers ================= */

    /**
     * Get the effective tax group for this line:
     * line override -> quote default
     */
    public function getEffectiveTaxGroupIdAttribute(): ?int
    {
        return $this->tax_group_id ?: $this->quote?->tax_group_id;
    }

    /**
     * Simple derived line formulas (if you want to recompute in code)
     * - subtotal = qty * unit_price
     * - line_total = (subtotal - discount) + tax
     */
    public function computeLineSubtotal(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    public function computeLineTotal(): float
    {
        $subtotal = $this->computeLineSubtotal();
        $discount = (float) $this->line_discount_total;
        $tax = (float) $this->tax_amount;

        return max(0, $subtotal - $discount) + $tax;
    }
}
