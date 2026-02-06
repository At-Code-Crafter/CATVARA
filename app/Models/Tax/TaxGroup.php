<?php

namespace App\Models\Tax;

use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'is_tax_inclusive',
        'is_active',
    ];

    protected $casts = [
        'is_tax_inclusive' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'tax_group_id')
            ->orderBy('priority');
    }

    /**
     * Handy helper: get total percent of active rates (e.g., 5.0000 or 18.0000)
     */
    public function activeRateSum(): float
    {
        return (float) $this->rates()
            ->where('is_active', true)
            ->sum('rate');
    }
}
