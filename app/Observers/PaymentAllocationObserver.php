<?php

namespace App\Observers;

use App\Models\Accounting\PaymentAllocation;

class PaymentAllocationObserver extends BaseActivityObserver
{
    /**
     * Handle the PaymentAllocation "created" event.
     */
    public function created(PaymentAllocation $paymentAllocation): void
    {
        $this->logCreated($paymentAllocation);
    }

    /**
     * Handle the PaymentAllocation "updated" event.
     */
    public function updated(PaymentAllocation $paymentAllocation): void
    {
        $this->logUpdated($paymentAllocation);
    }
}
