<?php

namespace App\Services\Sales;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceStatus;
use App\Models\Sales\Order;
use App\Services\Common\DocumentNumberService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    public function __construct(
        protected DocumentNumberService $docNumberService,
        protected SalesDocumentService $salesDocService
    ) {}

    /**
     * Create an Invoice from a Sales Order
     */
    public function createFromOrder(Order $order): Invoice
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order->loadMissing(['items.productVariant', 'addresses']);

            $status = InvoiceStatus::firstOrCreate(
                ['code' => 'DRAFT'],
                ['name' => 'Draft', 'is_active' => true]
            );

            $invoice = Invoice::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $order->company_id,
                'customer_id' => $order->customer_id,
                'status_id' => $status->id,
                'invoice_number' => $this->docNumberService->generate(
                    companyId: $order->company_id,
                    documentType: 'INVOICE',
                    channel: 'SALES'
                ),
                'source_type' => Order::class,
                'source_id' => $order->id,
                'currency_id' => $order->currency_id,
                'payment_term_id' => $order->payment_term_id,
                'payment_due_days' => $order->payment_due_days,
                'due_date' => $order->due_date,

                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total + ($order->shipping_tax_total ?? 0),
                'discount_total' => $order->discount_total ?? 0,
                'shipping_amount' => $order->shipping_total ?? 0,
                'grand_total' => $order->grand_total,

                'issued_at' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            // Sync items
            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'description' => $item->product_name . ($item->variant_description ? ' - ' . $item->variant_description : ''),
                    'discount_amount' => $item->discount_amount ?? 0,
                    'tax_rate' => $item->tax_rate ?? 0,
                    'tax_amount' => $item->tax_amount ?? 0,
                    'line_total' => $item->line_total,
                ]);
            }

            // Sync address snapshots using centralized service
            $billAddress = $order->addresses->where('type', 'BILLING')->first();
            $shipAddress = $order->addresses->where('type', 'SHIPPING')->first();
            
            // Note: Invoices usually just care about Billing, but some systems use both.
            // Documentation for syncAddressSnapshots suggests it takes billTo and shipTo Customers.
            // Since we already have Order Addresses, we can either map them or use the Customer models if available.
            
            if ($order->customer) {
                $this->salesDocService->syncAddressSnapshots($invoice, $order->customer, $order->shippingCustomer ?? $order->customer);
            }

            return $invoice;
        });
    }
}
