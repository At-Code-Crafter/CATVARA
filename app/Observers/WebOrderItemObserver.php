<?php

namespace App\Observers;

use App\Models\Web\WebOrderItem;

class WebOrderItemObserver extends BaseActivityObserver
{
    /**
     * Handle the WebOrderItem "created" event.
     */
    public function created(WebOrderItem $webOrderItem): void
    {
        $this->logCreated($webOrderItem);
    }

    /**
     * Handle the WebOrderItem "updated" event.
     */
    public function updated(WebOrderItem $webOrderItem): void
    {
        $this->logUpdated($webOrderItem);
    }
}
