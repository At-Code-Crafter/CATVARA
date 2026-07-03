<?php

namespace App\Models\Catalog;

use App\Models\Catalog\Category;
use App\Models\Common\Attachment;
use App\Models\Catalog\ProductVariant;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid ??= (string) Str::uuid();
        });

        // Inactive products are hidden everywhere by default (lists, pickers,
        // search, etc.). Historical documents (invoices, orders) read snapshot
        // columns, so they keep showing the product regardless of this scope.
        static::addGlobalScope(new ActiveScope);
    }

    /**
     * Include inactive products in the query (removes the ActiveScope).
     * Use in admin management / listing-with-toggle contexts.
     */
    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

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
