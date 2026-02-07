<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Services\Sales\InvoiceService;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use Illuminate\Support\Facades\DB;
use App\Models\Pricing\Currency;
use App\Models\Tax\TaxGroup;
use Illuminate\Support\Str;
use App\Models\Accounting\PaymentStatus;

class VerifyInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Invoice Generation Logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $company = Company::first();
        if (!$company) {
            $this->error("No Company found.");
            return;
        }

        $customer = Customer::where('company_id', $company->id)->first();
        if (!$customer) {
            $customer = Customer::create([
                'company_id' => $company->id,
                'display_name' => 'Test Customer',
                'type' => 'INDIVIDUAL'
            ]);
        }

        $currency = Currency::first();
        $taxGroup = TaxGroup::where('company_id', $company->id)->first();

        DB::transaction(function () use ($company, $customer, $currency, $taxGroup) {
            $this->info("Creating Test Order...");
            
            $order = Order::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'status_id' => 1, 
                'currency_id' => $currency->id ?? 1,
                'base_currency_id' => $currency->id ?? 1,
                'fx_rate' => 1.0,
                'order_number' => 'ORD-TEST-' . rand(1000, 9999), 
                'subtotal' => 100,
                'discount_total' => 0,
                'global_discount_percent' => 0,
                'global_discount_amount' => 0,
                'shipping_total' => 0,
                'shipping_tax_total' => 0,
                'tax_total' => 0,
                'rounding_total' => 0,
                'grand_total' => 100,
                'source' => 'WEB',
                'payment_term_id' => $customer->payment_term_id,
                'payment_term_name' => 'Net 30', // Dummy
                'payment_due_days' => 30, // Dummy
                'payment_status_id' => PaymentStatus::where('code', 'UNPAID')->value('id') ?? 1,
            ]);
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => 'Test Product',
                'unit_price' => 100,
                'quantity' => 1,
                'line_total' => 100,
                'line_subtotal' => 100,
                'tax_group_id' => $taxGroup->id ?? null,
                'variant_description' => 'Test Variant',
            ]);
            
            $this->info("Order Created: {$order->order_number}");
            
            $invoiceService = app(InvoiceService::class);
            
            $this->info("Generating Invoice...");
            try {
                $invoice = $invoiceService->createFromOrder($order);
                
                $this->info("Invoice Created Successfully!");
                $this->info("Invoice ID: {$invoice->id}");
                $this->info("Invoice Number: {$invoice->invoice_number}");
                
                if (Str::contains($invoice->invoice_number, 'INV-')) {
                    $this->info("[PASS] Invoice Number format looks correct.");
                } else {
                    $this->error("[FAIL] Invoice Number format unexpected: {$invoice->invoice_number}");
                }

                if ($invoice->grand_total == 100) {
                    $this->info("[PASS] Grand Total matches.");
                } else {
                    $this->error("[FAIL] Grand Total mismatch.");
                }

                // Verify Payment Term Snapshot
                if ($invoice->payment_term_id == $order->payment_term_id) {
                     $this->info("[PASS] Payment Term ID snapped correctly.");
                } else {
                     $this->warn("[WARN] Payment Term ID mismatch (could be null).");
                }
                
                // Rollback to keep DB clean
                // DB::rollBack();
                // $this->info("Rolled back transaction.");
            } catch (\Exception $e) {
                $this->error("Error creating invoice: " . $e->getMessage());
                file_put_contents('verify_error.txt', $e->getMessage() . "\n" . $e->getTraceAsString());
            }
        });
    }
}
