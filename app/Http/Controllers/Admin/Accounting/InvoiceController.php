<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceStatus;
use App\Models\Company\Company;
use App\Models\Sales\Order;
use App\Models\Sales\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function storeFromOrder(Request $request, Company $company, $orderUuid)
    {
        $order = Order::where('company_id', $company->id)
            ->where('uuid', $orderUuid)
            ->with(['items.productVariant', 'addresses'])
            ->firstOrFail();

        // 1. Check if order is already invoiced? 
        // Logic: You can invoice multiple times or just once. 
        // For now, allow multiple but maybe warn? Or just proceed.

        DB::beginTransaction();
        try {
            // Find or Create 'DRAFT' invoice status
            $status = InvoiceStatus::firstOrCreate(
                ['code' => 'DRAFT'],
                ['name' => 'Draft', 'is_active' => true]
            );

            // Create Invoice Header
            $invoice = Invoice::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'customer_id' => $order->customer_id,
                'status_id' => $status->id,
                'invoice_number' => $this->generateInvoiceNumber($company),
                'source_type' => Order::class,
                'source_id' => $order->id,
                'currency_id' => $order->currency_id,
                'payment_term_id' => $order->payment_term_id,
                'payment_due_days' => $order->payment_due_days,
                'due_date' => $order->due_date,

                // Totals
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total + $order->shipping_tax_total, // Combine shipping tax if needed?
                'discount_total' => $order->discount_total,
                'shipping_amount' => $order->shipping_total,
                'grand_total' => $order->grand_total,

                'issued_at' => Carbon::now(),
                'created_by' => auth()->id(),
            ]);

            // Copy Items
            foreach ($order->items as $item) {
                $invoice->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    // Description often copied or generated
                    'description' => $item->product_name . ($item->variant_description ? ' - ' . $item->variant_description : ''),
                    'discount_amount' => $item->discount_amount,
                    'tax_rate' => $item->tax_rate,
                    'tax_amount' => $item->tax_amount,
                    'line_total' => $item->line_total,
                ]);
            }

            // Copy Addresses
            // Order addresses are MorphMany. Invoice addresses are HasMany (based on model provided).
            // Check InvoiceAddress model if it has 'type'. Assuming yes.

            // Re-read addresses from order
            foreach ($order->addresses as $addr) {
                // If InvoiceAddress table structure matches Order Address roughly
                $invoice->addresses()->create([
                    'type' => $addr->type, // BILLING, SHIPPING
                    'name' => $addr->name, // If exists in source
                    'address_line_1' => $addr->address_line_1,
                    'address_line_2' => $addr->address_line_2,
                    'city' => $addr->city,
                    'state_id' => $addr->state_id,
                    'zip_code' => $addr->zip_code,
                    'country_id' => $addr->country_id,
                    'phone' => $addr->phone,
                    'email' => $addr->email,
                ]);
            }

            // Update Order Status to INVOICED
            $invoicedStatus = OrderStatus::firstOrCreate(
                ['code' => 'INVOICED'],
                ['name' => 'Invoiced', 'is_active' => true]
            );
            $order->update(['status_id' => $invoicedStatus->id]);

            // Log action? "Who took action and when"
            // Assuming simplified logging via `created_by` on Invoice and `updated_at` on Order.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully.',
                'invoice_uuid' => $invoice->uuid,
                'redirect_url' => '', // If you have an invoice show page
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateInvoiceNumber($company)
    {
        $prefix = 'INV-' . Carbon::now()->format('Ymd') . '-';
        $last = Invoice::where('company_id', $company->id)
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $lastNum = intval(substr($last->invoice_number, strlen($prefix)));
            return $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '0001';
    }

    public function print(Company $company, $uuid)
    {
        $invoice = Invoice::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items', 'company.detail', 'customer', 'addresses.country', 'currency'])
            ->firstOrFail();

        return view('theme.adminlte.accounting.invoices.print', compact('invoice'));
    }
}
