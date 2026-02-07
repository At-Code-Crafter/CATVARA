<?php

namespace App\Observers;

use App\Models\Inventory\InventoryMovement;

class InventoryMovementObserver extends BaseActivityObserver
{
    /**
     * Handle the InventoryMovement "created" event.
     */
    public function created(InventoryMovement $inventoryMovement): void
    {
        $this->logCreated($inventoryMovement);
    }

    /**
     * Handle the InventoryMovement "updated" event.
     */
    public function updated(InventoryMovement $inventoryMovement): void
    {
        $this->logUpdated($inventoryMovement);
    }
}
