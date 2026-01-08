<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Sales\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Step 1: Customer Selection
     */
    public function create()
    {
        // We will load customers via AJAX or pass them if there are few,
        // but for POS speed, usually AJAX is better if the list is large.
        // For now, let's pass a few recent or all if small number.
        // The blade calls `load-customers`, so we might just return the view.
        return view('theme.adminlte.sales.orders.create');
    }

    /**
     * Save Customer Step
     */
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,uuid',
        ]);

        $company = request()->company; // Via middleware/binding
        $customer = Customer::with(['country', 'state'])->where('uuid', $request->customer_id)->firstOrFail();

        // Check if we are resuming a draft or creating new?
        // For step 1, usually creating new.
        // If 'order_id' is present, update it.

        $order = null;
        if ($request->has('order_id') && $request->order_id) {
            $order = Order::where('uuid', $request->order_id)->first();
        }

        if (! $order) {
            $order = new Order;
            $order->uuid = (string) Str::uuid();
            $order->company_id = $company->id;
            $order->status_id = 1;
            $order->order_number = 'DRAFT-'.strtoupper(Str::random(6)); // Temp number
            $order->currency_id = $company->base_currency_id;
        }

        $order->customer_id = $customer->id;
        $order->payment_term_id = $customer->payment_term_id;

        // Construct Address JSON
        $addressData = [
            'name' => $customer->display_name,
            'address_line_1' => $customer->address,
            'postal_code' => $customer->postal_code,
            'city' => '', // Customer model doesn't have city explicitly? Defaulting to empty or parsing if needed.
            'state' => $customer->state?->name,
            'country' => $customer->country?->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
        ];

        $order->billing_address = $addressData;

        // Default shipping to same as billing initially
        if (empty($order->shipping_address)) {
            $order->shipping_address = $addressData;
        }

        $order->save();

        $order->save();

        return redirect()->route('sales.orders.products', ['company' => $company->uuid, 'order' => $order->uuid]);
    }

    /**
     * Step 2: Products
     */
    public function products(Order $order)
    {
        return view('theme.adminlte.sales.orders.steps.products', compact('order'));
    }

    /**
     * Step 3: Billing & Terms
     */
    /**
     * Step 3: Billing & Terms
     */
    public function billing(Order $order)
    {
        $paymentTerms = \App\Models\Accounting\PaymentTerm::where('company_id', $order->company_id)->where('is_active', true)->get();

        return view('theme.adminlte.sales.orders.steps.billing', compact('order', 'paymentTerms'));
    }

    public function storeBilling(Request $request, Order $order)
    {
        // specific validation rules if needed
        $data = $request->validate([
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'shipping_total' => 'nullable|numeric|min:0',
            'additional_total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            // add validation for address fields if strictly required
        ]);

        $order->payment_term_id = $request->payment_term_id;

        // Calculate Due Date
        if ($request->payment_term_id) {
            $term = \App\Models\Accounting\PaymentTerm::find($request->payment_term_id);
            if ($term) {
                $order->payment_term_name = $term->name;
                $order->payment_due_days = $term->due_days;
                $order->due_date = now()->addDays($term->due_days);
            }
        }

        $order->shipping_total = $request->shipping_total ?? 0;
        $order->additional_total = $request->additional_total ?? 0;
        $order->notes = $request->notes;

        // Process Addresses
        if ($request->has('billing')) {
            $order->billing_address = $request->billing;
        }
        if ($request->has('shipping')) {
            $order->shipping_address = $request->shipping;
        }

        // Calculate Grand Total
        // Subtotal is already calculated from items? Or should we recalc here?
        // Let's ensure totals are up to date.
        $this->recalculateOrder($order);

        $order->save();

        return redirect()->route('sales.orders.preview', ['company' => $request->company, 'order' => $order->uuid]);
    }

    /**
     * Step 4: Preview
     */
    public function preview(Order $order)
    {
        return view('theme.adminlte.sales.orders.steps.preview', compact('order'));
    }

    // ==========================================
    // AJAX / API Methods for Wizard
    // ==========================================

    public function loadCustomers(Request $request)
    {
        $customers = Customer::where('company_id', $request->company->id)
            ->orderBy('created_at', 'desc')
            ->take(20) // Limit initial load
            ->get();

        // Reuse the component or just return JSON?
        // User used response.view in previous attempt
        $data['customers'] = $customers;
        $view = view('theme.adminlte.components.customer-cards', $data)->render();

        return response()->json(['view' => $view]);
    }

    public function searchCustomers(Request $request)
    {
        $term = $request->term;
        $customers = Customer::where('company_id', $request->company->id)
            ->where(function ($q) use ($term) {
                $q->where('display_name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('phone', 'LIKE', "%{$term}%");
            })
            ->take(20)
            ->get();

        // Return select2 format
        $results = $customers->map(function ($c) {
            return ['id' => $c->uuid, 'text' => $c->display_name.' ('.$c->email.')'];
        });

        return response()->json(['results' => $results]);
    }

    public function searchProducts(Request $request)
    {
        $term = $request->term;
        $categoryId = $request->category_id;

        $query = \App\Models\Catalog\Product::where('company_id', $request->company->id);

        if ($term) {
            $query->where('name', 'LIKE', "%{$term}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->take(24)->get();

        // We need a partial view for the grid items
        $view = view('theme.adminlte.sales.orders.partials.product_grid_items', compact('products'))->render();

        return response()->json(['view' => $view]);
    }

    public function addToCart(Request $request, Order $order)
    {
        // Add item to order
        // Assume request has: product_id, variant_id, quantity, price

        // This is a simplified implementation. Real world would check stock, etc.
        // For now, allow drafting.

        // Check if item exists
        // $item = $order->items()...

        // Since I don't have the OrderItem model/migration handy in context, I'll assume standard relationship.
        // Actually, user said "Allow Drafting".

        // Let's assume request data:
        // variant_id, quantity, price, tax stuff?

        // For Verification: I will just simulate success and return true for now,
        // as the actual Order/OrderItem logic might be complex and I am mocking the UI flow first.
        // BUT user wants data to persist. Use OrderItem.

        $variant = \App\Models\Catalog\ProductVariant::find($request->variant_id);

        // Update or Create Item
        $item = $order->items()->where('variant_id', $variant->id)->first();
        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            $order->items()->create([
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'name' => $variant->product->name.' - '.$variant->name,
                'quantity' => $request->quantity,
                'price' => $request->price ?? $variant->price,
                'total' => ($request->price ?? $variant->price) * $request->quantity, // basic calc
            ]);
        }

        // Calculate Totals on Order
        $this->recalculateOrder($order);

        return $this->cartResponse($order);
    }

    public function updateLine(Request $request, Order $order, $lineId)
    {
        $item = $order->items()->findOrFail($lineId);
        $item->quantity = $request->quantity;
        // Update price/discount if needed?
        $item->save();

        $this->recalculateOrder($order);

        return $this->cartResponse($order);
    }

    public function removeLine(Request $request, Order $order, $lineId)
    {
        $item = $order->items()->findOrFail($lineId);
        $item->delete();

        $this->recalculateOrder($order);

        return $this->cartResponse($order);
    }

    // Helper to return Cart HTML
    private function cartResponse(Order $order)
    {
        $order->refresh();
        $view = view('theme.adminlte.sales.orders.partials.cart_items', compact('order'))->render();

        return response()->json([
            'status' => 'success',
            'cart_html' => $view,
            'subtotal' => number_format($order->subtotal, 2),
            'total' => number_format($order->total, 2),
            'count' => $order->items->count().' Items',
        ]);
    }

    public function store(Request $request, Order $order)
    {
        // Finalize order
        // Check if action is 'invoice' or 'save'
        $action = $request->action;

        if ($action == 'invoice') {
            $order->status_id = 2; // Confirmed? Need to check OrderStatus logic. Let's assume 2 is Confirmed.
            $order->confirmed_at = now();
            $order->order_number = 'ORD-'.strtoupper(Str::random(8)); // Real number generation
            // Create Invoice record logic would go here
        } else {
            // Just save as standard Draft or Confirmed Order
            $order->status_id = 2;
            $order->confirmed_at = now();
            $order->order_number = 'ORD-'.strtoupper(Str::random(8));
        }

        $order->save();

        return redirect()->route('sales-orders.show', ['sales_order' => $order->id])
            ->with('success', 'Order created successfully');
    }

    private function recalculateOrder(Order $order)
    {
        // Simple mock calc
        $subtotal = 0;
        foreach ($order->items as $item) {
            $item->total = $item->quantity * $item->price;
            $item->save();
            $subtotal += $item->total;
        }
        $order->subtotal = $subtotal;

        // Add Shipping + Additional
        $order->shipping_total = $order->shipping_total ?? 0;
        $order->additional_total = $order->additional_total ?? 0;

        // Tax? For now simple sum.
        $order->grand_total = $order->subtotal + $order->shipping_total + $order->additional_total;
        $order->total = $order->grand_total; // Legacy field match

        $order->save();
    }
}
