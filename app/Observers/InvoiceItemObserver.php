<?php

namespace App\Observers;

use App\Models\Accounting\InvoiceItem;

class InvoiceItemObserver extends BaseActivityObserver
{
    /**
     * Handle the InvoiceItem "created" event.
     */
    public function created(InvoiceItem $invoiceItem): void
    {
        $this->logCreated($invoiceItem);
    }

    /**
     * Handle the InvoiceItem "updated" event.
     */
    public function updated(InvoiceItem $invoiceItem): void
    {
        $this->logUpdated($invoiceItem);
    }
}
