<?php

namespace App\Observers;

use App\Models\Accounting\PaymentTerm;
use App\Models\Common\ActivityLog;

class PaymentTermObserver
{
    /**
     * Handle the PaymentTerm "created" event.
     */
    public function created(PaymentTerm $paymentTerm): void
    {
        ActivityLog::log(
            $paymentTerm,
            'payment_term_created',
            "Payment Term '{$paymentTerm->name}' was created",
            [
                'payment_term_id' => $paymentTerm->id,
                'company_id' => $paymentTerm->company_id,
                'code' => $paymentTerm->code,
                'name' => $paymentTerm->name,
                'due_days' => $paymentTerm->due_days,
                'is_active' => $paymentTerm->is_active,
            ]
        );
    }

    /**
     * Handle the PaymentTerm "updated" event.
     */
    public function updated(PaymentTerm $paymentTerm): void
    {
        $changes = $paymentTerm->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'payment_term_id' => $paymentTerm->id,
            'company_id' => $paymentTerm->company_id,
            'code' => $paymentTerm->code,
            'name' => $paymentTerm->name,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $paymentTerm->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $paymentTerm,
            'payment_term_updated',
            "Payment Term '{$paymentTerm->name}' was updated",
            $properties
        );
    }
}
