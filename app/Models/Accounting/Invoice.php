<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'invoice_number',
        'customer_id',
        'shipping_customer_id',
        'status_id',
        'payment_status_id',
        'source_type',
        'source_id',
        'issued_at',
        'due_date',
        'currency_id',
        'base_currency_id',
        'fx_rate',
        'tax_group_id',
        'payment_term_id',
        'payment_term_name',
        'payment_due_days',
        'subtotal',
        'discount_total',
        'global_discount_percent',
        'global_discount_amount',
        'shipping_total',
        'shipping_tax_total',
        'tax_total',
        'rounding_total',
        'grand_total',
        'notes',
        'posted_at',
        'posted_by',
        'voided_at',
        'created_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_date' => 'date',
        'fx_rate' => 'decimal:10',
        'subtotal' => 'decimal:6',
        'discount_total' => 'decimal:6',
        'global_discount_percent' => 'decimal:2',
        'global_discount_amount' => 'decimal:6',
        'shipping_total' => 'decimal:6',
        'shipping_tax_total' => 'decimal:6',
        'tax_total' => 'decimal:6',
        'rounding_total' => 'decimal:6',
        'grand_total' => 'decimal:6',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company\Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Pricing\Currency::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class);
    }

    public function shippingCustomer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'shipping_customer_id');
    }

    public function status()
    {
        return $this->belongsTo(InvoiceStatus::class, 'status_id');
    }

    public function paymentStatus()
    {
        return $this->belongsTo(\App\Models\Accounting\PaymentStatus::class, 'payment_status_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(\App\Models\Accounting\PaymentTerm::class);
    }

    public function addresses()
    {
        return $this->morphMany(\App\Models\Common\Address::class, 'addressable');
    }

    public function billingAddress()
    {
        return $this->morphOne(\App\Models\Common\Address::class, 'addressable')->where('type', 'BILLING');
    }

    public function shippingAddress()
    {
        return $this->morphOne(\App\Models\Common\Address::class, 'addressable')->where('type', 'SHIPPING');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }
}
