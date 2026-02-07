<?php

namespace App\Observers;

use App\Models\Returns\CreditNote;

class CreditNoteObserver extends BaseActivityObserver
{
    /**
     * Handle the CreditNote "created" event.
     */
    public function created(CreditNote $creditNote): void
    {
        $this->logCreated($creditNote);
    }

    /**
     * Handle the CreditNote "updated" event.
     */
    public function updated(CreditNote $creditNote): void
    {
        $this->logUpdated($creditNote);
    }
}
