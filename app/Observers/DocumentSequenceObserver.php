<?php

namespace App\Observers;

use App\Models\Company\DocumentSequence;

class DocumentSequenceObserver extends BaseActivityObserver
{
    /**
     * Handle the DocumentSequence "created" event.
     */
    public function created(DocumentSequence $documentSequence): void
    {
        $this->logCreated($documentSequence);
    }

    /**
     * Handle the DocumentSequence "updated" event.
     */
    public function updated(DocumentSequence $documentSequence): void
    {
        $this->logUpdated($documentSequence);
    }
}
