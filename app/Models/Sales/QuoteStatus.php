<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class QuoteStatus extends Model
{
    protected $fillable = ['code', 'name', 'is_final', 'is_active'];

    protected $casts = [
        'is_final' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'status_id');
    }
}
