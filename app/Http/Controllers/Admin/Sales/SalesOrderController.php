<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesOrderRequest;
use App\Http\Requests\Sales\UpdateSalesOrderCustomersRequest;
use App\Http\Requests\Sales\UpdateSalesOrderPaymentStatusRequest;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use App\Models\Accounting\PaymentStatus;
use App\Models\Accounting\PaymentTerm;
use App\Models\Catalog\ProductVariant;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\Sales\Order;
use App\Models\Sales\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function index()
    {
        $this->authorize('view', 'orders');

        $companyId = active_company_id();
        $statuses = OrderStatus::all();
        $customers = Customer::where('company_id', $companyId)->orderBy('display_name')->get();

        return view('catvara.sales-orders.index', compact('statuses', 'customers'));
    }

    public function data(Request $request)
    {
        $companyId = active_company_id();

        $query = Order::query()
            ->where('orders.company_id', $companyId)
            ->with(['customer', 'status', 'currency']);

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
            ->editColumn('order_number', fn ($order) => '<span class="font-weight-bold">'.e($order->order_number).'</span>')
            ->editColumn('created_at', fn ($order) => optional($order->created_at)->format('M d, Y'))
            ->addColumn('customer_name', fn ($order) => $order->customer->display_name ?? 'N/A')
            ->editColumn('status', function ($order) {
                $code = $order->status->code ?? '';
                $color = 'secondary';
                if ($code === 'CONFIRMED') {
                    $color = 'success';
                }
                if ($code === 'DRAFT') {
                    $color = 'warning';
                }
                if (in_array($code, ['CANCELLED'])) {
                    $color = 'danger';
                }

                return '<span class="badge badge-'.$color.'">'.e($order->status->name ?? '—').'</span>';
            })
            ->editColumn('grand_total', function ($order) {
                $amount = number_format((float) $order->grand_total, 2);
                $cur = $order->currency->code ?? '';

                return '<span class="font-weight-bold text-dark">'.e($cur).' '.$amount.'</span>';
            })
            ->addColumn('actions', function ($order) {
                $editUrl = company_route('sales-orders.edit', ['sales_order' => $order->uuid]);
                $showUrl = company_route('sales-orders.show', ['sales_order' => $order->id]);

                $compact['showUrl'] = $showUrl;
                $compact['editUrl'] = $editUrl;
                $compact['deleteUrl'] = null;
                $compact['editSidebar'] = false;

                return view('theme.adminlte.components._table-actions', $compact)->render();
            })
            ->rawColumns(['order_number', 'status', 'grand_total', 'actions'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $this->authorize('create', 'orders');

        $editOrder = null;
        if ($request->filled('edit_order')) {
            $editOrder = Order::where('company_id', $request->company->id)
                ->where('uuid', $request->edit_order)
                ->first();
        }

        return view('catvara.sales-orders.create', [
            'editOrder' => $editOrder,
        ]);
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $this->authorize('create', 'orders');

        $company = $request->company;

        $billToCustomer = Customer::where('company_id', $company->id)
            ->where('uuid', $request->bill_to)
            ->firstOrFail();

        $shipToCustomer = $request->filled('ship_to')
            ? Customer::where('company_id', $company->id)->where('uuid', $request->ship_to)->firstOrFail()
            : $billToCustomer;

        $status = OrderStatus::firstOrCreate(
            ['code' => 'DRAFT'],
            ['name' => 'Draft', 'is_active' => true]
        );

        $paymentStatus = PaymentStatus::where('code', 'INITIATED')->first()
            ?? PaymentStatus::firstOrCreate(['code' => 'INITIATED'], ['name' => 'Initiated', 'is_active' => true]);

        // Currency default: company base currency -> first currency
        $defaultCurrencyId = $company->base_currency_id
            ?? (int) (Currency::query()->value('id') ?? 1);

        return DB::transaction(function () use ($company, $billToCustomer, $shipToCustomer, $status, $paymentStatus, $defaultCurrencyId) {
            $order = Order::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,

                'customer_id' => $billToCustomer->id,
                'shipping_customer_id' => $shipToCustomer->id,         

                'status_id' => $status->id,
                'payment_status_id' => $paymentStatus->id,

                'order_number' => $this->generateOrderNumber($company),
                'created_by' => auth()->id(),

                'currency_id' => $defaultCurrencyId,
                'base_currency_id' => $company->base_currency_id ?? null,
                'fx_rate' => 1,
            ]);

            // Addresses snapshots
            $order->addresses()->create([
                'type' => 'BILLING',
                'company_id' => $company->id,
                'address_line_1' => $billToCustomer->address->address_line_1 ?? '',
                'address_line_2' => $billToCustomer->address->address_line_2 ?? null,
                'city' => $billToCustomer->address->city ?? null,
                'state_id' => ! empty($billToCustomer->address->state_id) ? $billToCustomer->address->state_id : null,
                'zip_code' => $billToCustomer->address->zip_code ?? '',
                'country_id' => ! empty($billToCustomer->address->country_id) ? $billToCustomer->address->country_id : null,
                'phone' => $billToCustomer->phone,
                'email' => $billToCustomer->email,
                'name' => $billToCustomer->legal_name ?? $billToCustomer->display_name,
                'tax_number' => $billToCustomer->tax_number,
            ]);

            $order->addresses()->create([
                'type' => 'SHIPPING',
                'company_id' => $company->id,
                'address_line_1' => $shipToCustomer->address->address_line_1 ?? '',
                'address_line_2' => $shipToCustomer->address->address_line_2 ?? null,
                'city' => $shipToCustomer->address->city ?? null,
                'state_id' => ! empty($shipToCustomer->address->state_id) ? $shipToCustomer->address->state_id : null,
                'zip_code' => $shipToCustomer->address->zip_code ?? '',
                'country_id' => ! empty($shipToCustomer->address->country_id) ? $shipToCustomer->address->country_id : null,
                'phone' => $shipToCustomer->phone,
                'email' => $shipToCustomer->email,
                'name' => $shipToCustomer->legal_name ?? $shipToCustomer->display_name,
                'tax_number' => $shipToCustomer->tax_number,
            ]);

            return redirect()->to(company_route('sales-orders.edit', ['sales_order' => $order->uuid]));
        });
    }

    public function updateCustomers(UpdateSalesOrderCustomersRequest $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $billToCustomer = Customer::where('company_id', $company->id)
            ->where('uuid', $request->bill_to)
            ->firstOrFail();

        $shipToCustomer = $request->filled('ship_to')
            ? Customer::where('company_id', $company->id)->where('uuid', $request->ship_to)->firstOrFail()
            : $billToCustomer;

        return DB::transaction(function () use ($order, $company, $billToCustomer, $shipToCustomer) {
            $order->update([
                'customer_id' => $billToCustomer->id,
                'shipping_customer_id' => $shipToCustomer->id,
            ]);

            $order->addresses()->updateOrCreate(
                ['type' => 'BILLING'],
                [
                    'company_id' => $company->id,
                    'address_line_1' => $billToCustomer->address->address_line_1 ?? '',
                    'address_line_2' => $billToCustomer->address->address_line_2 ?? null,
                    'city' => $billToCustomer->address->city ?? null,
                    'state_id' => ! empty($billToCustomer->address->state_id) ? $billToCustomer->address->state_id : null,
                    'zip_code' => $billToCustomer->address->zip_code ?? '',
                    'country_id' => ! empty($billToCustomer->address->country_id) ? $billToCustomer->address->country_id : null,
                    'phone' => $billToCustomer->phone,
                    'email' => $billToCustomer->email,
                    'name' => $billToCustomer->legal_name ?? $billToCustomer->display_name,
                    'tax_number' => $billToCustomer->tax_number,
                ]
            );

            $order->addresses()->updateOrCreate(
                ['type' => 'SHIPPING'],
                [
                    'company_id' => $company->id,
                    'address_line_1' => $shipToCustomer->address->address_line_1 ?? '',
                    'address_line_2' => $shipToCustomer->address->address_line_2 ?? null,
                    'city' => $shipToCustomer->address->city ?? null,
                    'state_id' => ! empty($shipToCustomer->address->state_id) ? $shipToCustomer->address->state_id : null,
                    'zip_code' => $shipToCustomer->address->zip_code ?? '',
                    'country_id' => ! empty($shipToCustomer->address->country_id) ? $shipToCustomer->address->country_id : null,
                    'phone' => $shipToCustomer->phone,
                    'email' => $shipToCustomer->email,
                    'name' => $shipToCustomer->legal_name ?? $shipToCustomer->display_name,
                    'tax_number' => $shipToCustomer->tax_number,
                ]
            );

            return redirect()->to(company_route('sales-orders.edit', ['sales_order' => $order->uuid]));
        });
    }

    public function edit(Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items.productVariant.product.category', 'customer', 'addresses', 'shippingCustomer', 'currency', 'status'])
            ->firstOrFail();

        $billToCustomer = $order->addresses->where('type', 'BILLING')->first();
        $shipToCustomer = $order->addresses->where('type', 'SHIPPING')->first();

        $initialState = [
            'items' => $order->items->map(function ($item) {
                $isCustom = (bool) ($item->is_custom ?? false);

                return [
                    'type' => $isCustom ? 'custom' : 'variant',
                    'variantId' => $isCustom ? null : (string) optional($item->productVariant)->uuid,
                    'custom_name' => $isCustom ? $item->product_name : null,
                    'custom_sku' => $isCustom ? $item->custom_sku : null,

                    'qty' => (float) $item->quantity,
                    'unitPrice' => (float) $item->unit_price,
                    'discountPercent' => (float) ($item->discount_percent ?? 0),
                    'taxRate' => (float) ($item->tax_rate ?? 0),
                    'attrs' => [],
                ];
            })->values(),

            'payment_term_id' => $order->payment_term_id ?? $billToCustomer->payment_term_id,
            'shipping' => (float) $order->shipping_total,
            'additional' => 0,
            'vat_rate' => 0, // only used as fallback if item/category doesn't provide tax
            'notes' => $order->notes,
            'currency' => $order->currency->code ?? ($company->baseCurrency->code ?? 'AED'),
            'status' => $order->status->code ?? 'DRAFT',
        ];

        $customerDiscount = $billToCustomer->percentage_discount ?? 0;

        return view('catvara.sales-orders.edit', compact('billToCustomer', 'shipToCustomer', 'order', 'initialState', 'customerDiscount'));
    }

    public function update(UpdateSalesOrderRequest $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Confirm / generate
        if ($request->action === 'generate') {
            $status = OrderStatus::where('code', 'CONFIRMED')->first();
            if ($status) {
                $order->update(['status_id' => $status->id, 'confirmed_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => company_route('sales-orders.show', ['sales_order' => $order->uuid]),
            ]);
        }

        DB::beginTransaction();
        try {
            $vatRateFallback = (float) ($request->vat_rate ?? 0);

            $shipping = (float) ($request->shipping ?? 0);
            $additional = (float) ($request->additional ?? 0);
            $shippingTotal = max(0, $shipping + $additional);

            $shippingTaxTotal = max(0, $shippingTotal * ($vatRateFallback / 100));

            $currencyId = $this->resolveCurrencyIdFromCode($request->currency);

            $termSnapshot = $this->resolvePaymentTermSnapshot($request->payment_term_id);

            $calc = $this->calculateFromItemsPayload(
                $company->id,
                $request->items ?? [],
                $vatRateFallback
            );

            // Update order header
            $order->update([
                'currency_id' => $currencyId,

                'payment_term_id' => $termSnapshot['payment_term_id'],
                'payment_term_name' => $termSnapshot['payment_term_name'],
                'payment_due_days' => $termSnapshot['payment_due_days'],
                'due_date' => $request->due_date ?? $order->due_date,

                'notes' => $request->notes ?? null,

                'subtotal' => $calc['subtotal'],
                'discount_total' => $calc['discount_total'],
                'tax_total' => $calc['tax_total'],

                'shipping_total' => $shippingTotal,
                'shipping_tax_total' => $shippingTaxTotal,

                'grand_total' => $calc['items_grand_total'] + $shippingTotal + $shippingTaxTotal,
            ]);

            // Sync items: delete + recreate (draft-safe)
            $order->items()->delete();
            foreach ($calc['items_for_db'] as $row) {
                $order->items()->create($row);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => $order->status->name ?? 'Draft',
                'is_confirmed' => ($order->status->code ?? '') === 'CONFIRMED',
                'totals' => [
                    'subtotal' => (float) $order->subtotal,
                    'discount_total' => (float) $order->discount_total,
                    'tax_total' => (float) $order->tax_total,
                    'shipping_total' => (float) $order->shipping_total,
                    'shipping_tax_total' => (float) $order->shipping_tax_total,
                    'grand_total' => (float) $order->grand_total,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show(Company $company, $id)
    {
        $order = Order::where('company_id', $company->id)
            ->where('id', $id)
            ->with([
                'items.productVariant.product',
                'customer',
                'billingAddress.country',
                'shippingAddress.country',
                'company',
                'currency',
                'status',
                'creator',
                'paymentTerm',
            ])
            ->firstOrFail();

        return view('catvara.sales-orders.show', compact('order'));
    }

    public function updatePaymentStatus(UpdateSalesOrderPaymentStatusRequest $request, Company $company, $id)
    {
        $order = Order::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $status = PaymentStatus::query()
            ->where('code', $request->payment_status_code)
            ->firstOrFail();

        $order->update(['payment_status_id' => $status->id]);

        return response()->json([
            'success' => true,
            'payment_status_id' => $order->payment_status_id,
            'payment_status_code' => $status->code,
            'payment_status_name' => $status->name,
        ]);
    }

    /* ===================== HELPERS ===================== */

    private function resolveCurrencyIdFromCode(string $code): int
    {
        $code = strtoupper(trim($code));
        $currency = Currency::query()->where('code', $code)->first();

        if (! $currency) {
            throw new \Exception("Currency not found for code: {$code}");
        }

        return (int) $currency->id;
    }

    private function resolvePaymentTermSnapshot(?int $paymentTermId): array
    {
        if (! $paymentTermId) {
            return [
                'payment_term_id' => null,
                'payment_term_name' => null,
                'payment_due_days' => null,
            ];
        }

        $term = PaymentTerm::find($paymentTermId);

        if (! $term) {
            return [
                'payment_term_id' => null,
                'payment_term_name' => null,
                'payment_due_days' => null,
            ];
        }

        return [
            'payment_term_id' => (int) $term->id,
            'payment_term_name' => (string) $term->name,
            'payment_due_days' => (int) ($term->due_days ?? 0),
        ];
    }

    /**
     * Matches your NEW order_items schema:
     * - is_custom, custom_sku
     * - line_subtotal
     * - discount_percent
     * - line_discount_total
     * - tax_rate, tax_amount
     * - line_total
     *
     * Tax resolution order:
     * 1) item.tax_rate
     * 2) category tax_rate (if exists)
     * 3) vat fallback (header vat_rate)
     */
    private function calculateFromItemsPayload(int $companyId, array $items, float $vatFallback): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $itemsGrand = 0.0;

        $rows = [];

        foreach ($items as $item) {
            $type = $item['type'] ?? 'variant';
            $isCustom = ($type === 'custom');

            $qty = max(1, (int) ($item['qty'] ?? 1));
            $unit = (float) ($item['unit_price'] ?? 0);

            $discPct = min(100, max(0, (float) ($item['discount_percent'] ?? 0)));

            $variantId = null;
            $productName = 'Custom Item';
            $variantDescription = null;
            $customSku = null;

            $categoryTaxRate = null;

            if ($isCustom) {
                $productName = $item['custom_name'] ?? 'Custom Item';
                $customSku = $item['custom_sku'] ?? null;
                $variantDescription = $customSku ? "SKU: {$customSku}" : null;
            } else {
                $variantUuid = $item['variant_id'] ?? null;
                if (! $variantUuid) {
                    throw new \Exception('Variant ID is required for variant item.');
                }

                $variant = ProductVariant::query()
                    ->where('company_id', $companyId)
                    ->where('uuid', $variantUuid)
                    ->with(['product.category'])
                    ->firstOrFail();

                $variantId = $variant->id;
                $productName = (string) ($variant->product->name ?? '');
                $variantDescription = method_exists($variant, 'getVariantDescription')
                    ? $variant->getVariantDescription()
                    : null;

                // Category-level tax (your current direction)
                $categoryTaxRate = optional($variant->product->category)->tax_rate;
            }

            // tax rate resolution
            if (array_key_exists('tax_rate', $item) && $item['tax_rate'] !== null) {
                $taxRate = min(100, max(0, (float) $item['tax_rate']));
            } elseif ($categoryTaxRate !== null) {
                $taxRate = min(100, max(0, (float) $categoryTaxRate));
            } else {
                $taxRate = min(100, max(0, (float) $vatFallback));
            }

            $gross = $unit * $qty;                    // line_subtotal
            $discountAmount = $gross * ($discPct / 100);
            $taxable = max(0, $gross - $discountAmount);
            $taxAmount = $taxable * ($taxRate / 100);
            $lineTotal = $taxable + $taxAmount;

            $subtotal += $gross;
            $discountTotal += $discountAmount;
            $taxTotal += $taxAmount;
            $itemsGrand += $lineTotal;

            $rows[] = [
                'product_variant_id' => $variantId,
                'is_custom' => $isCustom ? 1 : 0,
                'custom_sku' => $isCustom ? $customSku : null,

                'product_name' => $productName,
                'variant_description' => $variantDescription,

                'unit_price' => $unit,
                'quantity' => $qty,
                'fulfilled_quantity' => 0,

                'line_subtotal' => $gross,

                'discount_percent' => $discPct,
                'discount_amount' => $discountAmount,
                'line_discount_total' => $discountAmount,

                'tax_group_id' => null,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,

                'line_total' => $lineTotal,
            ];
        }

        return [
            'subtotal' => round($subtotal, 6),
            'discount_total' => round($discountTotal, 6),
            'tax_total' => round($taxTotal, 6),
            'items_grand_total' => round($itemsGrand, 6),
            'items_for_db' => $rows,
        ];
    }

    public function finalize(string $sales_order)
    {
        // You likely already have a "find by uuid" helper or route model binding.
        // Adjust this to your actual model fetch pattern.
        $order = Order::query()
            ->where('uuid', $sales_order)
            ->with([
                'currency',
                'items.productVariant.product', // adjust relations to your schema
                'paymentTerm',
                'paymentMethod',
            ])
            ->firstOrFail();

        // This initialState should match what Step 02 expects.
        // If you already store draft JSON on order, load it here.
        $initialState = $order->draft_state ?? null; // adjust column name if different

        // Customers used in Step 02 — reuse same variables if you have them.
        $billToCustomer = $order->billToCustomer ?? null; // adjust relation
        $shipToCustomer = $order->shipToCustomer ?? null; // adjust relation

        // Fallback customer discount (if you compute it already)
        $customerDiscount = $order->customer_discount_percent ?? 0;

        return view('catvara.sales-orders.finalize', compact(
            'order',
            'initialState',
            'billToCustomer',
            'shipToCustomer',
            'customerDiscount'
        ));
    }

    public function finalizeStore(Request $request, string $sales_order)
    {
        $order = Order::query()
            ->where('uuid', $sales_order)
            ->with(['items'])
            ->firstOrFail();

        $validated = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'payment_term_id' => ['nullable'],
            'payment_method_id' => ['nullable'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],

            'items.*.type' => ['required', 'in:variant,custom'],
            'items.*.variant_id' => ['nullable', 'string'],
            'items.*.custom_name' => ['nullable', 'string', 'max:255'],
            'items.*.custom_sku' => ['nullable', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        // Enforce payment method only when due_days = 0 (server-side safety)
        if (! empty($validated['payment_term_id'])) {
            $term = PaymentTerm::query()->find($validated['payment_term_id']);
            if ($term && (int) $term->due_days === 0) {
                if (empty($validated['payment_method_id'])) {
                    return response()->json(['message' => 'Payment method is required for Due Now terms.'], 422);
                }
            }
        }

        DB::transaction(function () use ($order, $validated) {
            // Save draft_state and mark as finalized.
            // Adjust fields to your schema:
            $order->currency_code = $validated['currency']; // or currency_id
            $order->payment_term_id = $validated['payment_term_id'] ?? null;
            $order->payment_method_id = $validated['payment_method_id'] ?? null;
            $order->shipping = $validated['shipping'] ?? 0;
            $order->additional = $validated['additional'] ?? 0;
            $order->vat_rate = $validated['vat_rate'] ?? 0;
            $order->notes = $validated['notes'] ?? null;

            // Save state JSON if you use it
            $order->draft_state = [
                'currency' => $validated['currency'],
                'payment_term_id' => $validated['payment_term_id'] ?? null,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'shipping' => $validated['shipping'] ?? 0,
                'additional' => $validated['additional'] ?? 0,
                'vat_rate' => $validated['vat_rate'] ?? 0,
                'notes' => $validated['notes'] ?? '',
                'items' => $validated['items'],
            ];

            // ✅ Finalize
            $order->status = 'confirmed'; // or 'finalized' based on your system
            $order->confirmed_at = now(); // optional
            $order->save();

            // If you also store line items in normalized tables,
            // you should sync them here (delete & recreate, or update existing).
            // I'm leaving this minimal because your Step 02 update already does it.
        });

        return response()->json([
            'ok' => true,
            'redirect' => company_route('sales-orders.show', ['sales_order' => $order->uuid]),
        ]);
    }

    private function generateOrderNumber($company)
    {
        $prefix = 'SO-'.Carbon::now()->format('Ymd').'-';

        $lastOrder = Order::where('company_id', $company->id)
            ->where('order_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNum = intval(substr($lastOrder->order_number, strlen($prefix)));

            return $prefix.str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix.'0001';
    }
}
