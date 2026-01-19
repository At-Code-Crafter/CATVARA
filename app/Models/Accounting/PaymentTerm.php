<?php

namespace App\Models\Accounting;

use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'due_days',
        'is_active',
    ];

    protected $casts = [
        'due_days' => 'integer',
        'is_active' => 'boolean',
    ];

    function scopeForCompany($query, $company_id = null)
    {
        return $query->where('company_id', $company_id ?? active_company_id());
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companies()
    {
        return $this->belongsToMany(
            Company::class,
            'company_payment_terms'
        )->withPivot('is_default');
    }
}
