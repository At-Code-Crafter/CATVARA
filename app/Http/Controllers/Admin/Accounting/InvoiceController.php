<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceStatus;
use App\Models\Company\Company;
use App\Models\Sales\Order;
use App\Models\Sales\OrderStatus;
use App\Services\Sales\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function storeFromOrder(Request $request, Company $company, $orderUuid)
    {
        $order = Order::where('company_id', $company->id)
            ->where('uuid', $orderUuid)
            ->firstOrFail();

        try {
            $invoice = $this->invoiceService->createFromOrder($order);

            // Update Order Status to INVOICED
            $invoicedStatus = OrderStatus::where('code', 'INVOICED')->first();
            if ($invoicedStatus) {
                $order->update(['status_id' => $invoicedStatus->id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully.',
                'invoice_uuid' => $invoice->uuid,
                'redirect_url' => '', 
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }


    /* Numbering handled by InvoiceService */

    public function print(Company $company, $uuid)
    {
        $invoice = Invoice::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items', 'company.detail', 'customer', 'addresses.country', 'currency'])
            ->firstOrFail();

        return view('theme.adminlte.accounting.invoices.print', compact('invoice'));
    }
}
