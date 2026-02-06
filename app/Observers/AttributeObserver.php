<?php

namespace App\Observers;

use App\Models\Catalog\Attribute;
use App\Models\Common\ActivityLog;

class AttributeObserver
{
    /**
     * Handle the Attribute "created" event.
     */
    public function created(Attribute $attribute): void
    {
        ActivityLog::log(
            $attribute,
            'attribute_created',
            "Attribute '{$attribute->name}' was created",
            [
                'attribute_id' => $attribute->id,
                'name' => $attribute->name,
                'code' => $attribute->code,
                'is_active' => $attribute->is_active,
            ]
        );
    }

    /**
     * Handle the Attribute "updated" event.
     */
    public function updated(Attribute $attribute): void
    {
        $changes = $attribute->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'attribute_id' => $attribute->id,
            'name' => $attribute->name,
            'code' => $attribute->code,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $attribute->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $attribute,
            'attribute_updated',
            "Attribute '{$attribute->name}' was updated",
            $properties
        );
    }
}
