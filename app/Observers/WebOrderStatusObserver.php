<?php

namespace App\Observers;

use App\Models\Web\WebOrderStatus;

class WebOrderStatusObserver extends BaseActivityObserver
{
    /**
     * Handle the WebOrderStatus "created" event.
     */
    public function created(WebOrderStatus $webOrderStatus): void
    {
        $this->logCreated($webOrderStatus);
    }

    /**
     * Handle the WebOrderStatus "updated" event.
     */
    public function updated(WebOrderStatus $webOrderStatus): void
    {
        $this->logUpdated($webOrderStatus);
    }
}
