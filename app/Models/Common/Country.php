<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'iso_code_2',
        'iso_code_3',
        'numeric_code',
        'phone_code',
        'currency_code',
        'capital',
        'region',
        'subregion',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the states for this country.
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get active states for this country.
     */
    public function activeStates(): HasMany
    {
        return $this->hasMany(State::class)->where('is_active', true);
    }

    /**
     * Scope for active countries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get the route key for Laravel model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
