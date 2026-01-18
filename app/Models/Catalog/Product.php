<?php

namespace App\Models\Catalog;

use App\Models\Catalog\Category;
use App\Models\Common\Attachment;
use App\Models\Catalog\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'uuid',
        'company_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'is_active',
        'image'
    ];

    /* ================= Relations ================= */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attachments()
    {
        return $this->morphMany(
            Attachment::class,
            'attachable'
        );
    }
}
