<?php

namespace App\Observers;

use App\Models\Pos\PosOrder;

class PosOrderObserver extends BaseActivityObserver
{
    /**
     * Handle the PosOrder "created" event.
     */
    public function created(PosOrder $posOrder): void
    {
        $this->logCreated($posOrder);
    }

    /**
     * Handle the PosOrder "updated" event.
     */
    public function updated(PosOrder $posOrder): void
    {
        $this->logUpdated($posOrder);
    }
}
