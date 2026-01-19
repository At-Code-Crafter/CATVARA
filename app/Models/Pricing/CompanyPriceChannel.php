<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyPriceChannel extends Pivot
{
    protected $table = 'company_price_channels';

    public $incrementing = true;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company\Company::class);
    }

    public function priceChannel()
    {
        return $this->belongsTo(PriceChannel::class);
    }
}
