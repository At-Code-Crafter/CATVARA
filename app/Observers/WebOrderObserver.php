<?php

namespace App\Observers;

use App\Models\Web\WebOrder;

class WebOrderObserver extends BaseActivityObserver
{
    /**
     * Handle the WebOrder "created" event.
     */
    public function created(WebOrder $webOrder): void
    {
        $this->logCreated($webOrder);
    }

    /**
     * Handle the WebOrder "updated" event.
     */
    public function updated(WebOrder $webOrder): void
    {
        $this->logUpdated($webOrder);
    }
}
