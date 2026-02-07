<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyBank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'bank_name',
        'account_name',
        'account_number',
        'iban',
        'swift_code',
        'branch',
        'currency_id',
        'is_active',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Pricing\Currency::class);
    }
}
