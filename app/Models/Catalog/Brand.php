<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid ??= (string) Str::uuid();
        });
    }

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
