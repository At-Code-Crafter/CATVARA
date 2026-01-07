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
        $companyId = active_company_id();
        $paymentTerms = PaymentTerm::where('is_active', true)->get();
        $categories = Category::where('company_id', $companyId)->where('is_active', true)->get();

        return view('theme.adminlte.sales.orders.create', compact('paymentTerms', 'categories'));
    }

    public function storeDraft(Request $request)
    {
        // Logic to save/update draft order
        // Returns ID/UUID to keep updating the same order
        $validated = $request->validate([
            'order_uuid' => 'nullable|string',
            'customer_id' => 'nullable|integer',
            'items' => 'nullable|array',
            'payment_term_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'billing_address' => 'nullable|array',
            'shipping_address' => 'nullable|array',
            'shipping_total' => 'nullable|numeric',
        ]);

        // Ensure company is selected
        $companyId = active_company_id();
        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No company selected. Please select a company first.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Get or create DRAFT status using firstOrCreate to handle race conditions
            $statusDraft = OrderStatus::firstOrCreate(
                ['code' => 'DRAFT'],
                ['name' => 'Draft', 'is_active' => true, 'is_final' => false]
            );

            if (! empty($validated['order_uuid'])) {
                $order = Order::where('uuid', $validated['order_uuid'])->firstOrFail();
            } else {
                // Get company's base currency or first available currency
                $company = Company::find($companyId);
                $currencyId = $company->base_currency_id ?? \App\Models\Pricing\Currency::first()?->id ?? 1;

                $order = new Order;
                $order->uuid = (string) Str::uuid();
                $order->company_id = $companyId;
                $order->order_number = 'DRAFT-'.strtoupper(Str::random(6)); // Temp number
                $order->status_id = $statusDraft->id;
                $order->currency_id = $currencyId;
                $order->created_by = \Illuminate\Support\Facades\Auth::id();
            }

            if (isset($validated['customer_id'])) {
                $order->customer_id = $validated['customer_id'];
            }

            if (isset($validated['payment_term_id'])) {
                $order->payment_term_id = $validated['payment_term_id'];
            }

            if (isset($validated['notes'])) {
                $order->notes = $validated['notes'];
            }

            if (isset($validated['billing_address'])) {
                $order->billing_address = $validated['billing_address'];
            }

            if (isset($validated['shipping_address'])) {
                $order->shipping_address = $validated['shipping_address'];
            }

            if (isset($validated['shipping_total'])) {
                $order->shipping_total = $validated['shipping_total'];
            }

            $order->save();

            // Sync Items if provided
            if (isset($validated['items'])) {
                $order->items()->delete();
                $subtotal = 0;
                $discountTotal = 0;
                $taxTotal = 0;
                foreach ($validated['items'] as $item) {
                    $orderItem = new OrderItem;
                    $orderItem->order_id = $order->id;
                    $orderItem->product_variant_id = $item['variant_id'];
                    $orderItem->product_name = $item['name'];
                    $orderItem->unit_price = $item['price'];
                    $orderItem->quantity = $item['qty'];
                    $orderItem->discount_amount = $item['discount'] ?? 0;
                    $orderItem->tax_rate = $item['tax_rate'] ?? 20; // Default VAT or from logic

                    $lineTotal = ($item['price'] * $item['qty']) - ($item['discount'] ?? 0);
                    $orderItem->line_total = $lineTotal;

                    // Basic Tax Calc
                    $orderItem->tax_amount = $lineTotal * ($orderItem->tax_rate / 100);

                    $orderItem->save();

                    $subtotal += ($item['price'] * $item['qty']);
                    $discountTotal += ($item['discount'] ?? 0);
                    $taxTotal += $orderItem->tax_amount;
                }

                $shipping = $order->shipping_total ?? 0;
                $shippingTax = $shipping * 0.20; // 20% VAT on shipping

                $order->subtotal = $subtotal;
                $order->discount_total = $discountTotal;
                $order->shipping_tax_total = $shippingTax;
                $order->tax_total = $taxTotal + $shippingTax;
                $order->grand_total = ($subtotal - $discountTotal) + $order->tax_total + $shipping;
                $order->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_uuid' => $order->uuid,
                'message' => 'Draft saved successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Draft save error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        // Finalize order
        $validated = $request->validate([
            'order_uuid' => 'required|string|exists:orders,uuid',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::where('uuid', $validated['order_uuid'])->with('items')->firstOrFail();

            // Get or create CONFIRMED status
            $statusConfirmed = OrderStatus::where('code', 'CONFIRMED')->first();
            if (!$statusConfirmed) {
                $statusConfirmed = OrderStatus::create([
                    'code' => 'CONFIRMED',
                    'name' => 'Confirmed',
                    'is_active' => true,
                    'is_final' => false,
                ]);
            }

            // Security: Recalculate totals from items
            $subtotal = $order->items->sum(function ($item) {
                return $item->unit_price * $item->quantity;
            });
            $discountTotal = $order->items->sum('discount_amount');
            $taxTotal = $order->items->sum('tax_amount');
            $shipping = $order->shipping_total ?? 0;
            $shippingTax = $shipping * 0.20;

            $order->status_id = $statusConfirmed->id;
            $order->confirmed_at = now();
            $order->subtotal = $subtotal;
            $order->discount_total = $discountTotal;
            $order->shipping_tax_total = $shippingTax;
            $order->tax_total = $taxTotal + $shippingTax;
            $order->grand_total = ($subtotal - $discountTotal) + $order->tax_total + $shipping;

            // Generate official order number if not already set or if it was DRAFT-xxx
            if (Str::startsWith($order->order_number, 'DRAFT-')) {
                $order->order_number = 'SO-'.date('Ymd').'-'.Str::upper(Str::random(4));
            }

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => company_route('sales-orders.print', ['order' => $order->id]),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with(['items', 'customer', 'status', 'paymentTerm', 'company', 'currency'])->findOrFail($id);

        return view('theme.adminlte.sales.orders.show', compact('order'));
    }

    public function print($uuid)
    {
        $order = Order::with(['items', 'customer', 'paymentTerm', 'company', 'currency'])->where('uuid', $uuid)->firstOrFail();

        return view('theme.adminlte.sales.orders.print', compact('order'));
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->get('q', $request->get('term', '')); // Select2 sends term by default
        $companyId = active_company_id();

        $customers = Customer::where('company_id', $companyId)
            ->with(['paymentTerm', 'addresses', 'country', 'state'])
            ->when($term, function ($q) use ($term) {
                $q->where(function ($x) use ($term) {
                    $x->where('display_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->orderBy('display_name')
            ->limit(20)
            ->get();

        $results = $customers->map(function ($customer) {
            $initials = collect(preg_split('/\s+/', trim((string) $customer->display_name)))
                ->filter()
                ->map(fn ($p) => mb_substr($p, 0, 1))
                ->take(2)
                ->implode('');

            return [
                'id' => $customer->id,
                'text' => $customer->display_name.($customer->email ? " ({$customer->email})" : ''),
                'display_name' => $customer->display_name,
                'initials' => $initials ?: '??',
                'email' => $customer->email,
                'phone' => $customer->phone,
                'type' => $customer->type,
                'legal_name' => $customer->legal_name,
                'tax_number' => $customer->tax_number,
                'notes' => $customer->notes,
                'address' => $customer->address,
                'postal_code' => $customer->postal_code,
                'country_name' => $customer->country->name ?? null,
                'state_name' => $customer->state->name ?? null,
                'payment_term_id' => $customer->payment_term_id,
                'payment_term_name' => $customer->paymentTerm->name ?? null,
                'payment_due_days' => $customer->paymentTerm->due_days ?? null,
                'addresses' => $customer->addresses, // keep as array/collection
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false],
        ]);
    }

    public function searchProducts(Request $request)
    {
        $term = $request->get('q');
        $categoryId = $request->get('category_id');
        $companyId = active_company_id();

        $query = Product::where('company_id', $companyId)
            ->where('is_active', true);

        if ($term) {
            $query->where('name', 'LIKE', '%'.$term.'%');
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->with(['attachments'])
            ->limit(24)
            ->get();

        $results = $products->map(function ($p) {
            $image = $p->attachments->first()?->url ?? 'https://placehold.co/150?text='.urlencode($p->name);

            return [
                'id' => $p->id,
                'name' => $p->name,
                'image' => $image,
                'sku' => $p->slug,
            ];
        });

        return response()->json($results);
    }

    public function getProductVariants(Company $company, Product $product)
    {
        $companyId = active_company_id();

        $variants = ProductVariant::where('product_id', $product->id)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['product', 'attributeValues.attribute', 'prices' => function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->where('is_active', true);
            }])
            ->get();

        $results = $variants->map(function ($v) {
            $price = $v->prices->first()?->price ?? 0.00;

            // Build descriptive name from attributes
            $attrNames = $v->attributeValues->map(function ($av) {
                return $av->attribute->name.': '.$av->value;
            })->implode(', ');

            $displayName = $v->product->name;
            if ($attrNames) {
                $displayName .= ' ('.$attrNames.')';
            }

            return [
                'id' => $v->id,
                'name' => $displayName,
                'price' => (float) $price,
                'sku' => $v->sku,
                'stock' => 999,
            ];
        });

        return response()->json($results);
    }
}
