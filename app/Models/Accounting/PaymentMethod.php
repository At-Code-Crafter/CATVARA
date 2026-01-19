<?php

namespace App\Models\Accounting;

use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'code',
        'name',
        'type',
        'is_active',
        'allow_refund',
        'requires_reference',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_refund' => 'boolean',
        'requires_reference' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid ??= (string) \Illuminate\Support\Str::uuid();
        });
    }

    /* ==========================
     | RELATIONSHIPS
     ========================== */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id');
    }

    /* ==========================
     | SCOPES
     ========================== */

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', strtoupper($type));
    }

    public function scopeAllowsRefund($query)
    {
        return $query->where('allow_refund', true);
    }

    /* ==========================
     | HELPERS
     ========================== */

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'CASH' => 'Cash',
            'CARD' => 'Card',
            'GATEWAY' => 'Payment Gateway',
            'BANK' => 'Bank Transfer',
            'WALLET' => 'Digital Wallet',
            'CREDIT' => 'Credit/Store Credit',
            default => $this->type,
        };
    }
}
