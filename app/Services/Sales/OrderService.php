<?php

namespace App\Services\Sales;

use App\Models\Sales\{
    Order,
    OrderItem,
    OrderStatus,
    Quote
};
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderService
{
    public function __construct(
        protected DocumentNumberService $docService,
        protected SalesCalculationService $calcService,
        protected SalesDocumentService $salesDocService
    ) {}

    /**
     * Create order from Quote / POS / Web
     */
    public function create(array $data): Order
    {
        $statusId = OrderStatus::where('code', 'DRAFT')->value('id');

        $termSnapshot = $this->salesDocService->resolvePaymentTermSnapshot($data['payment_term_id'] ?? null);

        $dueDate = !empty($data['due_date'])
            ? Carbon::parse($data['due_date'])
            : ($termSnapshot['payment_due_days'] > 0 ? Carbon::now()->addDays($termSnapshot['payment_due_days']) : null);

        return Order::create([
            'uuid' => Str::uuid(),
            'company_id' => $data['company_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'status_id' => $statusId,

            'source' => $data['source'] ?? null,
            'source_id' => $data['source_id'] ?? null,

            'order_number' => $this->docService->generate(
                companyId: $data['company_id'],
                documentType: 'ORDER',
                channel: 'SALES',
                year: now()->year
            ),

            'currency_id' => $data['currency_id'],

            // Payment term snapshot
            'payment_term_id' => $termSnapshot['payment_term_id'],
            'payment_term_name' => $termSnapshot['payment_term_name'],
            'payment_due_days' => $termSnapshot['payment_due_days'],
            'due_date' => $dueDate,

            'created_by' => $data['user_id'] ?? Auth::id(),
        ]);
    }

    /**
     * Create order from an accepted Quote
     */
    public function createFromQuote(Quote $quote): Order
    {
        return DB::transaction(function () use ($quote) {

            $quote->loadMissing(['items', 'customer']);

            // Create the order
            $order = $this->create([
                'company_id' => $quote->company_id,
                'customer_id' => $quote->customer_id,
                'source' => 'QUOTE',
                'source_id' => $quote->id,
                'currency_id' => $quote->currency_id,
                'payment_term_id' => $quote->payment_term_id,
                'user_id' => Auth::id(),
            ]);

            // Sync addresses from Quote if they exist, or from Customer
            $this->salesDocService->syncAddressSnapshots($order, $quote->customer);

            // Prepare items for calculation
            $itemsPayload = $quote->items->map(function ($item) {
                return [
                    'type' => $item->product_variant_id ? 'variant' : 'custom',
                    'variant_id' => $item->productVariant?->uuid,
                    'custom_name' => $item->product_name,
                    'unit_price' => $item->unit_price,
                    'qty' => $item->quantity,
                    'tax_group_id' => $item->tax_group_id,
                ];
            })->toArray();

            $calc = $this->calcService->calculate($quote->company_id, [
                'items' => $itemsPayload,
                'customer_id' => $quote->customer_id,
                'tax_group_id' => $quote->tax_group_id,
            ]);

            // Create Order items from calculation results
            foreach ($calc['items_for_db'] as $row) {
                $order->items()->create($row);
            }

            // Final total update
            $order->update([
                'subtotal' => $calc['subtotal'],
                'discount_total' => $calc['discount_total'] ?? 0,
                'tax_total' => $calc['tax_total'],
                'grand_total' => $calc['grand_total'],
            ]);

            return $order;
        });
    }

    /**
     * Confirm order (locks pricing + totals)
     */
    public function confirm(Order $order): Order
    {
        $confirmedStatusId = OrderStatus::where('code', 'CONFIRMED')->value('id');

        return DB::transaction(function () use ($order, $confirmedStatusId) {

            $order->load(['items', 'customer']);

            if ($order->items->isEmpty()) {
                throw new \RuntimeException('Cannot confirm empty order.');
            }

            // Re-calculate to be sure
            $itemsPayload = $order->items->map(function ($item) {
                return [
                    'type' => $item->product_variant_id ? 'variant' : 'custom',
                    'variant_id' => $item->productVariant?->uuid,
                    'custom_name' => $item->product_name,
                    'unit_price' => $item->unit_price,
                    'qty' => $item->quantity,
                    'tax_group_id' => $item->tax_group_id,
                    'discount_percent' => $item->discount_percent ?? 0,
                ];
            })->toArray();

            $calc = $this->calcService->calculate($order->company_id, [
                'items' => $itemsPayload,
                'customer_id' => $order->customer_id,
                'tax_group_id' => $order->tax_group_id,
                'global_discount_percent' => $order->global_discount_percent,
                'global_discount_amount' => $order->global_discount_amount,
                'shipping' => $order->shipping_total,
                'additional' => 0,
            ]);

            $order->update([
                'subtotal' => $calc['subtotal'],
                'tax_total' => $calc['tax_total'],
                'discount_total' => $calc['discount_total'] ?? 0,
                'grand_total' => $calc['grand_total'],
                'status_id' => $confirmedStatusId,
                'confirmed_at' => now(),
            ]);

            return $order;
        });
    }
}
