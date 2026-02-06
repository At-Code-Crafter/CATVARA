<?php

namespace App\Observers;

use App\Models\Auth\Permission;
use App\Models\Common\ActivityLog;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        ActivityLog::log(
            $permission,
            'permission_created',
            "Permission '{$permission->name}' was created",
            [
                'permission_id' => $permission->id,
                'module_id' => $permission->module_id ?? null,
                'name' => $permission->name,
                'slug' => $permission->slug ?? null,
                'is_active' => $permission->is_active ?? null,
            ]
        );
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        $changes = $permission->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'permission_id' => $permission->id,
            'name' => $permission->name,
            'slug' => $permission->slug ?? null,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $permission->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $permission,
            'permission_updated',
            "Permission '{$permission->name}' was updated",
            $properties
        );
    }
}
