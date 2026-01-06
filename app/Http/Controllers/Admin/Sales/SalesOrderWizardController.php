<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Models\Catalog\Category;
use App\Models\Customer\Customer;
use App\Models\Settings\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SalesOrderWizardController extends Controller
{
    /**
     * Show Step 1: Customer Selection
     */
    public function step1(Request $request, $uuid = null)
    {
        $order = null;
        if ($uuid) {
            $order = Order::where('uuid', $uuid)->firstOrFail();
        }

        return view('theme.adminlte.sales.orders.wizard.step1', compact('order'));
    }

    /**
     * Store Step 1: Create Draft Order with Customer & Addresses
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bill_to' => 'required', // JSON or ID, we'll parse it
            'ship_to' => 'required',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        
        // Find existing or create new order
        if ($request->uuid) {
            $order = Order::where('uuid', $request->uuid)->firstOrFail();
        } else {
            $order = new Order();
            $order->uuid = Str::uuid();
            $order->order_number = 'ORD-' . strtoupper(Str::random(8)); // Temp number
            $order->status_id = 1; // Draft
            $order->company_id = auth()->user()->company_id ?? 1; // Fallback
            $order->created_by = auth()->id();
        }

        $order->customer_id = $customer->id;
        $order->billing_address = $request->bill_to; // Assuming JSON string or array from frontend
        $order->shipping_address = $request->ship_to;
        $order->save();

        return redirect()->route('admin.sales.orders.wizard.step2', ['uuid' => $order->uuid]);
    }

    /**
     * Show Step 2: Add Items
     */
    public function step2(Request $request, $uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        $categories = Category::all(); // For filter
        
        return view('theme.adminlte.sales.orders.wizard.step2', compact('order', 'categories'));
    }

    /**
     * Show Step 3: Review & Payment
     */
    public function step3(Request $request, $uuid)
    {
        $order = Order::where('uuid', $uuid)->with('items')->firstOrFail();
        $paymentTerms = PaymentTerm::where('is_active', true)->get();

        return view('theme.adminlte.sales.orders.wizard.step3', compact('order', 'paymentTerms'));
    }

    /**
     * Store Step 3: Finalize
     */
    public function storeStep3(Request $request, $uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();
        
        $order->payment_term_id = $request->payment_term_id;
        $order->notes = $request->notes;
        $order->shipping_total = $request->shipping_cost ?? 0;
        // Recalculate totals would happen here or via service
        $order->status_id = 2; // Pending / Placed
        $order->save();

        if ($request->action === 'invoice') {
            // Generate Invoice Logic (Placeholder)
            // return redirect()->route('admin.sales.invoices.show', $invoiceId);
        }

        return redirect()->route('admin.sales.orders.show', $order->id)->with('success', 'Order created successfully!');
    }
}
