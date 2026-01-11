<?php

namespace App\Models\Sales;

use App\Models\Accounting\Payment;
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

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
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
