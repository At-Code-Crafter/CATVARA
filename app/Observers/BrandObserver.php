<?php

namespace App\Observers;

use App\Models\Catalog\Brand;
use App\Models\Common\ActivityLog;

class BrandObserver
{
    /**
     * Handle the Brand "created" event.
     */
    public function created(Brand $brand): void
    {
        ActivityLog::log(
            $brand,
            'brand_created',
            "Brand '{$brand->name}' was created",
            [
                'brand_id' => $brand->id,
                'parent_id' => $brand->parent_id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo' => $brand->logo,
                'is_active' => $brand->is_active,
            ]
        );
    }

    /**
     * Handle the Brand "updated" event.
     */
    public function updated(Brand $brand): void
    {
        $changes = $brand->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'brand_id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $brand->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $brand,
            'brand_updated',
            "Brand '{$brand->name}' was updated",
            $properties
        );
    }
}
