<?php

namespace App\Observers;

use App\Models\Accounting\Invoice;
use App\Models\Sales\Order;

class InvoiceObserver extends BaseActivityObserver
{
    public static bool $syncing = false;

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->logCreated($invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->logUpdated($invoice);

        $this->syncPoNumberToOrder($invoice);
    }

    /**
     * Push the invoice's po_number into the parent order's order_number so that
     * it becomes the canonical PO number for the order, the invoice and all
     * delivery notes belonging to that order.
     */
    protected function syncPoNumberToOrder(Invoice $invoice): void
    {
        if (self::$syncing || DeliveryNoteObserver::$syncing) {
            return;
        }

        if (! $invoice->wasChanged('po_number')) {
            return;
        }

        if (! $invoice->source_id || $invoice->source_type !== Order::class) {
            return;
        }

        $order = Order::find($invoice->source_id);
        if (! $order) {
            return;
        }

        if ((string) $order->order_number === (string) $invoice->po_number) {
            return;
        }

        self::$syncing = true;

        try {
            $order->order_number = $invoice->po_number;
            $order->saveQuietly();
        } finally {
            self::$syncing = false;
        }
    }
}
