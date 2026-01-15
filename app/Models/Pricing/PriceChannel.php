<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Model;

class PriceChannel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get variant prices for this channel.
     */
    public function variantPrices()
    {
        return $this->hasMany(VariantPrice::class, 'price_channel_id');
    }
}
