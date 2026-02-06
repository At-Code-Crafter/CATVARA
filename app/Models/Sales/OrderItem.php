<?php

namespace App\Models\Sales;

use App\Models\Catalog\ProductVariant;
use App\Models\Tax\TaxGroup; // ✅ add this (create later)
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',

        'product_variant_id',

        // custom/manual item support
        'is_custom',
        'custom_sku',

        // snapshot
        'product_name',
        'variant_description',

        // pricing
        'unit_price',
        'quantity',
        'fulfilled_quantity',

        'line_subtotal',

        // discounts
        'discount_amount',
        'discount_percent',
        'line_discount_total',

        // tax snapshot
        'tax_group_id',
        'tax_rate',
        'tax_amount',

        // final
        'line_total',
    ];

    protected $casts = [
        'is_custom' => 'boolean',

        'unit_price' => 'decimal:6',
        'line_subtotal' => 'decimal:6',

        'discount_amount' => 'decimal:6',
        'discount_percent' => 'decimal:2',
        'line_discount_total' => 'decimal:6',

        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:6',

        'line_total' => 'decimal:6',
    ];

    /* ================= Relations ================= */

    public function order()
    {
        return $this->belongsTo(Order::class);
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
     * line override -> order default
     */
    public function getEffectiveTaxGroupIdAttribute(): ?int
    {
        return $this->tax_group_id ?: $this->order?->tax_group_id;
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
        $subtotal = (float) $this->line_subtotal;
        $discount = (float) $this->line_discount_total;
        $tax = (float) $this->tax_amount;

        return max(0, ($subtotal - $discount) + $tax);
    }
}
