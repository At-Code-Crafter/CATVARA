<?php

namespace App\Models\Common;

use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'company_id',
        'type',
        'address_line_1',
        'address_line_2',
        'city',
        'country_id',
        'state_id',
        'address',
        'zip_code',
        'phone',
        'email',
        'name',
        'tax_number',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // use if conditions if empty address 2 then not show and same with city
    public function render($html = false): string
    {
        if (! $this) {
            return '';
        }

        $lines = [
            implode(', ', array_filter([
                $this->address_line_1 ?? ($this->address1 ?? null),
                $this->address_line_2 ?? ($this->address2 ?? null),
            ])),
            implode(', ', array_filter([
                $this->city ?? null,
                $this->state->name ?? null,
            ])),
            implode(' ', array_filter([
                $this->country->name ?? null,
                $this->postal_code ?? ($this->zip ?? null),
            ])),
        ];

        $lines = array_filter($lines);

        return implode($html ? '<br>' : ', ', $lines);
    }
}
