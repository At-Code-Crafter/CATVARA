<?php

namespace App\Observers;

use App\Models\Accounting\PaymentApplication;

class PaymentApplicationObserver extends BaseActivityObserver
{
    /**
     * Handle the PaymentApplication "created" event.
     */
    public function created(PaymentApplication $paymentApplication): void
    {
        $this->logCreated($paymentApplication);
    }

    /**
     * Handle the PaymentApplication "updated" event.
     */
    public function updated(PaymentApplication $paymentApplication): void
    {
        $this->logUpdated($paymentApplication);
    }
}
