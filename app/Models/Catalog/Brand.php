<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'logo',
        'is_active',
    ];

    /* ================= Relations ================= */

    public function parent()
    {
        return $this->belongsTo(Brand::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Brand::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
