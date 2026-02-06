<?php

namespace App\Observers;

use App\Models\Accounting\PaymentMethod;
use App\Models\Common\ActivityLog;

class PaymentMethodObserver
{
    /**
     * Handle the PaymentMethod "created" event.
     */
    public function created(PaymentMethod $paymentMethod): void
    {
        ActivityLog::log(
            $paymentMethod,
            'payment_method_created',
            "Payment Method '{$paymentMethod->name}' was created",
            [
                'payment_method_id' => $paymentMethod->id,
                'company_id' => $paymentMethod->company_id,
                'code' => $paymentMethod->code,
                'name' => $paymentMethod->name,
                'type' => $paymentMethod->type,
                'is_active' => $paymentMethod->is_active,
                'allow_refund' => $paymentMethod->allow_refund,
                'requires_reference' => $paymentMethod->requires_reference,
            ]
        );
    }

    /**
     * Handle the PaymentMethod "updated" event.
     */
    public function updated(PaymentMethod $paymentMethod): void
    {
        $changes = $paymentMethod->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'payment_method_id' => $paymentMethod->id,
            'company_id' => $paymentMethod->company_id,
            'code' => $paymentMethod->code,
            'name' => $paymentMethod->name,
            'type' => $paymentMethod->type,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $paymentMethod->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $paymentMethod,
            'payment_method_updated',
            "Payment Method '{$paymentMethod->name}' was updated",
            $properties
        );
    }
}
