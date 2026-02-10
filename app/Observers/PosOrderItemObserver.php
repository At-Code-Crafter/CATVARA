<?php

namespace App\Observers;

use App\Models\Pos\PosOrderItem;

class PosOrderItemObserver extends BaseActivityObserver
{
    /**
     * Handle the PosOrderItem "created" event.
     */
    public function created(PosOrderItem $posOrderItem): void
    {
        $this->logCreated($posOrderItem);
    }

    /**
     * Handle the PosOrderItem "updated" event.
     */
    public function updated(PosOrderItem $posOrderItem): void
    {
        $this->logUpdated($posOrderItem);
    }
}
