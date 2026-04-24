<?php

namespace App\Models\Sales;

use App\Models\Company\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'tracking_url_template',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
