<?php

namespace App\Observers;

use App\Models\Inventory\InventoryBalance;

class InventoryBalanceObserver extends BaseActivityObserver
{
    /**
     * Handle the InventoryBalance "created" event.
     */
    public function created(InventoryBalance $inventoryBalance): void
    {
        $this->logCreated($inventoryBalance);
    }

    /**
     * Handle the InventoryBalance "updated" event.
     */
    public function updated(InventoryBalance $inventoryBalance): void
    {
        $this->logUpdated($inventoryBalance);
    }
}
