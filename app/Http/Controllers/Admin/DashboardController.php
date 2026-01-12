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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $companyId = active_company_id();
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Date filter from request
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : null;

        // Get order status IDs
        $draftStatus = OrderStatus::where('code', 'DRAFT')->first();
        $confirmedStatus = OrderStatus::where('code', 'CONFIRMED')->first();

        // Base query builder helper for date filtering
        $applyDateFilter = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('created_at', '<=', $dateTo);
            }
            return $query;
        };

        // Order Statistics (with date filter)
        $ordersBase = Order::where('company_id', $companyId);
        $customersBase = Customer::where('company_id', $companyId);

        $stats = [
            // Orders
            'total_orders' => $applyDateFilter(clone $ordersBase)->count(),
            'new_orders_today' => Order::where('company_id', $companyId)
                ->whereDate('created_at', $today)
                ->count(),
            'pending_orders' => $applyDateFilter(clone $ordersBase)
                ->where('status_id', $draftStatus?->id)
                ->count(),
            'confirmed_orders' => $applyDateFilter(clone $ordersBase)
                ->where('status_id', $confirmedStatus?->id)
                ->count(),
            'total_revenue' => $applyDateFilter(clone $ordersBase)
                ->where('status_id', $confirmedStatus?->id)
                ->sum('grand_total'),

            // Customers
            'total_customers' => $applyDateFilter(clone $customersBase)->count(),
            'new_customers_month' => Customer::where('company_id', $companyId)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),

            // Products (not date filtered - shows all)
            'total_products' => Product::where('company_id', $companyId)->count(),
            'active_products' => Product::where('company_id', $companyId)
                ->where('is_active', true)
                ->count(),

            // Inventory (not date filtered - shows current state)
            'total_warehouses' => Warehouse::where('company_id', $companyId)->count(),
            'total_stores' => Store::where('company_id', $companyId)->count(),
            'pending_transfers' => InventoryTransfer::where('company_id', $companyId)
                ->whereHas('status', fn($q) => $q->where('code', 'PENDING'))
                ->count(),

            // Status IDs for links
            'draft_status_id' => $draftStatus?->id,
            'confirmed_status_id' => $confirmedStatus?->id,

            // Pass filter values back to view
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
        ];

        // ========== CHART DATA ==========

        // 1. Monthly Revenue Trend (Last 6 months) - Line Chart
        $monthlyRevenue = Order::where('company_id', $companyId)
            ->where('status_id', $confirmedStatus?->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(grand_total) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $revenueLabels = [];
        $revenueData = [];
        $orderCountData = [];

        // Fill in missing months with zeros
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->format('M Y');
            $revenueLabels[] = $monthLabel;

            $found = $monthlyRevenue->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });

            $revenueData[] = $found ? round((float) $found->revenue, 2) : 0;
            $orderCountData[] = $found ? (int) $found->order_count : 0;
        }

        // 2. Order Status Distribution - Donut Chart
        $ordersByStatus = Order::where('company_id', $companyId)
            ->select('status_id', DB::raw('COUNT(*) as count'))
            ->groupBy('status_id')
            ->with('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->status->name ?? 'Unknown',
                    'count' => $item->count,
                    'code' => $item->status->code ?? 'UNKNOWN',
                ];
            });

        // 3. Customer Types - Pie Chart
        $customersByType = Customer::where('company_id', $companyId)
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        // 4. Monthly Orders Trend (Last 6 months) - Bar Chart
        $monthlyOrders = Order::where('company_id', $companyId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $ordersLabels = [];
        $ordersData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $ordersLabels[] = $date->format('M');

            $found = $monthlyOrders->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });

            $ordersData[] = $found ? (int) $found->count : 0;
        }

        // 5. Top 5 Products by Order Count
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('orders.company_id', $companyId)
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Chart data arrays
        $charts = [
            'revenue' => [
                'labels' => $revenueLabels,
                'data' => $revenueData,
                'orders' => $orderCountData,
            ],
            'orderStatus' => [
                'labels' => $ordersByStatus->pluck('label')->toArray(),
                'data' => $ordersByStatus->pluck('count')->toArray(),
                'codes' => $ordersByStatus->pluck('code')->toArray(),
            ],
            'customerTypes' => [
                'labels' => $customersByType->pluck('type')->toArray(),
                'data' => $customersByType->pluck('count')->toArray(),
            ],
            'monthlyOrders' => [
                'labels' => $ordersLabels,
                'data' => $ordersData,
            ],
            'topProducts' => [
                'labels' => $topProducts->pluck('name')->toArray(),
                'data' => $topProducts->pluck('total_qty')->toArray(),
            ],
        ];

        return view('theme.adminlte.dashboard', compact('stats', 'charts'));
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
