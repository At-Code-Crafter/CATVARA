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

    public function index(Company $company)
    {
        return view('catvara.accounting.invoices.index');
    }

    public function data(Request $request, Company $company)
    {
        $query = Invoice::where('company_id', $company->id)
            ->with(['customer', 'status', 'paymentStatus', 'currency']);

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->editColumn('invoice_number', fn($i) => '<span class="font-bold text-slate-700">'.e($i->invoice_number).'</span>')
            ->addColumn('customer_name', fn($i) => $i->customer->display_name ?? 'N/A')
            ->editColumn('status', function($i) {
                $code = $i->status->code ?? '';
                $color = match($code) {
                    'DRAFT' => 'warning',
                    'ISSUED' => 'info',
                    'PAID' => 'success',
                    'VOIDED' => 'danger',
                    default => 'secondary'
                };
                return '<span class="badge badge-'.$color.'">'.e($i->status->name ?? 'Draft').'</span>';
            })
            ->editColumn('grand_total', function($i) {
                return '<span class="font-bold text-slate-900">'.\money($i->grand_total, $i->currency->code).'</span>';
            })
            ->editColumn('created_at', fn($i) => $i->created_at->format('M d, Y'))
            ->addColumn('actions', function($i) {
                $showUrl = company_route('accounting.invoices.show', ['invoice' => $i->uuid]);
                $printUrl = company_route('accounting.invoices.print', ['invoice' => $i->uuid]);
                
                return '
                    <div class="flex items-center gap-2">
                        <a href="'.$showUrl.'" class="btn btn-xs btn-white"><i class="fas fa-eye"></i></a>
                        <a href="'.$printUrl.'" target="_blank" class="btn btn-xs btn-white"><i class="fas fa-print"></i></a>
                    </div>
                ';
            })
            ->rawColumns(['invoice_number', 'status', 'grand_total', 'actions'])
            ->make(true);
    }

    public function show(Company $company, $uuid)
    {
        $invoice = Invoice::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items', 'customer', 'addresses', 'currency', 'status'])
            ->firstOrFail();

        return view('catvara.accounting.invoices.show', compact('invoice'));
    }

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
                'redirect_url' => company_route('accounting.invoices.show', ['invoice' => $invoice->uuid]), 
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function post(Company $company, $uuid)
    {
        $invoice = Invoice::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        try {
            $this->invoiceService->post($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Invoice posted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to post invoice: ' . $e->getMessage()
            ], 422);
        }
    }

    public function print(Company $company, $uuid)
    {
        $invoice = Invoice::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items', 'company', 'customer', 'addresses', 'currency'])
            ->firstOrFail();

        return view('catvara.accounting.invoices.print', compact('invoice'));
    }
}
