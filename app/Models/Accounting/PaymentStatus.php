<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_final',
        'is_active',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all payments with this status
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'status_id');
    }

    /**
     * Scope: Active statuses only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Final statuses (cannot be changed)
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }
}
