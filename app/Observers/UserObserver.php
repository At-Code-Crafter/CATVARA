<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        ActivityLog::log(
            $user,
            'user_created',
            "User '{$user->name}' was created",
            [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'is_active' => $user->is_active,
            ]
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        unset(
            $changes['updated_at'],
            $changes['password'],
            $changes['remember_token'],
            $changes['last_login_at']
        );

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $user->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $user,
            'user_updated',
            "User '{$user->name}' was updated",
            $properties
        );
    }
}
