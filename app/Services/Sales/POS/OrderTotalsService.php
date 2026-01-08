<?php

namespace App\Services\Sales\POS;

use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class OrderTotalsService
{
    public function recalculateAndPersist(Order $order): void
    {
        $rows = DB::table('order_items')
            ->where('order_id', $order->id)
            ->get(['unit_price', 'quantity', 'discount_amount', 'tax_amount']);

        $subtotalGross = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;

        foreach ($rows as $r) {
            $subtotalGross += ((float)$r->unit_price * (int)$r->quantity);
            $discountTotal += (float)$r->discount_amount;
            $taxTotal += (float)$r->tax_amount;
        }

        $order->subtotal = $subtotalGross;
        $order->discount_total = $discountTotal;
        $order->tax_total = $taxTotal;

        $shipping = (float)($order->shipping_total ?? 0);
        $shippingTax = (float)($order->shipping_tax_total ?? 0);
        $additional = (float)($order->additional_total ?? 0);

        $order->grand_total = max(0, ($subtotalGross - $discountTotal) + $taxTotal + $shipping + $shippingTax + $additional);
        $order->save();
    }
}
