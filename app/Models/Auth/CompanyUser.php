<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUser extends Pivot
{
    protected $table = 'company_user';

    protected $fillable = [
        'company_id',
        'user_id',
        'is_owner',
        'is_active',
    ];

    public $timestamps = true;
}
