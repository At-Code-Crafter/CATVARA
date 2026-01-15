<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
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
                return '<span class="font-weight-bold">'.e($order->order_number).'</span>';
            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('M d, Y');
            })
            ->addColumn('customer_name', function ($order) {
                return $order->customer->display_name ?? 'N/A';
            })
            ->editColumn('status', function ($order) {
                $color = 'secondary';
                if (($order->status->code ?? '') === 'CONFIRMED') {
                    $color = 'success';
                }
                if (($order->status->code ?? '') === 'DRAFT') {
                    $color = 'warning';
                }

                return '<span class="badge badge-'.$color.'">'.e($order->status->name ?? '—').'</span>';
            })
            ->editColumn('grand_total', function ($order) {
                return '<span class="font-weight-bold text-dark">'.number_format((float) $order->grand_total, 2).'</span>';
            })
            ->addColumn('actions', function ($order) {
                $edit = company_route('sales-orders.edit', ['sales_order' => $order->uuid]);
                $showUrl = company_route('sales-orders.show', ['sales_order' => $order->id]); // if you have show

                $compact['showUrl'] = $showUrl;
                $compact['editUrl'] = $edit;
                $compact['deleteUrl'] = null;
                $compact['editSidebar'] = false;

                return view('theme.adminlte.components._table-actions', $compact)->render();
            })
            ->rawColumns(['order_number', 'status', 'grand_total', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', 'orders');

        return view('theme.adminlte.sales.orders.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', 'orders');

        $request->validate([
            'sell_to' => 'required|exists:customers,uuid',
            'bill_to' => 'nullable|exists:customers,uuid',
        ]);

        $company = $request->company;

        $sellToCustomer = Customer::where('company_id', $company->id)
            ->where('uuid', $request->sell_to)
            ->firstOrFail();

        $billToCustomer = $request->filled('bill_to')
            ? Customer::where('company_id', $company->id)->where('uuid', $request->bill_to)->firstOrFail()
            : $sellToCustomer;

        $status = OrderStatus::where('code', 'DRAFT')->first();
        if (! $status) {
            $status = OrderStatus::firstOrCreate(
                ['code' => 'DRAFT'],
                ['name' => 'Draft', 'is_active' => true]
            );
        }

        // Currency default (adjust as per settings)
        $defaultCurrencyId = 1;

        $order = Order::create([
            'uuid' => Str::uuid(),
            'company_id' => $company->id,
            'customer_id' => $sellToCustomer->id,
            'status_id' => $status->id,
            'order_number' => $this->generateOrderNumber($company),
            'created_by' => auth()->id(),
            'currency_id' => $defaultCurrencyId,
        ]);

        // Addresses (keep as you already had)
        $order->addresses()->create([
            'type' => 'BILLING',
            'company_id' => $company->id,
            'address_line_1' => $billToCustomer->address->address_line_1 ?? '',
            'address_line_2' => $billToCustomer->address->address_line_2 ?? '',
            'city' => $billToCustomer->address->city ?? '',
            'state_id' => $billToCustomer->address->state_id ?? '',
            'zip_code' => $billToCustomer->address->zip_code ?? '',
            'country_id' => $billToCustomer->address->country_id ?? '',
            'phone' => $billToCustomer->phone,
            'email' => $billToCustomer->email,
        ]);

        $order->addresses()->create([
            'type' => 'SHIPPING',
            'company_id' => $company->id,
            'address_line_1' => $sellToCustomer->address->address_line_1 ?? '',
            'address_line_2' => $sellToCustomer->address->address_line_2 ?? '',
            'city' => $sellToCustomer->address->city ?? '',
            'state_id' => $sellToCustomer->address->state_id ?? '',
            'zip_code' => $sellToCustomer->address->zip_code ?? '',
            'country_id' => $sellToCustomer->address->country_id ?? '',
            'phone' => $sellToCustomer->phone,
            'email' => $sellToCustomer->email,
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => company_route('sales-orders.edit', ['sales_order' => $order->uuid]),
        ]);
    }

    public function edit(Company $company, $id)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->whereUuid($id)
            ->with(['items.productVariant.product', 'invoice'])
            ->firstOrFail();

        $sellToCustomer = $order->customer;

        $billToCustomer = $order->billingAddress
            ? Customer::where('email', $order->billingAddress->email)->first()
            : $sellToCustomer;

        // IMPORTANT: Your migration has discount_amount but NOT discount_percent.
        // So we cannot reliably hydrate discountPercent from DB unless you add that column.
        // We'll keep it 0 in UI for now.
        $initialState = [
            'items' => $order->items->map(function ($item) {
                return [
                    'variantId' => (string) $item->productVariant->uuid,
                    'product_id' => (string) optional($item->productVariant)->product->uuid,
                    'qty' => (float) $item->quantity,
                    'unitPrice' => (float) $item->unit_price,
                    'discountPercent' => $item->discount_percent,
                    'attrs' => [],
                ];
            })->values(),

            'payment_term_id' => $order->payment_term_id,
            'shipping' => (float) $order->shipping_total,

            // You do NOT have additional_total column in orders migration.
            // We'll keep UI value 0; frontend can still send it, backend will fold into shipping_total.
            'additional' => $order->additional_total ?? 0,

            // You do NOT have order.tax_rate column in migration. We use UI vat_rate only.
            'vat_rate' => $order->tax_rate,

            'notes' => $order->notes,

            // your JS expects currency code, but we store currency_id; JS select has AED/USD/GBP values.
            // We'll set it to AED by default; feel free to map id->code if you want.
            'currency' => $order->currency->code,
            'status' => $order->status->code ?? 'DRAFT',
        ];

        return view('theme.adminlte.sales.orders.edit', compact('sellToCustomer', 'billToCustomer', 'order', 'initialState'));
    }

    /**
     * SINGLE HIT UPDATE:
     * Receives header + items together, recalculates everything server-side,
     * syncs items and updates order totals in one transaction.
     */
    public function update(Request $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Handle Status Change
        if ($request->action === 'generate') {
            $status = OrderStatus::where('code', 'CONFIRMED')->first();
            if ($status) {
                $order->update(['status_id' => $status->id]);
            }

            // Return redirect URL for show page
            return response()->json([
                'success' => true,
                'redirect_url' => company_route('sales-orders.show', ['sales_order' => $order->id]),
            ]);
        }

        $data = $request->validate([
            'payment_term_id' => ['nullable', 'integer'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],

            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],

            // JS sends currency code: AED/USD/GBP
            'currency' => ['required', 'string'],
            'sell_to' => ['nullable', 'exists:customers,uuid'],
            'bill_to' => ['nullable', 'exists:customers,uuid'],

            'items' => ['nullable', 'array'],
            'items.*.variant_id' => ['required_with:items'],
            'items.*.qty' => ['required_with:items', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $vatRate = (float) ($data['vat_rate'] ?? 0);

            // Fold "additional" into shipping_total because your orders table does NOT have additional_total
            $shipping = (float) ($data['shipping'] ?? 0);
            $additional = (float) ($data['additional'] ?? 0);
            $shippingTotal = max(0, $shipping + $additional);

            // Compute shipping tax (if you want shipping to be taxable)
            $shippingTaxTotal = max(0, $shippingTotal * ($vatRate / 100));

            // Resolve currency_id from code (AED/USD/GBP)
            $currencyId = $this->resolveCurrencyIdFromCode($data['currency']);

            // Payment term snapshot
            $termSnapshot = $this->resolvePaymentTermSnapshot($data['payment_term_id'] ?? null);

            // Calculate items & order totals from payload
            $calc = $this->calculateFromItemsPayload(
                $data['items'] ?? [],
                $vatRate
            );

            // Update addresses if sell_to/bill_to provided
            if (! empty($data['sell_to'])) {
                $sellToCustomer = Customer::where('company_id', $company->id)->where('uuid', $data['sell_to'])->first();
                if ($sellToCustomer) {
                    $order->update(['customer_id' => $sellToCustomer->id]);

                    // Update Shipping Address
                    $order->shippingAddress()->updateOrCreate(
                        ['type' => 'SHIPPING'],
                        [
                            'company_id' => $company->id,
                            'address_line_1' => $sellToCustomer->address->address_line_1 ?? '',
                            'address_line_2' => $sellToCustomer->address->address_line_2 ?? '',
                            'city' => $sellToCustomer->address->city ?? '',
                            'state_id' => $sellToCustomer->address->state_id ?? '',
                            'zip_code' => $sellToCustomer->address->zip_code ?? '',
                            'country_id' => $sellToCustomer->address->country_id ?? '',
                            'phone' => $sellToCustomer->phone,
                            'email' => $sellToCustomer->email,
                        ]
                    );
                }
            }

            if (! empty($data['bill_to'])) {
                $billToCustomer = Customer::where('company_id', $company->id)->where('uuid', $data['bill_to'])->first();
                if ($billToCustomer) {
                    // Update Billing Address
                    $order->billingAddress()->updateOrCreate(
                        ['type' => 'BILLING'],
                        [
                            'company_id' => $company->id,
                            'address_line_1' => $billToCustomer->address->address_line_1 ?? '',
                            'address_line_2' => $billToCustomer->address->address_line_2 ?? '',
                            'city' => $billToCustomer->address->city ?? '',
                            'state_id' => $billToCustomer->address->state_id ?? '',
                            'zip_code' => $billToCustomer->address->zip_code ?? '',
                            'country_id' => $billToCustomer->address->country_id ?? '',
                            'phone' => $billToCustomer->phone,
                            'email' => $billToCustomer->email,
                        ]
                    );
                }
            }

            // Update order header (ONLY fields that exist in your migration)
            $order->update([
                'currency_id' => $currencyId,

                'payment_term_id' => $termSnapshot['payment_term_id'],
                'payment_term_name' => $termSnapshot['payment_term_name'],
                'payment_due_days' => $termSnapshot['payment_due_days'],
                'due_date' => $data['due_date'] ?? $order->due_date,

                'notes' => $data['notes'] ?? null,

                'subtotal' => $calc['subtotal'],
                'discount_total' => $calc['discount_total'],
                'tax_total' => $calc['tax_total'],

                'shipping_total' => $shippingTotal,
                'shipping_tax_total' => $shippingTaxTotal,

                // Items grand + shipping + shipping tax
                'grand_total' => $calc['items_grand_total'] + $shippingTotal + $shippingTaxTotal,
            ]);

            // Sync items: delete + recreate (simple and safe for draft)
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

    public function printOrder(Company $company, $uuid)
    {
        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items.productVariant.product', 'customer', 'billingAddress.country', 'shippingAddress.country', 'company', 'currency'])
            ->firstOrFail();

        return view('theme.adminlte.sales.orders.print', compact('order'));
    }

    public function invoicePreview(Company $company, $orderId)
    {
        $order = Order::where('company_id', $company->id)
            ->where('id', $orderId) // Route uses ID, not UUID based on web.php definition
            ->with(['items.productVariant.product', 'customer', 'billingAddress.country', 'shippingAddress.country', 'company', 'currency'])
            ->firstOrFail();

        // Use the same print view but potentially with a flag or just render it
        // The user asked for "Preview the pdf".
        // We can either return the PDF view (HTML) to be shown in iframe/modal
        // or generate a real PDF if using a library like DOMPDF.
        // Given existing code uses HTML views for 'print', we will return that.

        return view('theme.adminlte.sales.orders.print', compact('order'));
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
     * Build order_items rows + compute totals using your schema.
     *
     * subtotal = sum(unit_price * qty)
     * discount_total = sum(discount_amount)
     * tax_total = sum(tax_amount)
     * items_grand_total = sum(line_total)
     *
     * tax is applied on (gross - discount) by default.
     */
    private function calculateFromItemsPayload(array $items, float $globalVatRate): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        $itemsGrand = 0.0;

        $rows = [];

        foreach ($items as $item) {
            $variantUuid = $item['variant_id'];
            $qty = max(1, (int) ($item['qty'] ?? 1));
            $unit = (float) ($item['unit_price'] ?? 0);

            $discPct = min(100, max(0, (float) ($item['discount_percent'] ?? 0)));

            // prefer item tax_rate if provided, else global vat
            $taxRate = array_key_exists('tax_rate', $item)
                ? min(100, max(0, (float) $item['tax_rate']))
                : min(100, max(0, $globalVatRate));

            $variant = ProductVariant::with('product')->whereUuid($variantUuid)->firstOrFail();

            $gross = $unit * $qty;
            $discountAmount = $gross * ($discPct / 100);

            $taxable = max(0, $gross - $discountAmount);
            $taxAmount = $taxable * ($taxRate / 100);

            $lineTotal = $taxable + $taxAmount;

            $subtotal += $gross;
            $discountTotal += $discountAmount;
            $taxTotal += $taxAmount;
            $itemsGrand += $lineTotal;

            $rows[] = [
                'product_variant_id' => $variant->id,
                'quantity' => $qty,
                'unit_price' => $unit,

                'product_name' => (string) ($variant->product->name ?? ''),
                'variant_description' => method_exists($variant, 'getVariantDescription')
                    ? $variant->getVariantDescription()
                    : null,

                // Your migration includes discount_amount but NOT discount_percent
                'discount_amount' => $discountAmount,
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

    public function updatePaymentStatus(Request $request, Company $company, $id)
    {
        $order = Order::where('company_id', $company->id)
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'payment_status' => 'required|in:UNPAID,PAID',
        ]);

        $order->update(['payment_status' => $request->payment_status]);

        return response()->json([
            'success' => true,
            'payment_status' => $order->payment_status,
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
