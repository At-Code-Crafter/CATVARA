<?php

namespace App\Models\Company;

use App\Models\Accounting\PaymentTerm;
use App\Models\Auth\Role;
use App\Models\Common\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'legal_name',
        'code',
        'logo',
        'website_url',
        'base_currency_id',
        'company_status_id',
        'password_expiry_days',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid ??= (string) Str::uuid();
        });
    }

    public function status()
    {
        return $this->belongsTo(CompanyStatus::class, 'company_status_id');
    }

    public function detail()
    {
        return $this->hasOne(CompanyDetail::class);
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'company_user'
        )->withPivot(['is_owner', 'is_active'])->withTimestamps();
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function baseCurrency()
    {
        return $this->belongsTo(\App\Models\Pricing\Currency::class, 'base_currency_id');
    }

    public function exchangeRates()
    {
        return $this->hasMany(\App\Models\Pricing\ExchangeRate::class);
    }

    public function paymentTerms()
    {
        return $this->hasMany(PaymentTerm::class);
    }

    // Company Has one Address
    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function priceChannels()
    {
        return $this->belongsToMany(
            \App\Models\Pricing\PriceChannel::class,
            'company_price_channels'
        )->using(\App\Models\Pricing\CompanyPriceChannel::class)
         ->withPivot('is_active')
         ->withTimestamps();
    }

    public function banks()
    {
        return $this->hasMany(CompanyBank::class);
    }
}
