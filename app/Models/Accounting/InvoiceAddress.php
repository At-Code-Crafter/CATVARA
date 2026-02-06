<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class InvoiceAddress extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',
        'contact_name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state_id',
        'country_id',
        'zip_code',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function state()
    {
        return $this->belongsTo(\App\Models\Common\State::class);
    }

    public function country()
    {
        return $this->belongsTo(\App\Models\Common\Country::class);
    }
}
