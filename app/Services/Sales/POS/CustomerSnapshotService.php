<?php

namespace App\Services\Sales\POS;

use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class CustomerSnapshotService
{
    public function applyDefaultsFromCustomer(Order $order, int $customerId): void
    {
        // Payment term default from customer
        $termId = DB::table('customers')->where('id', $customerId)->value('payment_term_id');
        $this->applyPaymentTermSnapshot($order, $termId);

        // Default address snapshots
        $order->billing_address = $this->getDefaultAddressSnapshot($order->company_id, $customerId, 'BILLING');
        $order->shipping_address = $this->getDefaultAddressSnapshot($order->company_id, $customerId, 'SHIPPING');
    }

    public function applyPaymentTermSnapshot(Order $order, $termId): void
    {
        if (!$termId) {
            $order->payment_term_id = null;
            $order->payment_term_name = null;
            $order->payment_due_days = null;
            $order->due_date = null;
            return;
        }

        $term = DB::table('payment_terms')
            ->select(['id', 'name', 'due_days'])
            ->where('id', (int)$termId)
            ->first();

        if (!$term) return;

        $order->payment_term_id = (int)$term->id;
        $order->payment_term_name = (string)$term->name;
        $order->payment_due_days = (int)$term->due_days;
        $order->due_date = now()->addDays((int)$term->due_days)->toDateString();
    }

    public function getDefaultAddressSnapshot(int $companyId, int $customerId, string $type): ?array
    {
        $addr = DB::table('customer_addresses')
            ->where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('type', $type)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if (!$addr) return null;

        return [
            'customer_id' => $customerId,
            'type' => $type,
            'contact_name' => $addr->contact_name,
            'phone' => $addr->phone,
            'address_line_1' => $addr->address_line_1,
            'address_line_2' => $addr->address_line_2,
            'city' => $addr->city,
            'state' => $addr->state,
            'postal_code' => $addr->postal_code,
            'country_code' => $addr->country_code,
        ];
    }
}
