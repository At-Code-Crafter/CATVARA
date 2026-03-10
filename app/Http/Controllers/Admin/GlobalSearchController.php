<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Models\Customer\Customer;
use App\Models\Catalog\Product;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $companyId = $request->company->id;

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Orders
        $orders = Order::where('company_id', $companyId)
            ->where('order_number', 'like', "%{$query}%")
            ->take(5)
            ->get();
        foreach ($orders as $o) {
            $results[] = [
                'type' => 'Order',
                'title' => $o->order_number,
                'subtitle' => optional($o->customer)->display_name ?? 'Guest',
                'url' => company_route('sales-orders.show', ['sales_order' => $o->uuid]),
                'icon' => 'fa-file-invoice'
            ];
        }

        // Search Customers
        $customers = Customer::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('display_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(5)
            ->get();
        foreach ($customers as $c) {
            $results[] = [
                'type' => 'Customer',
                'title' => $c->display_name,
                'subtitle' => $c->email,
                'url' => company_route('customers.edit', ['customer' => $c->id]),
                'icon' => 'fa-user'
            ];
        }

        // Search Products
        $products = Product::where('company_id', $companyId)
            ->where('name', 'like', "%{$query}%");
        apply_brand_filter($products);
        $products = $products->take(5)->get();
        foreach ($products as $p) {
            $results[] = [
                'type' => 'Product',
                'title' => $p->name,
                'subtitle' => optional($p->category)->name ?? 'General',
                'url' => company_route('catalog.products.edit', ['product' => $p->id]),
                'icon' => 'fa-box'
            ];
        }

        return response()->json($results);
    }
}
