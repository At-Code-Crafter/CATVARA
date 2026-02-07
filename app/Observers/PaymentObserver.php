<?php

namespace App\Observers;

use App\Models\Accounting\Payment;

class PaymentObserver extends BaseActivityObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $this->logCreated($payment);
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        $this->logUpdated($payment);
    }
}
