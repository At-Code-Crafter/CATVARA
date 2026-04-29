<?php

namespace App\Observers;

use App\Models\Sales\DeliveryNote;

class DeliveryNoteObserver extends BaseActivityObserver
{
    public static bool $syncing = false;

    /**
     * Handle the DeliveryNote "created" event.
     */
    public function created(DeliveryNote $deliveryNote): void
    {
        $this->logCreated($deliveryNote);

        $this->syncPoNumberToOrder($deliveryNote);
    }

    /**
     * Handle the DeliveryNote "updated" event.
     */
    public function updated(DeliveryNote $deliveryNote): void
    {
        $this->logUpdated($deliveryNote);

        $this->syncPoNumberToOrder($deliveryNote);
    }

    /**
     * Push the delivery note's po_number into the parent order's order_number
     * so that it becomes the canonical PO number for the order, the invoice
     * and any sibling delivery notes.
     */
    protected function syncPoNumberToOrder(DeliveryNote $deliveryNote): void
    {
        if (self::$syncing || InvoiceObserver::$syncing) {
            return;
        }

        if ($deliveryNote->wasRecentlyCreated) {
            if (empty($deliveryNote->po_number)) {
                return;
            }
        } elseif (! $deliveryNote->wasChanged('po_number')) {
            return;
        }

        $order = $deliveryNote->order;
        if (! $order) {
            return;
        }

        if ((string) $order->order_number === (string) $deliveryNote->po_number) {
            return;
        }

        self::$syncing = true;

        try {
            $order->order_number = $deliveryNote->po_number;
            $order->saveQuietly();
        } finally {
            self::$syncing = false;
        }
    }
}
