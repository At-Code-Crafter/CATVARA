<?php

namespace App\Observers;

use App\Models\Sales\DeliveryNote;

class DeliveryNoteObserver extends BaseActivityObserver
{
    /**
     * Handle the DeliveryNote "created" event.
     */
    public function created(DeliveryNote $deliveryNote): void
    {
        $this->logCreated($deliveryNote);
    }

    /**
     * Handle the DeliveryNote "updated" event.
     */
    public function updated(DeliveryNote $deliveryNote): void
    {
        $this->logUpdated($deliveryNote);
    }
}
