<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesOrderRequest;
use App\Http\Requests\Sales\UpdateSalesOrderCustomersRequest;
use App\Http\Requests\Sales\UpdateSalesOrderPaymentStatusRequest;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use App\Models\Accounting\PaymentStatus;
use App\Models\Accounting\PaymentTerm;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Inventory\InventoryLocation;
use App\Models\Pricing\Currency;
use App\Models\Sales\DeliveryNote;
use App\Models\Sales\DeliveryNoteItem;
use App\Models\Sales\Order;
use App\Models\Sales\OrderStatus;
use Illuminate\Support\Facades\Auth;
use App\Services\Sales\OrderCalculationService;
use App\Services\Inventory\InventoryPostingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function __construct(
        protected OrderCalculationService $calcService,
        protected InventoryPostingService $inventoryService
    ) {}

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
                'created_by' => \Illuminate\Support\Facades\Auth::id(),

                'currency_id' => $defaultCurrencyId,
                'base_currency_id' => $company->base_currency_id ?? null,
                'fx_rate' => 1,
            ]);

            // Addresses snapshots
            $order->addresses()->create([
                'type' => 'BILLING',
                'company_id' => $company->id,
                'address_line_1' => $billToCustomer->address?->address_line_1 ?? '',
                'address_line_2' => $billToCustomer->address?->address_line_2 ?? null,
                'city' => $billToCustomer->address?->city ?? null,
                'state_id' => ! empty($billToCustomer->address?->state_id) ? $billToCustomer->address?->state_id : null,
                'zip_code' => $billToCustomer->address?->zip_code ?? '',
                'country_id' => ! empty($billToCustomer->address?->country_id) ? $billToCustomer->address?->country_id : null,
                'phone' => $billToCustomer->phone,
                'email' => $billToCustomer->email,
                'name' => $billToCustomer->legal_name ?? $billToCustomer->display_name,
                'tax_number' => $billToCustomer->tax_number,
            ]);

            $order->addresses()->create([
                'type' => 'SHIPPING',
                'company_id' => $company->id,
                'address_line_1' => $shipToCustomer->address?->address_line_1 ?? '',
                'address_line_2' => $shipToCustomer->address?->address_line_2 ?? null,
                'city' => $shipToCustomer->address?->city ?? null,
                'state_id' => ! empty($shipToCustomer->address?->state_id) ? $shipToCustomer->address?->state_id : null,
                'zip_code' => $shipToCustomer->address?->zip_code ?? '',
                'country_id' => ! empty($shipToCustomer->address?->country_id) ? $shipToCustomer->address?->country_id : null,
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

        return DB::transaction(function () use ($order, $company, $billToCustomer, $shipToCustomer, $request) {
            $order->update([
                'customer_id' => $billToCustomer->id,
                'shipping_customer_id' => $shipToCustomer->id,
            ]);

            $order->addresses()->updateOrCreate(
                ['type' => 'BILLING'],
                [
                    'company_id' => $company->id,
                    'address_line_1' => $billToCustomer->address?->address_line_1 ?? '',
                    'address_line_2' => $billToCustomer->address?->address_line_2 ?? null,
                    'city' => $billToCustomer->address?->city ?? null,
                    'state_id' => ! empty($billToCustomer->address?->state_id) ? $billToCustomer->address?->state_id : null,
                    'zip_code' => $billToCustomer->address?->zip_code ?? '',
                    'country_id' => ! empty($billToCustomer->address?->country_id) ? $billToCustomer->address?->country_id : null,
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
                    'address_line_1' => $shipToCustomer->address?->address_line_1 ?? '',
                    'address_line_2' => $shipToCustomer->address?->address_line_2 ?? null,
                    'city' => $shipToCustomer->address?->city ?? null,
                    'state_id' => ! empty($shipToCustomer->address?->state_id) ? $shipToCustomer->address?->state_id : null,
                    'zip_code' => $shipToCustomer->address?->zip_code ?? '',
                    'country_id' => ! empty($shipToCustomer->address?->country_id) ? $shipToCustomer->address?->country_id : null,
                    'phone' => $shipToCustomer->phone,
                    'email' => $shipToCustomer->email,
                    'name' => $shipToCustomer->legal_name ?? $shipToCustomer->display_name,
                    'tax_number' => $shipToCustomer->tax_number,
                ]
            );

            if ($request->ajax()) {
                $order->load(['addresses', 'customer', 'shippingCustomer']);
                $billToCustomerModel = $order->customer;
                $billToAddress = $order->addresses->where('type', 'BILLING')->first();
                $shipToAddress = $order->addresses->where('type', 'SHIPPING')->first();

                $customerDiscount = $billToCustomerModel->percentage_discount ?? 0;
                $defaultPaymentTermId = $billToCustomerModel->payment_term_id ?? null;

                return response()->json([
                    'success' => true,
                    'message' => 'Order customers updated successfully.',
                    'order' => $order,
                    'fx_rate' => $order->fx_rate,
                    'totals' => [
                        'subtotal' => (float) $order->subtotal,
                        'discount_total' => (float) $order->discount_total,
                        'tax_total' => (float) $order->tax_total,
                        'shipping_total' => (float) $order->shipping_total,
                        'shipping_tax_total' => (float) $order->shipping_tax_total,
                        'grand_total' => (float) $order->grand_total,
                    ],
                    'customerDiscount' => $customerDiscount,
                    'payment_term_id' => $defaultPaymentTermId,
                    'billing_html' => view('catvara.sales-orders.partials._address_card_content', [
                        'address' => $billToAddress,
                        'name' => $billToAddress->name,
                    ])->render(),
                    'shipping_html' => view('catvara.sales-orders.partials._address_card_content', [
                        'address' => $shipToAddress,
                        'name' => $shipToAddress->name,
                    ])->render(),
                ]);
            }

            return redirect()->to(company_route('sales-orders.edit', ['sales_order' => $order->uuid]));
        });
    }

    public function customerSwitcher(Request $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $type = $request->input('type', 'BILLING'); // BILLING or SHIPPING
        $search = $request->input('q');
        $customerType = $request->input('customer_type'); // INDIVIDUAL or COMPANY

        $query = Customer::where('company_id', $company->id)
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'LIKE', "%{$search}%")
                    ->orWhere('legal_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('customer_code', 'LIKE', "%{$search}%");
            });
        }

        if ($customerType) {
            $query->where('type', $customerType);
        }

        $customers = $query->orderBy('display_name')->limit(50)->get();

        return view('catvara.sales-orders.partials._customer_switcher', compact('customers', 'order', 'type'))->render();
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
                    'tax_group_id' => $item->tax_group_id,
                    'taxRate' => (float) ($item->tax_rate ?? 0),
                    'attrs' => [],
                ];
            })->values(),

            'payment_term_id' => $order->payment_term_id ?? $billToCustomer->payment_term_id,
            'shipping' => (float) $order->shipping_total,
            'additional' => 0,
            'tax_group_id' => $order->tax_group_id,
            'global_discount_percent' => (float) $order->global_discount_percent,
            'global_discount_amount' => (float) $order->global_discount_amount,
            'notes' => $order->notes,
            'currency' => $order->currency->code ?? ($company->baseCurrency->code ?? 'AED'),
            'status' => $order->status->code ?? 'DRAFT',
        ];

        $customerDiscount = $billToCustomer->percentage_discount ?? 0;

        $taxGroups = \App\Models\Tax\TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $baseCurrency = $company->baseCurrency;
        $exchangeRates = $company->exchangeRates()->with('targetCurrency')->get();
        $enabledCurrencies = collect([$baseCurrency])->merge($exchangeRates->pluck('targetCurrency'))->filter()->unique('id');

        return view('catvara.sales-orders.edit', compact(
            'billToCustomer',
            'shipToCustomer',
            'order',
            'initialState',
            'customerDiscount',
            'taxGroups',
            'enabledCurrencies'
        ));
    }

    public function update(UpdateSalesOrderRequest $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $taxGroupId = $request->tax_group_id ? (int) $request->tax_group_id : null;

            $currencyId = $this->resolveCurrencyIdFromCode($request->currency);

            $termSnapshot = $this->resolvePaymentTermSnapshot($request->payment_term_id);

            $calc = $this->calcService->calculate($company->id, [
                'items' => $request->items ?? [],
                'tax_group_id' => $taxGroupId,
                'global_discount_percent' => $request->global_discount_percent,
                'global_discount_amount' => $request->global_discount_amount,
                'shipping' => $request->shipping,
                'additional' => $request->additional,
                'customer_id' => $order->customer_id,
            ]);

            // Update order header
            $order->update([
                'currency_id' => $currencyId,
                'tax_group_id' => $taxGroupId,

                'payment_term_id' => $termSnapshot['payment_term_id'],
                'payment_term_name' => $termSnapshot['payment_term_name'],
                'payment_due_days' => $termSnapshot['payment_due_days'],
                'due_date' => $request->due_date ?? $order->due_date,

                'notes' => $request->notes ?? null,

                'subtotal' => $calc['subtotal'],
                'discount_total' => $calc['discount_total'],
                'global_discount_percent' => (float) ($request->global_discount_percent ?? 0),
                'global_discount_amount' => (float) ($request->global_discount_amount ?? 0),
                'tax_total' => $calc['tax_total'],

                'shipping_total' => $calc['shipping_total'],
                'shipping_tax_total' => $calc['shipping_tax_total'],

                'grand_total' => $calc['grand_total'],
            ]);

            // Sync items: delete + recreate (draft-safe)
            $order->items()->delete();
            foreach ($calc['items_for_db'] as $row) {
                $order->items()->create($row);
            }

            $order->load(['items.productVariant.product', 'customer', 'shippingCustomer', 'currency', 'status', 'addresses']);

            if ($request->action === 'generate') {
                $status = OrderStatus::where('code', 'CONFIRMED')->first();
                if ($status) {
                    $order->update(['status_id' => $status->id, 'confirmed_at' => now()]);
                }
                DB::commit();

                return response()->json([
                    'success' => true,
                    'redirect_url' => company_route('sales-orders.show', ['sales_order' => $order->uuid]),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order' => $order,
                'fx_rate' => $order->fx_rate,
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
            ->where('uuid', $id)
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
                'deliveryNotes.items.orderItem',
            ])
            ->firstOrFail();

        $locations = InventoryLocation::where('company_id', $company->id)
            ->where('is_active', true)
            ->with(['locatable'])
            ->get()
            ->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'name' => $loc->locatable->name ?? ucfirst($loc->type),
                ];
            });

        // Fulfillment Summary Stats
        $totalItems = $order->items->count();
        $totalQuantityOrdered = $order->items->sum('quantity');
        $totalQuantityFulfilled = $order->items->sum('fulfilled_quantity');
        
        $fulfillmentPercentage = $totalQuantityOrdered > 0 
            ? round(($totalQuantityFulfilled / $totalQuantityOrdered) * 100, 1) 
            : 0;

        $fullyFulfilledCount = $order->items->filter(fn($i) => $i->fulfilled_quantity >= $i->quantity)->count();
        $partialFulfilledCount = $order->items->filter(fn($i) => $i->fulfilled_quantity > 0 && $i->fulfilled_quantity < $i->quantity)->count();
        $notFulfilledCount = $order->items->filter(fn($i) => $i->fulfilled_quantity <= 0)->count();

        $stats = [
            'total_items' => $totalItems,
            'fully' => $fullyFulfilledCount,
            'partial' => $partialFulfilledCount,
            'none' => $notFulfilledCount,
            'percentage' => $fulfillmentPercentage,
            'total_ordered' => (float)$totalQuantityOrdered,
            'total_fulfilled' => (float)$totalQuantityFulfilled,
        ];

        return view('catvara.sales-orders.show', compact('order', 'locations', 'stats'));
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

    public function printOrder(Company $company, $uuid)
    {
        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with([
                'items.productVariant.product',
                'customer',
                'billingAddress',
                'shippingAddress',
                'company',
                'currency',
                'paymentTerm',
            ])
            ->firstOrFail();

        return view('catvara.sales-orders.print', compact('order'));
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

    public function finalize(Company $company, string $sales_order)
    {
        $order = Order::query()
            ->where('company_id', $company->id)
            ->where('uuid', $sales_order)
            ->with([
                'currency',
                'items.productVariant.product',
                'paymentTerm',
                'customer',
                'shippingCustomer',
                'addresses',
            ])
            ->firstOrFail();

        $initialState = null; // No longer using draft_state column

        $billToCustomer = $order->customer;
        $shipToCustomer = $order->shippingCustomer;

        // Fallback addresses if specifically needed as objects for the view
        $billAddress = $order->addresses->where('type', 'BILLING')->first();
        $shipAddress = $order->addresses->where('type', 'SHIPPING')->first();

        $customerDiscount = $billAddress->percentage_discount ?? 0;

        $taxGroups = \App\Models\Tax\TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $baseCurrency = $company->baseCurrency;
        $exchangeRates = $company->exchangeRates()->with('targetCurrency')->get();
        $enabledCurrencies = collect([$baseCurrency])->merge($exchangeRates->pluck('targetCurrency'))->filter()->unique('id');

        return view('catvara.sales-orders.finalize', compact(
            'order',
            'initialState',
            'billToCustomer',
            'shipToCustomer',
            'customerDiscount',
            'taxGroups',
            'enabledCurrencies'
        ));
    }

    public function finalizeStore(Request $request, Company $company, string $sales_order)
    {
        $order = Order::query()
            ->where('uuid', $sales_order)
            ->with(['items'])
            ->firstOrFail();

        $validated = $request->validate([
            'currency' => ['required', 'string', 'max:10'],
            'payment_term_id' => ['nullable'],
            'due_date' => ['nullable', 'date'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'tax_group_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],

            'items.*.type' => ['required', 'in:variant,custom'],
            'items.*.variant_id' => ['nullable', 'string'],
            'items.*.custom_name' => ['nullable', 'string', 'max:255'],
            'items.*.custom_sku' => ['nullable', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax_group_id' => ['nullable', 'integer'],
        ]);

        /* Enforced payment method check removed per user request */

        DB::transaction(function () use ($order, $validated, $company, $request) {
            $taxGroupId = $validated['tax_group_id'] ? (int) $validated['tax_group_id'] : null;

            $totals = $this->calcService->calculate($company->id, [
                'items' => $validated['items'],
                'tax_group_id' => $taxGroupId,
                'global_discount_percent' => $request->global_discount_percent,
                'global_discount_amount' => $request->global_discount_amount,
                'shipping' => $validated['shipping'] ?? 0,
                'additional' => $validated['additional'] ?? 0,
                'customer_id' => $order->customer_id,
            ]);

            $currencyId = $this->resolveCurrencyIdFromCode($validated['currency']);
            $termSnapshot = $this->resolvePaymentTermSnapshot($validated['payment_term_id']);

            // Save finalized data
            $order->currency_id = $currencyId;
            $order->tax_group_id = $taxGroupId;
            $order->payment_term_id = $termSnapshot['payment_term_id'];
            $order->payment_term_name = $termSnapshot['payment_term_name'];
            $order->payment_due_days = $termSnapshot['payment_due_days'];
            $order->due_date = $validated['due_date'] ?? $order->due_date;

            $order->tax_total = $totals['tax_total'];
            $order->subtotal = $totals['subtotal'];
            $order->discount_total = $totals['discount_total'];
            $order->global_discount_percent = (float) ($request->global_discount_percent ?? 0);
            $order->global_discount_amount = (float) ($request->global_discount_amount ?? 0);
            $order->notes = $validated['notes'] ?? null;

            $order->shipping_total = $totals['shipping_total'];
            $order->shipping_tax_total = $totals['shipping_tax_total'];
            $order->grand_total = $totals['grand_total'];

            // ✅ Finalize status
            $status = OrderStatus::where('code', 'CONFIRMED')->first();
            if ($status) {
                $order->status_id = $status->id;
            }
            $order->confirmed_at = now();
            $order->save();

            // Sync items to normalized table
            $order->items()->delete();
            foreach ($totals['items_for_db'] as $row) {
                $order->items()->create($row);
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Order confirmed. Invoice generated and fulfillment team notified.',
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

    public function generateDeliveryNote(Request $request, Company $company, string $sales_order)
    {
        $order = Order::where('company_id', $company->id)
            ->where('uuid', $sales_order)
            ->with(['items'])
            ->firstOrFail();

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'inventory_location_id' => ['required', 'exists:inventory_locations,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($order, $company, $validated) {
            $dn = DeliveryNote::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $company->id,
                'order_id' => $order->id,
                'inventory_location_id' => $validated['inventory_location_id'],
                'delivery_note_number' => $this->generateDeliveryNoteNumber($company),
                'status' => DeliveryNote::STATUS_SHIPPED,
                'reference_number' => $validated['reference_number'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'shipped_at' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            $anyFulfilledInThisSession = false;

            foreach ($validated['items'] as $inputItem) {
                if ($inputItem['quantity'] <= 0) {
                    continue;
                }

                $orderItem = $order->items->firstWhere('id', $inputItem['order_item_id']);
                if (! $orderItem) {
                    continue;
                }

                // Safety: Cannot fulfill more than ordered
                $remaining = (float) $orderItem->quantity - (float) $orderItem->fulfilled_quantity;
                $deliverQty = min((float) $inputItem['quantity'], $remaining);

                if ($deliverQty > 0) {
                    DeliveryNoteItem::create([
                        'delivery_note_id' => $dn->id,
                        'order_item_id' => $orderItem->id,
                        'quantity' => $deliverQty,
                    ]);

                    $orderItem->fulfilled_quantity = (float) $orderItem->fulfilled_quantity + $deliverQty;
                    $orderItem->save();

                    // Inventory Integration: Record movement if it's a real product
                    if ($orderItem->product_variant_id) {
                        try {
                            $this->inventoryService->postMovement([
                                'company_id' => $company->id,
                                'inventory_location_id' => $dn->inventory_location_id,
                                'product_variant_id' => $orderItem->product_variant_id,
                                'reason_code' => 'SALE',
                                'quantity' => $deliverQty,
                                'reference_type' => 'delivery_note',
                                'reference_id' => $dn->id,
                                'performed_by' => Auth::id(),
                            ]);
                        } catch (\Exception $e) {
                            // If stock is insufficient (and not allowed negative), we fail the transaction
                            throw new \Exception("Insufficient stock for item: {$orderItem->product_name} at selected location.");
                        }
                    }

                    $anyFulfilledInThisSession = true;
                }
            }

            if (! $anyFulfilledInThisSession) {
                // If nothing was actually delivered, rollback
                throw new \Exception('No items were selected for delivery or they are already fully delivered.');
            }

            // Sync order status
            $totalOrdered = $order->items->sum('quantity');
            $totalFulfilled = $order->items->sum('fulfilled_quantity');

            $statusCode = 'CONFIRMED';
            if ($totalFulfilled >= $totalOrdered) {
                $statusCode = 'FULFILLED';
            } elseif ($totalFulfilled > 0) {
                $statusCode = 'PARTIALLY_FULFILLED';
            }

            $status = OrderStatus::where('code', $statusCode)->first();
            if ($status && $order->status_id !== $status->id) {
                $order->status_id = $status->id;
                $order->save();
            }

            return response()->json([
                'ok' => true,
                'message' => 'Delivery Note generated successfully.',
                'redirect' => company_route('sales-orders.delivery-note.print', ['delivery_note' => $dn->uuid]),
            ]);
        });
    }

    public function printDeliveryNote(Company $company, string $delivery_note)
    {
        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->with([
                'order.customer',
                'order.billingAddress.country',
                'order.shippingAddress.country',
                'order.company',
                'items.orderItem',
            ])
            ->firstOrFail();

        return view('catvara.sales-orders.delivery-note', compact('dn'));
    }

    private function generateDeliveryNoteNumber($company)
    {
        $prefix = 'DN-'.Carbon::now()->format('Ymd').'-';

        $lastDn = DeliveryNote::where('company_id', $company->id)
            ->where('delivery_note_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDn) {
            $lastNum = intval(substr($lastDn->delivery_note_number, strlen($prefix)));

            return $prefix.str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix.'0001';
    }
    public function markDeliveryNoteAsDelivered(Company $company, string $delivery_note)
    {
        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->firstOrFail();

        $dn->update([
            'status' => DeliveryNote::STATUS_DELIVERED,
            'delivered_at' => Carbon::now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Delivery Note marked as DELIVERED.',
        ]);
    }

    public function deleteDeliveryNote(Company $company, string $delivery_note)
    {
        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->with(['items.orderItem', 'order.items'])
            ->firstOrFail();

        return DB::transaction(function () use ($dn) {
            $order = $dn->order;

            // Rollback quantities and inventory movements
            foreach ($dn->items as $item) {
                if ($item->orderItem) {
                    $item->orderItem->fulfilled_quantity = max(0, (float) $item->orderItem->fulfilled_quantity - (float) $item->quantity);
                    $item->orderItem->save();

                    // Inventory Integration: Reverse movement
                    if ($item->orderItem->product_variant_id && $dn->inventory_location_id) {
                        $this->inventoryService->postMovement([
                            'company_id' => $dn->company_id,
                            'inventory_location_id' => $dn->inventory_location_id,
                            'product_variant_id' => $item->orderItem->product_variant_id,
                            'reason_code' => 'RETURN_IN', // Simple restoral
                            'quantity' => $item->quantity,
                            'reference_type' => 'delivery_note_reversal',
                            'reference_id' => $dn->id,
                            'performed_by' => Auth::id(),
                        ]);
                    }
                }
            }

            // Sync order status after rollback
            // Note: We need to reload order items to get fresh fulfilled_quantity
            $order->load('items');
            $totalOrdered = $order->items->sum('quantity');
            $totalFulfilled = $order->items->sum('fulfilled_quantity');

            $statusCode = 'CONFIRMED';
            if ($totalFulfilled >= $totalOrdered && $totalOrdered > 0) {
                $statusCode = 'FULFILLED';
            } elseif ($totalFulfilled > 0) {
                $statusCode = 'PARTIALLY_FULFILLED';
            }

            $status = OrderStatus::where('code', $statusCode)->first();
            if ($status && $order->status_id !== $status->id) {
                $order->status_id = $status->id;
                $order->save();
            }

            $dn->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Delivery Note deleted and quantities restored.',
            ]);
        });
    }
}
