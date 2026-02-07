<?php

namespace App\Observers;

use App\Models\Inventory\InventoryTransfer;

class InventoryTransferObserver extends BaseActivityObserver
{
    /**
     * Handle the InventoryTransfer "created" event.
     */
    public function created(InventoryTransfer $inventoryTransfer): void
    {
        $this->logCreated($inventoryTransfer);
    }

    /**
     * Handle the InventoryTransfer "updated" event.
     */
    public function updated(InventoryTransfer $inventoryTransfer): void
    {
        $this->logUpdated($inventoryTransfer);
    }
}
