<?php

namespace App\Observers;

use App\Models\Sales\QuoteItem;

class QuoteItemObserver extends BaseActivityObserver
{
    /**
     * Handle the QuoteItem "created" event.
     */
    public function created(QuoteItem $quoteItem): void
    {
        $this->logCreated($quoteItem);
    }

    /**
     * Handle the QuoteItem "updated" event.
     */
    public function updated(QuoteItem $quoteItem): void
    {
        $this->logUpdated($quoteItem);
    }
}
