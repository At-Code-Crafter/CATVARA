<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {
        $user = $event->user;

        $activity = \App\Models\UserLoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'location' => null,
            'logged_at' => now(),
        ]);

        if ($activity) {
            \App\Jobs\UpdateLoginLocation::dispatch($activity)->afterResponse();
        }

        if ($user instanceof \App\Models\User) {
            $user->update(['last_login_at' => now()]);
        }
    }
}
