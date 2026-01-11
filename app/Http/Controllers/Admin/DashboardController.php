<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Customer\Customer;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\Store;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Order;
use App\Models\Sales\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $companyId = active_company_id();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Get order status IDs
        $draftStatus = OrderStatus::where('code', 'DRAFT')->first();
        $confirmedStatus = OrderStatus::where('code', 'CONFIRMED')->first();

        // Order Statistics
        $stats = [
            // Orders
            'total_orders' => Order::where('company_id', $companyId)->count(),
            'new_orders_today' => Order::where('company_id', $companyId)
                ->whereDate('created_at', $today)
                ->count(),
            'pending_orders' => Order::where('company_id', $companyId)
                ->where('status_id', $draftStatus?->id)
                ->count(),
            'confirmed_orders' => Order::where('company_id', $companyId)
                ->where('status_id', $confirmedStatus?->id)
                ->count(),
            'total_revenue' => Order::where('company_id', $companyId)
                ->where('status_id', $confirmedStatus?->id)
                ->sum('grand_total'),

            // Customers
            'total_customers' => Customer::where('company_id', $companyId)->count(),
            'new_customers_month' => Customer::where('company_id', $companyId)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),

            // Products
            'total_products' => Product::where('company_id', $companyId)->count(),
            'active_products' => Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->count(),

            // Inventory
            'total_warehouses' => Warehouse::where('company_id', $companyId)->count(),
            'total_stores' => Store::where('company_id', $companyId)->count(),
            'pending_transfers' => InventoryTransfer::where('company_id', $companyId)
                ->whereHas('status', fn($q) => $q->where('code', 'PENDING'))
                ->count(),

            // Status IDs for links
            'draft_status_id' => $draftStatus?->id,
            'confirmed_status_id' => $confirmedStatus?->id,
        ];

        return view('theme.adminlte.dashboard', compact('stats'));
    }

    public function redirectToCompanyDashboard()
    {
        // Uses helper active_company() you already asked me to implement
        $company = active_company();

        if (!$company) {
            return redirect()->route('company.select');
        }

        return redirect()->route('company.dashboard', ['company' => $company->uuid]);
    }
}
