<?php

namespace App\Models\Sales;

use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\Tax\TaxGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',

        // Customers (bill-to / ship-to)
        'customer_id',
        'shipping_customer_id',

        'status_id',
        'quote_number',

        // Currency snapshot
        'currency_id',
        'base_currency_id',
        'fx_rate',

        // Tax defaults (line can override)
        'tax_group_id',

        // Payment term snapshot
        'payment_term_id',
        'payment_term_name',
        'payment_due_days',

        // Validity
        'valid_until',

        // Totals snapshot
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'shipping_tax_total',
        'global_discount_percent',
        'global_discount_amount',
        'rounding_total',
        'grand_total',

        // Timestamps
        'sent_at',
        'accepted_at',
        'rejected_at',
        'created_by',

        // Order reference (when converted)
        'order_id',

        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',

        'fx_rate' => 'decimal:10',

        'subtotal' => 'decimal:6',
        'discount_total' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'shipping_total' => 'decimal:6',
        'shipping_tax_total' => 'decimal:6',
        'global_discount_percent' => 'decimal:2',
        'global_discount_amount' => 'decimal:6',
        'rounding_total' => 'decimal:6',
        'grand_total' => 'decimal:6',
    ];

    /* ================= Relations ================= */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function status()
    {
        return $this->belongsTo(QuoteStatus::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function shippingCustomer()
    {
        return $this->belongsTo(Customer::class, 'shipping_customer_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function baseCurrency()
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function taxGroup()
    {
        return $this->belongsTo(TaxGroup::class, 'tax_group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function billingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'BILLING');
    }

    public function shippingAddress()
    {
        return $this->morphOne(Address::class, 'addressable')->where('type', 'SHIPPING');
    }

    /* ================= Computed ================= */

    /**
     * Convenience computed base totals (no DB storage)
     * Assumption: base_amount = quote_amount * fx_rate
     */
    public function getBaseSubtotalAttribute(): float
    {
        return (float) $this->subtotal * (float) $this->fx_rate;
    }

    public function getBaseTaxTotalAttribute(): float
    {
        return (float) $this->tax_total * (float) $this->fx_rate;
    }

    public function getBaseGrandTotalAttribute(): float
    {
        return (float) $this->grand_total * (float) $this->fx_rate;
    }

    /**
     * Check if quote is still valid
     */
    public function isValid(): bool
    {
        if (! $this->valid_until) {
            return true;
        }

        return $this->valid_until->isFuture() || $this->valid_until->isToday();
    }

    /**
     * Check if quote can be converted to order
     */
    public function canConvertToOrder(): bool
    {
        $allowedStatuses = ['DRAFT', 'SENT', 'ACCEPTED'];

        return in_array($this->status->code ?? '', $allowedStatuses) && $this->isValid() && ! $this->order_id;
    }
}
