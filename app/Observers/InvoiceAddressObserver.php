<?php

namespace App\Observers;

use App\Models\Accounting\InvoiceAddress;

class InvoiceAddressObserver extends BaseActivityObserver
{
    /**
     * Handle the InvoiceAddress "created" event.
     */
    public function created(InvoiceAddress $invoiceAddress): void
    {
        $this->logCreated($invoiceAddress);
    }

    /**
     * Handle the InvoiceAddress "updated" event.
     */
    public function updated(InvoiceAddress $invoiceAddress): void
    {
        $this->logUpdated($invoiceAddress);
    }
}
