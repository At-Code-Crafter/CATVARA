<?php

namespace App\Observers;

use App\Models\Sales\Quote;

class QuoteObserver extends BaseActivityObserver
{
    /**
     * Handle the Quote "created" event.
     */
    public function created(Quote $quote): void
    {
        $this->logCreated($quote);
    }

    /**
     * Handle the Quote "updated" event.
     */
    public function updated(Quote $quote): void
    {
        $this->logUpdated($quote);
    }
}
