<?php

namespace App\Observers;

use App\Models\Accounting\Invoice;

class InvoiceObserver extends BaseActivityObserver
{
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
    }
}
