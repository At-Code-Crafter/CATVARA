<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLoginActivity extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'location',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
