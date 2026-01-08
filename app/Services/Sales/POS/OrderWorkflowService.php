<?php

namespace App\Services\Sales\POS;

use App\Models\Sales\Order;
use Illuminate\Support\Facades\DB;

class OrderWorkflowService
{
    public function confirm(Order $order): void
    {
        $confirmedId = DB::table('order_statuses')->where('code', 'CONFIRMED')->value('id');

        if (!$confirmedId) {
            $confirmedId = DB::table('order_statuses')->insertGetId([
                'code' => 'CONFIRMED',
                'name' => 'Confirmed',
                'is_final' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $order->status_id = (int)$confirmedId;
        $order->confirmed_at = now();
        $order->save();
    }
}
