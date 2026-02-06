<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use App\Models\Sales\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        ActivityLog::log(
            $order,
            'order_created',
            "Order '{$order->order_number}' was created",
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'company_id' => $order->company_id,
                'customer_id' => $order->customer_id,
                'status_id' => $order->status_id,
                'payment_status_id' => $order->payment_status_id,
                'currency_id' => $order->currency_id,
                'grand_total' => $order->grand_total,
                'source' => $order->source,
                'source_reference' => $order->source_reference,
            ]
        );
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'company_id' => $order->company_id,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $order->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $order,
            'order_updated',
            "Order '{$order->order_number}' was updated",
            $properties
        );
    }
}
