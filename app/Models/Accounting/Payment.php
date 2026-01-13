<?php

namespace App\Models\Accounting;

use App\Models\Attachment;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'customer_id',
        'payment_method_id',
        'status_id',
        'payment_number',
        'source',
        'direction',
        'currency_id',
        'amount',
        'exchange_rate',
        'base_amount',
        'unallocated_amount',
        'reference',
        'description',
        'gateway_reference',
        'gateway_payload',
        'paid_at',
        'received_by',
        'created_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'amount'             => 'decimal:6',
        'base_amount'        => 'decimal:6',
        'unallocated_amount' => 'decimal:6',
        'exchange_rate'      => 'decimal:8',
        'gateway_payload'    => 'array',
        'paid_at'            => 'datetime',
        'confirmed_at'       => 'datetime',
    ];

    /* ==========================
     | RELATIONSHIPS
     ========================== */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function status()
    {
        return $this->belongsTo(PaymentStatus::class, 'status_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Payment applications (allocations to orders/invoices)
     */
    public function applications()
    {
        return $this->hasMany(PaymentApplication::class);
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

    public function scopeIncoming($query)
    {
        return $query->where('direction', 'IN');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'OUT');
    }

    public function scopeConfirmed($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'CONFIRMED'));
    }

    public function scopePending($query)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', 'PENDING'));
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', strtoupper($source));
    }

    public function scopeUnallocated($query)
    {
        return $query->where('unallocated_amount', '>', 0);
    }

    /* ==========================
     | HELPERS
     ========================== */

    /**
     * Calculate allocated amount from applications
     */
    public function allocatedAmount(): string
    {
        return (string) $this->applications()->sum('amount');
    }

    /**
     * Calculate remaining unallocated amount
     */
    public function calculateUnallocatedAmount(): string
    {
        return bcsub(
            (string) $this->amount,
            (string) $this->allocatedAmount(),
            6
        );
    }

    /**
     * Check if payment is fully allocated
     */
    public function isFullyAllocated(): bool
    {
        return bccomp(
            (string) $this->allocatedAmount(),
            (string) $this->amount,
            6
        ) >= 0;
    }

    /**
     * Check if payment is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status?->code === 'CONFIRMED';
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status?->code === 'PENDING';
    }

    /**
     * Check if payment can be edited
     */
    public function canBeEdited(): bool
    {
        return !$this->status?->is_final && $this->applications()->count() === 0;
    }

    /**
     * Check if payment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return !$this->status?->is_final;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2);
    }

    /**
     * Get formatted base amount
     */
    public function getFormattedBaseAmountAttribute(): string
    {
        return number_format((float) $this->base_amount, 2);
    }

    /**
     * Get source label
     */
    public function getSourceLabelAttribute(): string
    {
        return match($this->source) {
            'WEB' => 'Web',
            'POS' => 'POS',
            'MANUAL' => 'Manual',
            'API' => 'API',
            default => $this->source,
        };
    }

    /**
     * Get direction label
     */
    public function getDirectionLabelAttribute(): string
    {
        return $this->direction === 'IN' ? 'Received' : 'Refund';
    }
}
