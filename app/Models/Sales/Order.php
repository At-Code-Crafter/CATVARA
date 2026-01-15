<?php

namespace App\Models\Sales;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentApplication;
use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'customer_id',
        'status_id',
        'source',
        'source_id',
        'order_number',
        'currency_id',
        'payment_term_id',
        'payment_term_name',
        'payment_due_days',
        'due_date',
        'subtotal',
        'tax_total',
        'discount_total',
        'grand_total',
        'confirmed_at',
        'created_by',
        'notes',
        'shipping_total',
        'shipping_tax_total',
        'additional_charges',
        'additional_total',
        'payment_status',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'due_date' => 'date',
        'subtotal' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'discount_total' => 'decimal:6',
        'grand_total' => 'decimal:6',
        'shipping_total' => 'decimal:6',
        'shipping_tax_total' => 'decimal:6',
        'additional_charges' => 'json',
        'additional_total' => 'decimal:6',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
     * Get all payment applications for this order
     */
    public function paymentApplications()
    {
        return $this->morphMany(PaymentApplication::class, 'paymentable');
    }

    /**
     * Get total amount paid for this order (in order's currency)
     * Uses converted_amount for multi-currency payments, falls back to amount for same-currency
     */
    public function getTotalPaidAttribute(): float
    {
        $applications = $this->paymentApplications()
            ->whereHas('payment', fn($q) => $q->whereHas('status', fn($s) => $s->where('code', 'CONFIRMED')))
            ->get();

        return (float) $applications->sum(function ($app) {
            // Use converted_amount (in document/order currency) if available
            return $app->converted_amount ?? $app->amount;
        });
    }

    /**
     * Get outstanding balance for this order
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return max(0, (float) $this->grand_total - $this->total_paid);
    }

    /**
     * Check if order is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    /**
     * Check if order is partially paid
     */
    public function isPartiallyPaid(): bool
    {
        return $this->total_paid > 0 && !$this->isFullyPaid();
    }

    /**
     * Get payment status label
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        if ($this->isFullyPaid()) {
            return 'PAID';
        } elseif ($this->isPartiallyPaid()) {
            return 'PARTIAL';
        }
        return 'UNPAID';
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

    public function invoice()
    {
        // Invoice source_id = order.id
        // invoice.source_type = Order::class
        return $this->hasOne(\App\Models\Accounting\Invoice::class, 'source_id')->where('source_type', self::class);
    }


}
