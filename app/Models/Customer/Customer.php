<?php

namespace App\Models\Customer;

use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Tax\TaxGroup;
use Illuminate\Database\Eloquent\Model;
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
        'is_tax_exempt',
        'tax_exempt_reason',
        'tax_group_id',
        'notes',
        'customer_code',
        'is_active',
        'payment_term_id',
        'percentage_discount',
        'timezone',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage_discount' => 'decimal:2',
        'is_tax_exempt' => 'boolean',
    ];

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function taxGroup()
    {
        return $this->belongsTo(TaxGroup::class, 'tax_group_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function getInitialsAttribute()
    {
        return collect(preg_split('/\s+/', trim((string) $this->display_name)))
            ->filter()
            ->map(fn ($p) => mb_substr($p, 0, 1))
            ->take(2)
            ->implode('');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Sales\Order::class);
    }

}
