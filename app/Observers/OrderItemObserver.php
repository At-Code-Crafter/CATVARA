<?php

namespace App\Observers;

use App\Models\Sales\OrderItem;

class OrderItemObserver extends BaseActivityObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        $this->logCreated($orderItem);
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        $this->logUpdated($orderItem);
    }
}
