<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use App\Models\Common\State;

class StateObserver
{
    /**
     * Handle the State "created" event.
     */
    public function created(State $state): void
    {
        ActivityLog::log(
            $state,
            'state_created',
            "State '{$state->name}' was created",
            [
                'state_id' => $state->id,
                'country_id' => $state->country_id,
                'name' => $state->name,
                'code' => $state->code,
                'type' => $state->type,
                'is_active' => $state->is_active,
            ]
        );
    }

    /**
     * Handle the State "updated" event.
     */
    public function updated(State $state): void
    {
        $changes = $state->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'state_id' => $state->id,
            'country_id' => $state->country_id,
            'name' => $state->name,
            'code' => $state->code,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $state->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $state,
            'state_updated',
            "State '{$state->name}' was updated",
            $properties
        );
    }
}
