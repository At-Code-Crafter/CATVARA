<?php

namespace App\Models\Accounting;

use App\Models\Attachment;
use App\Models\Company\Company;
use App\Models\Pricing\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'payment_id',
        'paymentable_type',
        'paymentable_id',
        'currency_id',
        'document_currency_id',
        'amount',
        'converted_amount',
        'exchange_rate',
        'base_amount',
        'notes',
        'applied_by',
        'applied_at',
    ];

    protected $casts = [
        'amount'           => 'decimal:6',
        'converted_amount' => 'decimal:6',
        'base_amount'      => 'decimal:6',
        'exchange_rate'    => 'decimal:8',
        'applied_at'       => 'datetime',
    ];

    /* ==========================
     | RELATIONSHIPS
     ========================== */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * The document this payment is applied to (Order, Invoice, CreditNote)
     */
    public function paymentable()
    {
        return $this->morphTo();
    }

    /**
     * Payment currency (the currency the payment was made in)
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Document currency (the currency of the order/invoice being paid)
     */
    public function documentCurrency()
    {
        return $this->belongsTo(Currency::class, 'document_currency_id');
    }

    public function applier()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    /**
     * Polymorphic attachments
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /* ==========================
     | SCOPES
     ========================== */

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForPayment($query, $paymentId)
    {
        return $query->where('payment_id', $paymentId);
    }

    public function scopeForDocument($query, string $type, int $id)
    {
        return $query->where('paymentable_type', $type)
                     ->where('paymentable_id', $id);
    }

    /* ==========================
     | HELPERS
     ========================== */

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2);
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->paymentable_type) {
            'App\\Models\\Sales\\Order' => 'Order',
            'App\\Models\\Accounting\\Invoice' => 'Invoice',
            'App\\Models\\Accounting\\CreditNote' => 'Credit Note',
            default => class_basename($this->paymentable_type),
        };
    }

    /**
     * Get document number
     */
    public function getDocumentNumberAttribute(): ?string
    {
        return $this->paymentable?->order_number
            ?? $this->paymentable?->invoice_number
            ?? $this->paymentable?->credit_number
            ?? null;
    }
}
