<?php

namespace App\Observers;

use App\Models\Inventory\InventoryTransferItem;

class InventoryTransferItemObserver extends BaseActivityObserver
{
    /**
     * Handle the InventoryTransferItem "created" event.
     */
    public function created(InventoryTransferItem $inventoryTransferItem): void
    {
        $this->logCreated($inventoryTransferItem);
    }

    /**
     * Handle the InventoryTransferItem "updated" event.
     */
    public function updated(InventoryTransferItem $inventoryTransferItem): void
    {
        $this->logUpdated($inventoryTransferItem);
    }
}
