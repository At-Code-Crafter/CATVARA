<?php

namespace App\Http\Controllers\Admin\Sales\POS;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Sales\POS\CustomerSnapshotService;
use App\Services\Sales\POS\OrderTotalsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderDraftController extends Controller
{
    public function sync(Company $company, string $uuid, Request $request)
    {
        $order = Order::query()
            ->where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with('items')
            ->firstOrFail();

        $payload = $request->validate([
            'items' => 'array',

            'items.*.product_variant_id' => 'required|integer',
            'items.*.product_name' => 'required|string',
            'items.*.variant_description' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',

            'shipping_total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',

            'additional_charges' => 'nullable|array',
            'additional_charges.*.label' => 'required_with:additional_charges|string',
            'additional_charges.*.amount' => 'required_with:additional_charges|numeric|min:0',

            'payment_term_id' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($order, $payload) {

            // Header fields
            if (array_key_exists('shipping_total', $payload)) {
                $order->shipping_total = (float) ($payload['shipping_total'] ?? 0);
            }

            if (array_key_exists('notes', $payload)) {
                $order->notes = $payload['notes'];
            }

            if (array_key_exists('additional_charges', $payload)) {
                $order->additional_charges = $payload['additional_charges'] ?? null;
                $order->additional_total = $this->sumAdditional($payload['additional_charges'] ?? []);
            }

            if (array_key_exists('payment_term_id', $payload)) {
                app(CustomerSnapshotService::class)->applyPaymentTermSnapshot($order, $payload['payment_term_id']);
            }

            $order->save();

            // Items upsert by (order_id + product_variant_id)
            $incoming = collect($payload['items'] ?? []);

            foreach ($incoming as $row) {
                $variantId = (int) $row['product_variant_id'];

                $unit = (float) $row['unit_price'];
                $qty  = (int) $row['quantity'];
                $disc = (float) ($row['discount_amount'] ?? 0);
                $taxRate = (float) ($row['tax_rate'] ?? 0);

                $gross = $unit * $qty;
                $net = max(0, $gross - $disc);
                $taxAmount = ($taxRate > 0) ? ($net * $taxRate / 100) : 0;

                OrderItem::query()->updateOrCreate(
                    ['order_id' => $order->id, 'product_variant_id' => $variantId],
                    [
                        'product_name' => $row['product_name'],
                        'variant_description' => $row['variant_description'] ?? null,
                        'unit_price' => $unit,
                        'quantity' => $qty,
                        'discount_amount' => $disc,
                        'tax_rate' => $taxRate,
                        'line_total' => $net,
                        'tax_amount' => $taxAmount,
                    ]
                );
            }

            // Remove deleted lines
            $incomingIds = $incoming->pluck('product_variant_id')->map(fn($x) => (int)$x)->values()->all();

            if (count($incomingIds) === 0) {
                OrderItem::query()->where('order_id', $order->id)->delete();
            } else {
                OrderItem::query()
                    ->where('order_id', $order->id)
                    ->whereNotIn('product_variant_id', $incomingIds)
                    ->delete();
            }

            app(OrderTotalsService::class)->recalculateAndPersist($order);
        });

        $order->refresh();

        return response()->json([
            'ok' => true,
            'order' => [
                'uuid' => $order->uuid,
                'subtotal' => (float)$order->subtotal,
                'discount_total' => (float)$order->discount_total,
                'tax_total' => (float)$order->tax_total,
                'shipping_total' => (float)$order->shipping_total,
                'additional_total' => (float)$order->additional_total,
                'grand_total' => (float)$order->grand_total,
                'due_date' => $order->due_date,
            ],
        ]);
    }

    public function updateSellTo(Company $company, string $uuid, Request $request)
    {
        $request->validate(['customer_id' => 'nullable|integer']);

        $order = Order::query()
            ->where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $order->customer_id = $request->customer_id;

        // Defaults:
        // - Payment term from customer.payment_term_id
        // - Billing address = customer's default billing
        // - Shipping address = customer's default shipping
        if ($order->customer_id) {
            app(CustomerSnapshotService::class)->applyDefaultsFromCustomer($order, $order->customer_id);
        } else {
            // if customer removed, keep existing snapshots but clear term snapshot
            app(CustomerSnapshotService::class)->applyPaymentTermSnapshot($order, null);
        }

        $order->save();
        app(OrderTotalsService::class)->recalculateAndPersist($order);

        return response()->json(['ok' => true, 'due_date' => $order->due_date]);
    }

    public function updateBillTo(Company $company, string $uuid, Request $request)
    {
        $request->validate(['bill_to_customer_id' => 'nullable|integer']);

        $order = Order::query()
            ->where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        if (!$request->bill_to_customer_id) {
            // revert to sell-to (if exists)
            if ($order->customer_id) {
                $billing = app(CustomerSnapshotService::class)->getDefaultAddressSnapshot($order->company_id, $order->customer_id, 'BILLING');
                $order->billing_address = $billing;
            } else {
                $order->billing_address = null;
            }
        } else {
            $order->billing_address = app(CustomerSnapshotService::class)
                ->getDefaultAddressSnapshot($order->company_id, (int)$request->bill_to_customer_id, 'BILLING');
        }

        $order->save();

        return response()->json(['ok' => true]);
    }

    public function updatePaymentTerm(Company $company, string $uuid, Request $request)
    {
        $request->validate(['payment_term_id' => 'nullable|integer']);

        $order = Order::query()
            ->where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        app(CustomerSnapshotService::class)->applyPaymentTermSnapshot($order, $request->payment_term_id);
        $order->save();

        return response()->json(['ok' => true, 'due_date' => $order->due_date]);
    }

    private function sumAdditional(array $charges): float
    {
        $sum = 0.0;
        foreach ($charges as $c) {
            $sum += (float)($c['amount'] ?? 0);
        }
        return $sum;
    }
}
