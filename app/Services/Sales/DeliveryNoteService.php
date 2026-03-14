<?php

namespace App\Services\Sales;

use App\Models\Sales\DeliveryNote;
use App\Models\Sales\DeliveryNoteItem;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\OrderItemBox;
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveryNoteService
{
    public function __construct(
        protected DocumentNumberService $docNumberService,
        protected SalesDocumentService $salesDocService
    ) {}

    /**
     * Create a Delivery Note from a Sales Order
     */
    public function createFromOrder(Order $order, array $itemsData): DeliveryNote
    {
        return DB::transaction(function () use ($order, $itemsData) {
            $dn = DeliveryNote::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $order->company_id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'shipping_customer_id' => $order->shipping_customer_id ?? $order->customer_id,
                'delivery_note_number' => $this->docNumberService->generate(
                    companyId: $order->company_id,
                    documentType: 'DELIVERY_NOTE',
                    channel: 'SALES',
                    year: now()->year
                ),
                'status' => DeliveryNote::STATUS_SHIPPED,
                'shipped_at' => now(),
                'created_by' => Auth::id(),
            ]);

            // Sync addresses
            $this->salesDocService->syncAddressSnapshots($dn, $order->customer, $order->shippingCustomer ?? $order->customer);

            foreach ($itemsData as $row) {
                $orderItem = OrderItem::find($row['order_item_id']);
                if (! $orderItem || $orderItem->order_id !== $order->id) {
                    continue;
                }

                $qtyToShip = (float) $row['quantity'];
                if ($qtyToShip <= 0) {
                    continue;
                }

                DeliveryNoteItem::create([
                    'delivery_note_id' => $dn->id,
                    'order_item_id' => $orderItem->id,
                    'product_variant_id' => $orderItem->product_variant_id,
                    'quantity' => $qtyToShip,
                ]);

                // Update fulfilled quantity on order item
                $orderItem->increment('fulfilled_quantity', $qtyToShip);
            }

            return $dn;
        });
    }

    /**
     * Mark a Delivery Note as delivered
     */
    public function markAsDelivered(DeliveryNote $dn): bool
    {
        return $dn->update([
            'status' => DeliveryNote::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Delete a Delivery Note and revert fulfillment
     */
    public function delete(DeliveryNote $dn): bool
    {
        return DB::transaction(function () use ($dn) {
            foreach ($dn->items as $item) {
                if ($item->orderItem) {
                    $item->orderItem->decrement('fulfilled_quantity', $item->quantity);
                }
            }

            // Delete box assignments
            OrderItemBox::where('delivery_note_id', $dn->id)->delete();

            // Sync addresses deletion if they are HasMany
            $dn->addresses()->delete();

            return $dn->delete();
        });
    }
}
