<?php

namespace App\Models\Sales;

use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'customer_id',
        'status_id',
        'quote_number',
        'currency_id',
        'payment_term_id',
        'payment_term_name',
        'payment_due_days',
        'valid_until',
        'subtotal',
        'tax_total',
        'discount_total',
        'shipping_total',
        'shipping_tax_total',
        'grand_total',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'created_by',
        'order_id',
        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'subtotal' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'discount_total' => 'decimal:6',
        'shipping_total' => 'decimal:6',
        'shipping_tax_total' => 'decimal:6',
        'grand_total' => 'decimal:6',
    ];

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

    /**
     * Check if quote is still valid
     */
    public function isValid(): bool
    {
        if (!$this->valid_until) {
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
        return in_array($this->status->code ?? '', $allowedStatuses) && $this->isValid() && !$this->order_id;
    }
}
