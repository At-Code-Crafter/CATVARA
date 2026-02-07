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
        return DB::transaction(function () use ($order) {
            $order->loadMissing(['items', 'currency', 'paymentTerm', 'customer', 'shippingCustomer', 'addresses']);

            $draftStatus = InvoiceStatus::where('code', 'DRAFT')->first();
            if (!$draftStatus) {
                $draftStatus = InvoiceStatus::create(['code' => 'DRAFT', 'name' => 'Draft']);
            }

            $invoiceNumber = $this->docNumberService->generate(
                companyId: $order->company_id,
                documentType: 'INVOICE',
                channel: ($order->source ?? 'SALES') === 'POS' ? 'POS' : 'SALES',
                year: now()->year
            );

            $invoice = Invoice::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $order->company_id,
                'invoice_number' => $invoiceNumber,
                'customer_id' => $order->customer_id,
                'shipping_customer_id' => $order->shipping_customer_id,
                'status_id' => $draftStatus->id,
                'payment_status_id' => $order->payment_status_id,
                'source_type' => get_class($order),
                'source_id' => $order->id,
                'currency_id' => $order->currency_id,
                'base_currency_id' => $order->base_currency_id,
                'fx_rate' => $order->fx_rate,
                'tax_group_id' => $order->tax_group_id,
                'payment_term_id' => $order->payment_term_id,
                'payment_term_name' => $order->payment_term_name,
                'payment_due_days' => $order->payment_due_days,
                'due_date' => $order->due_date,
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'global_discount_percent' => $order->global_discount_percent,
                'global_discount_amount' => $order->global_discount_amount,
                'shipping_total' => $order->shipping_total,
                'shipping_tax_total' => $order->shipping_tax_total,
                'tax_total' => $order->tax_total,
                'rounding_total' => $order->rounding_total,
                'grand_total' => $order->grand_total,
                'notes' => $order->notes,
                'created_by' => Auth::id(),
            ]);

            // Copy Address Snapshots from Order to Invoice
            foreach ($order->addresses as $address) {
                $invoice->addresses()->create($address->replicate(['addressable_id', 'addressable_type'])->toArray());
            }

            // Sync items
            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'is_custom' => $item->is_custom,
                    'custom_sku' => $item->custom_sku,
                    'product_name' => $item->product_name,
                    'variant_description' => $item->variant_description,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,

                    'line_subtotal' => $item->line_subtotal,
                    'discount_amount' => $item->discount_amount,
                    'discount_percent' => $item->discount_percent,
                    'line_discount_total' => $item->line_discount_total,

                    'tax_group_id' => $item->tax_group_id,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,

                    'line_total' => $item->line_total,
                ]);
            }

            return $invoice;
        });
    }

    /**
     * Post an Invoice (Transition from DRAFT to ISSUED)
     */
    public function post(Invoice $invoice): void
    {
        if ($invoice->posted_at) {
            throw new \Exception('Invoice is already posted.');
        }

        $issuedStatus = InvoiceStatus::where('code', 'ISSUED')->first();
        if (!$issuedStatus) {
            $issuedStatus = InvoiceStatus::create(['code' => 'ISSUED', 'name' => 'Issued']);
        }

        $invoice->update([
            'status_id' => $issuedStatus->id,
            'posted_at' => Carbon::now(),
            'posted_by' => Auth::id(),
            'issued_at' => Carbon::now(),
        ]);

        // Future: trigger General Ledger entries or tax reporting integrations here.
    }
}
