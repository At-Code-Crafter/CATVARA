<?php

namespace App\Observers;

use App\Models\Sales\DeliveryNoteItem;

class DeliveryNoteItemObserver extends BaseActivityObserver
{
    /**
     * Handle the DeliveryNoteItem "created" event.
     */
    public function created(DeliveryNoteItem $deliveryNoteItem): void
    {
        $this->logCreated($deliveryNoteItem);
    }

    /**
     * Handle the DeliveryNoteItem "updated" event.
     */
    public function updated(DeliveryNoteItem $deliveryNoteItem): void
    {
        $this->logUpdated($deliveryNoteItem);
    }
}
