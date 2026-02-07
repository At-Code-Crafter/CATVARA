<?php

namespace App\Models\Sales;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentApplication;
use App\Models\Accounting\PaymentStatus;
use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\Tax\TaxGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',

        'order_number',

        'customer_id',
        'shipping_customer_id',

        'status_id',
        'payment_status_id',

        'source',
        'source_reference',

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
        'due_date',

        // Totals snapshot
        'subtotal',
        'discount_total',
        'shipping_total',
        'shipping_tax_total',
        'tax_total',
        'rounding_total',
        'global_discount_percent',
        'global_discount_amount',
        'grand_total',

        // Payment tracking (optional but useful)
        'paid_total',
        'refunded_total',

        // Fulfillment tracking
        'is_fulfilled',

        'confirmed_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'due_date' => 'date',

        'fx_rate' => 'decimal:10',

        'subtotal' => 'decimal:6',
        'discount_total' => 'decimal:6',
        'shipping_total' => 'decimal:6',
        'shipping_tax_total' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'rounding_total' => 'decimal:6',
        'global_discount_percent' => 'decimal:2',
        'global_discount_amount' => 'decimal:6',
        'grand_total' => 'decimal:6',

        'paid_total' => 'decimal:6',
        'refunded_total' => 'decimal:6',

        'is_fulfilled' => 'boolean',
    ];

    /* ================= Relations ================= */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function paymentStatus()
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
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
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
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

    /**
     * Legacy morphMany - if payments are linked directly to order
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Payment applications (new structure)
     */
    public function paymentApplications()
    {
        return $this->morphMany(PaymentApplication::class, 'paymentable');
    }

    public function invoice()
    {
        return $this->hasOne(\App\Models\Accounting\Invoice::class, 'source_id')
            ->where('source_type', self::class);
    }

    /* ================= Computed ================= */

    /**
     * Total paid in order currency (uses converted_amount if present)
     */
    public function getTotalPaidAttribute(): float
    {
        $applications = $this->paymentApplications()
            ->whereHas('payment', fn ($q) => $q->whereHas('status', fn ($s) => $s->where('code', 'CONFIRMED')))
            ->get();

        return (float) $applications->sum(function ($app) {
            return $app->converted_amount ?? $app->amount;
        });
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, (float) $this->grand_total - $this->total_paid);
    }

    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->total_paid > 0 && ! $this->isFullyPaid();
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'PAID';
        }
        if ($this->isPartiallyPaid()) {
            return 'PARTIAL';
        }

        return 'UNPAID';
    }

    /**
     * Convenience computed base totals (no DB storage)
     * Assumption: base_amount = order_amount * fx_rate
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

}
