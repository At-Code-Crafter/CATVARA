<?php

namespace App\Observers;

use App\Models\Returns\CreditNoteItem;

class CreditNoteItemObserver extends BaseActivityObserver
{
    /**
     * Handle the CreditNoteItem "created" event.
     */
    public function created(CreditNoteItem $creditNoteItem): void
    {
        $this->logCreated($creditNoteItem);
    }

    /**
     * Handle the CreditNoteItem "updated" event.
     */
    public function updated(CreditNoteItem $creditNoteItem): void
    {
        $this->logUpdated($creditNoteItem);
    }
}
