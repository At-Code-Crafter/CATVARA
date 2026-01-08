<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Accounting\PaymentTerm;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItem;
use App\Models\Sales\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function index()
    {
        $companyId = active_company_id();
        $statuses = OrderStatus::all();
        $customers = Customer::where('company_id', $companyId)->orderBy('display_name')->get();

        return view('theme.adminlte.sales.orders.index', compact('statuses', 'customers'));
    }

    public function data(Request $request)
    {
        $companyId = active_company_id();
        $query = Order::where('orders.company_id', $companyId)
            ->with(['customer', 'status']);

        // Filters
        if ($request->filled('status_id')) {
            $query->where('orders.status_id', $request->status_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('orders.customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('orders.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('orders.created_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->editColumn('order_number', function ($order) {
                return '<span class="font-weight-bold">'.$order->order_number.'</span>';
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('M d, Y');
            })
            ->addColumn('customer_name', function ($order) {
                return $order->customer->display_name ?? 'N/A';
            })
            ->editColumn('status', function ($order) {
                $color = 'secondary';
                if ($order->status->code === 'CONFIRMED') {
                    $color = 'success';
                }
                if ($order->status->code === 'DRAFT') {
                    $color = 'warning';
                }

                return '<span class="badge badge-'.$color.'">'.$order->status->name.'</span>';
            })
            ->editColumn('grand_total', function ($order) {
                return '<span class="font-weight-bold text-dark">'.number_format($order->grand_total, 2).'</span>';
            })
            ->addColumn('actions', function ($order) {
                $printUrl = company_route('sales-orders.print', ['order' => $order->uuid]);
                $showUrl = company_route('sales-orders.show', ['sales_order' => $order->id]); // Assuming show exists

                return '
                    <div class="btn-group">
                        <a href="'.$showUrl.'" class="btn btn-xs btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="'.$printUrl.'" class="btn btn-xs btn-outline-primary" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                    </div>
                ';
            })
            ->rawColumns(['order_number', 'status', 'grand_total', 'actions'])
            ->make(true);
    }

    public function create()
    {

        if (request()->has('sell_to') && request()->has('bill_to')) {

            $sellToCustomer = Customer::where('company_id', request()->company->id)->where('uuid', request('sell_to'))->first();
            $billToCustomer = Customer::where('company_id', request()->company->id)->where('uuid', request('bill_to'))->first();
            return view('theme.adminlte.sales.orders.create_pos', compact('sellToCustomer', 'billToCustomer'));

        }

        return view('theme.adminlte.sales.orders.create');
    }

    function loadPaymentTerms(){

        $paymentTerms = request()->company->paymentTerms->map(function ($paymentTerm) {
            return [
                'id' => $paymentTerm->id,
                'name' => $paymentTerm->name,
                'due_days' => $paymentTerm->due_days,
            ];
        });

        return response()->json($paymentTerms);
    }

}
