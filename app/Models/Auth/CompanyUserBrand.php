<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUserBrand extends Pivot
{
    protected $table = 'company_user_brands';

    public $incrementing = true;

    protected $fillable = [
        'company_id',
        'user_id',
        'brand_id',
    ];
}
