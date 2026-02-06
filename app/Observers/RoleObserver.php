<?php

namespace App\Observers;

use App\Models\Auth\Role;
use App\Models\Common\ActivityLog;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        ActivityLog::log(
            $role,
            'role_created',
            "Role '{$role->name}' was created",
            [
                'role_id' => $role->id,
                'company_id' => $role->company_id,
                'name' => $role->name,
                'slug' => $role->slug ?? null,
                'description' => $role->description ?? null,
                'is_active' => $role->is_active ?? null,
            ]
        );
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $changes = $role->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'role_id' => $role->id,
            'company_id' => $role->company_id,
            'name' => $role->name,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $role->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $role,
            'role_updated',
            "Role '{$role->name}' was updated",
            $properties
        );
    }
}
