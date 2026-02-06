<?php

namespace App\Observers;

use App\Models\Catalog\Category;
use App\Models\Common\ActivityLog;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        ActivityLog::log(
            $category,
            'category_created',
            "Category '{$category->name}' was created",
            [
                'category_id' => $category->id,
                'parent_id' => $category->parent_id,
                'tax_group_id' => $category->tax_group_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'is_active' => $category->is_active,
            ]
        );
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $changes = $category->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'category_id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $category->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $category,
            'category_updated',
            "Category '{$category->name}' was updated",
            $properties
        );
    }
}
