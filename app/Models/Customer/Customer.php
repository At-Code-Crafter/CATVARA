<?php

namespace App\Models\Customer;

use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Country;
use App\Models\Common\State;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer\CustomerAddress;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'type',
        'display_name',
        'email',
        'phone',
        'legal_name',
        'tax_number',
        'notes',
        'country_id',
        'state_id',
        'postal_code',
        'address',
        'is_active',
        'payment_term_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function billingAddresses()
    {
        return $this->hasMany(CustomerAddress::class)->where('type', 'BILLING');
    }

    public function shippingAddresses()
    {
        return $this->hasMany(CustomerAddress::class)->where('type', 'SHIPPING');
    }

    public function paymentTerm(){
        return $this->belongsTo(PaymentTerm::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
