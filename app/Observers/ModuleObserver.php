<?php

namespace App\Observers;

use App\Models\Auth\Module;
use App\Models\Common\ActivityLog;

class ModuleObserver
{
    /**
     * Handle the Module "created" event.
     */
    public function created(Module $module): void
    {
        ActivityLog::log(
            $module,
            'module_created',
            "Module '{$module->name}' was created",
            [
                'module_id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug ?? null,
                'is_active' => $module->is_active ?? null,
            ]
        );
    }

    /**
     * Handle the Module "updated" event.
     */
    public function updated(Module $module): void
    {
        $changes = $module->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'module_id' => $module->id,
            'name' => $module->name,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $module->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $module,
            'module_updated',
            "Module '{$module->name}' was updated",
            $properties
        );
    }
}
