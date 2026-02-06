<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class CompanyUserRole extends Model
{
    protected $table = 'company_user_role';

    protected $fillable = [
        'company_id',
        'user_id',
        'role_id',
    ];

    public $timestamps = true;
}
