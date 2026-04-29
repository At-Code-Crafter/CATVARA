<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\FinalizeSalesOrderRequest;
use App\Http\Requests\Sales\StoreSalesOrderRequest;
use App\Http\Requests\Sales\UpdateSalesOrderCustomersRequest;
use App\Http\Requests\Sales\UpdateSalesOrderPaymentStatusRequest;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use App\Models\Accounting\PaymentStatus;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Inventory\InventoryLocation;
use App\Models\Pricing\Currency;
use App\Models\Sales\DeliveryNote;
use App\Models\Sales\Order;
use App\Models\Sales\OrderItemBox;
use App\Models\Sales\OrderStatus;
use App\Services\Inventory\InventoryPostingService;
use App\Services\Sales\SalesCalculationService;
use App\Services\Sales\SalesDocumentService;
use App\Services\Sales\TaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SalesOrderController extends Controller
{
    public function __construct(
        protected SalesCalculationService $calcService,
        protected SalesDocumentService $docService,
        protected TaxService $taxService,
        protected InventoryPostingService $inventoryService,
        protected \App\Services\Sales\DeliveryNoteService $deliveryNoteService,
        protected \App\Services\Common\DocumentNumberService $docNumberService
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
        $this->authorize('view', 'orders');

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
            ->editColumn('order_number', fn($order) => '<span class="font-weight-bold">' . e($order->order_number) . '</span>')
            ->editColumn('created_at', fn($order) => optional($order->created_at)->format('M d, Y'))
            ->addColumn('customer_name', fn($order) => $order->customer->display_name ?? 'N/A')
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

                return '<span class="badge badge-' . $color . '">' . e($order->status->name ?? '—') . '</span>';
            })
            ->editColumn('grand_total', function ($order) {
                $amount = number_format((float) $order->grand_total, 2);
                $cur = $order->currency->code ?? '';

                return '<span class="font-weight-bold text-dark">' . e($cur) . ' ' . $amount . '</span>';
            })
            ->addColumn('actions', function ($order) {
                $editUrl = company_route('sales-orders.edit', ['sales_order' => $order->uuid]);
                $showUrl = company_route('sales-orders.show', ['sales_order' => $order->uuid]);

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

        $countries = \App\Models\Common\Country::active()->ordered()->get();

        return view('catvara.sales-orders.create', [
            'editOrder' => $editOrder,
            'countries' => $countries,
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

                'order_number' => $this->docNumberService->generate(
                    companyId: $company->id,
                    documentType: 'ORDER',
                    channel: 'SALES',
                    year: now()->year
                ),
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

        return DB::transaction(function () use ($order, $billToCustomer, $shipToCustomer, $request) {
            $order->update([
                'customer_id' => $billToCustomer->id,
                'shipping_customer_id' => $shipToCustomer->id,
            ]);

            $this->docService->syncAddressSnapshots($order, $billToCustomer, $shipToCustomer);

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

    public function updateShippingAddress(Request $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address_line_1' => 'required|string|max:500',
            'address_line_2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|integer|exists:countries,id',
            'state_id' => 'nullable|integer|exists:states,id',
        ]);

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $shippingAddress = $order->shippingAddress;

        if (!$shippingAddress) {
            return response()->json(['success' => false, 'message' => 'Shipping address not found.'], 404);
        }

        $shippingAddress->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'zip_code' => $request->zip_code,
            'country_id' => $request->country_id ?: null,
            'state_id' => $request->state_id ?: null,
        ]);

        $shippingAddress->load(['state', 'country']);

        return response()->json([
            'success' => true,
            'message' => 'Shipping address updated.',
            'shipping_html' => view('catvara.sales-orders.partials._address_card_content', [
                'address' => $shippingAddress,
                'name' => $shippingAddress->name,
            ])->render(),
        ]);
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

        $countries = \App\Models\Common\Country::active()->ordered()->get();

        return view('catvara.sales-orders.edit', compact(
            'billToCustomer',
            'shipToCustomer',
            'order',
            'initialState',
            'customerDiscount',
            'taxGroups',
            'enabledCurrencies',
            'countries'
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

            $currencyId = $this->docService->resolveCurrencyId($request->currency);

            $termSnapshot = $this->docService->resolvePaymentTermSnapshot($request->payment_term_id);

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
                'discount_total' => $calc['discount_total'] ?? 0,
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
        $this->authorize('view', 'orders');

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
            'total_ordered' => (float) $totalQuantityOrdered,
            'total_fulfilled' => (float) $totalQuantityFulfilled,
        ];

        return view('catvara.sales-orders.show', compact('order', 'locations', 'stats'));
    }

    public function updatePaymentStatus(UpdateSalesOrderPaymentStatusRequest $request, Company $company, $id)
    {
        $this->authorize('edit', 'orders');

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
        $this->authorize('view', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with([
                'items.productVariant.product',
                'customer',
                'billingAddress',
                'shippingAddress',
                'company.banks',
                'company.detail',
                'currency',
                'paymentTerm',
            ])
            ->firstOrFail();

        return view('catvara.sales-orders.print', compact('order'));
    }

    public function printProforma(Company $company, $uuid)
    {
        $this->authorize('view', 'orders');

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

        return view('catvara.sales-orders.print-proforma', compact('order'));
    }

    public function boxLabels(Company $company, $uuid)
    {
        $this->authorize('view', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with([
                'items.productVariant.product',
                'customer',
                'shippingAddress.state',
                'shippingAddress.country',
                'company.detail',
                'currency',
                'boxItems.orderItem.productVariant',
            ])
            ->firstOrFail();

        // Group box items by box_number
        $boxes = $order->boxItems->groupBy('box_number')->sortKeys();

        return view('catvara.sales-orders.box-labels', compact('order', 'boxes'));
    }

    public function boxLabelPreview(Company $company, $uuid, $boxNumber)
    {
        $this->authorize('view', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with([
                'items.productVariant.product',
                'customer',
                'shippingAddress.state',
                'shippingAddress.country',
                'company.detail',
                'currency',
            ])
            ->firstOrFail();

        $boxNumber = (int) $boxNumber;

        $boxItems = OrderItemBox::where('order_id', $order->id)
            ->where('box_number', $boxNumber)
            ->with('orderItem.productVariant')
            ->get();

        abort_if($boxItems->isEmpty(), 404);

        // Count total boxes for this order
        $totalBoxes = OrderItemBox::where('order_id', $order->id)
            ->distinct('box_number')
            ->count('box_number');

        return view('catvara.sales-orders.box-label-print', compact('order', 'boxItems', 'boxNumber', 'totalBoxes'));
    }

    public function createDeliveryNote(Company $company, $uuid)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with([
                'items.productVariant.product',
                'items.productVariant.inventory',
                'customer',
                'shippingAddress.state',
                'shippingAddress.country',
                'company.detail',
                'currency',
                'invoice',
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

        $deliveryServices = \App\Models\Sales\DeliveryService::where('company_id', $company->id)
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        return view('catvara.sales-orders.create-delivery-note', compact('order', 'locations', 'deliveryServices'));
    }

    /* ===================== HELPERS ===================== */
    // Helper methods moved to SalesDocumentService

    public function finalize(Company $company, string $sales_order)
    {
        $this->authorize('edit', 'orders');

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

    public function finalizeStore(FinalizeSalesOrderRequest $request, Company $company, string $sales_order)
    {
        $this->authorize('edit', 'orders');

        $order = Order::query()
            ->where('uuid', $sales_order)
            ->with(['items'])
            ->firstOrFail();

        $validated = $request->validated();

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

            $currencyId = $this->docService->resolveCurrencyId($validated['currency']);
            $termSnapshot = $this->docService->resolvePaymentTermSnapshot($validated['payment_term_id']);

            // Save finalized data
            $order->currency_id = $currencyId;
            $order->tax_group_id = $taxGroupId;
            $order->payment_term_id = $termSnapshot['payment_term_id'];
            $order->payment_term_name = $termSnapshot['payment_term_name'];
            $order->payment_due_days = $termSnapshot['payment_due_days'];
            $order->due_date = $validated['due_date'] ?? $order->due_date;

            $order->tax_total = $totals['tax_total'];
            $order->subtotal = $totals['subtotal'];
            $order->discount_total = $totals['discount_total'] ?? 0;
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

    public function generateDeliveryNote(Request $request, Company $company, string $sales_order)
    {
        $this->authorize('edit', 'orders');

        $request->validate([
            'inventory_location_id' => ['required', 'exists:inventory_locations,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'delivery_service_id' => ['nullable', 'exists:delivery_services,id'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'boxes' => ['required', 'array', 'min:1'],
            'boxes.*.box_number' => ['required', 'integer', 'min:1'],
            'boxes.*.items' => ['required', 'array', 'min:1'],
            'boxes.*.items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'boxes.*.items.*.quantity' => ['required', 'numeric', 'min:0.000001'],
            'boxes.*.items.*.weight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $sales_order)
            ->with(['items'])
            ->firstOrFail();

        $validated = $request->all();

        return DB::transaction(function () use ($order, $company, $validated) {
            // Aggregate quantities per order_item_id from all boxes
            $aggregatedItems = [];
            foreach ($validated['boxes'] as $box) {
                foreach ($box['items'] as $boxItem) {
                    $itemId = $boxItem['order_item_id'];
                    $qty = (float) $boxItem['quantity'];
                    if (! isset($aggregatedItems[$itemId])) {
                        $aggregatedItems[$itemId] = 0;
                    }
                    $aggregatedItems[$itemId] += $qty;
                }
            }

            // Convert to items array for DN service
            $itemsData = [];
            foreach ($aggregatedItems as $orderItemId => $totalQty) {
                $itemsData[] = [
                    'order_item_id' => $orderItemId,
                    'quantity' => $totalQty,
                ];
            }

            $dn = $this->deliveryNoteService->createFromOrder($order, $itemsData);

            $dn->update([
                'inventory_location_id' => $validated['inventory_location_id'],
                'reference_number' => $validated['reference_number'] ?? null,
                'po_number' => $validated['po_number'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'delivery_service_id' => $validated['delivery_service_id'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Save box assignments
            foreach ($validated['boxes'] as $box) {
                foreach ($box['items'] as $boxItem) {
                    OrderItemBox::create([
                        'company_id' => $company->id,
                        'order_id' => $order->id,
                        'delivery_note_id' => $dn->id,
                        'order_item_id' => $boxItem['order_item_id'],
                        'box_number' => $box['box_number'],
                        'quantity' => $boxItem['quantity'],
                        'weight' => $boxItem['weight'] ?? null,
                    ]);
                }
            }

            // Inventory posting
            $dn->load('items.orderItem');
            $anyFulfilled = false;

            if (! $order->is_fulfilled) {
                foreach ($dn->items as $dnItem) {
                    $orderItem = $dnItem->orderItem;
                    if ($orderItem && $orderItem->product_variant_id) {
                        try {
                            $this->inventoryService->postMovement([
                                'company_id' => $company->id,
                                'inventory_location_id' => $dn->inventory_location_id,
                                'product_variant_id' => $orderItem->product_variant_id,
                                'reason_code' => 'SALE',
                                'quantity' => $dnItem->quantity,
                                'reference_type' => 'delivery_note',
                                'reference_id' => $dn->id,
                                'performed_by' => Auth::id(),
                            ]);
                        } catch (\Exception $e) {
                            throw new \Exception("Insufficient stock for item: {$orderItem->product_name}");
                        }
                    }
                    if ($dnItem->quantity > 0) {
                        $anyFulfilled = true;
                    }
                }
            } else {
                foreach ($dn->items as $dnItem) {
                    if ($dnItem->quantity > 0) {
                        $anyFulfilled = true;
                        break;
                    }
                }
            }

            if (! $anyFulfilled) {
                throw new \Exception('No items were selected for delivery or they are already fully delivered.');
            }

            // Sync order status and check if fully fulfilled
            $order->load('items'); // Reload to get updated fulfilled_quantity
            $totalOrdered = $order->items->sum('quantity');
            $totalFulfilled = $order->items->sum('fulfilled_quantity');

            $statusCode = 'CONFIRMED';
            $isFullyFulfilled = false;
            if ($totalFulfilled >= $totalOrdered && $totalOrdered > 0) {
                $statusCode = 'FULFILLED';
                $isFullyFulfilled = true;
            } elseif ($totalFulfilled > 0) {
                $statusCode = 'PARTIALLY_FULFILLED';
            }

            $status = OrderStatus::where('code', $statusCode)->first();
            if ($status && $order->status_id !== $status->id) {
                $order->status_id = $status->id;
            }

            // Auto-mark as fulfilled if all items are delivered
            if ($isFullyFulfilled && ! $order->is_fulfilled) {
                $order->is_fulfilled = true;
            }

            $order->save();

            return response()->json([
                'ok' => true,
                'message' => 'Delivery Note generated successfully.',
                'redirect' => company_route('sales-orders.delivery-note.print', ['delivery_note' => $dn->uuid]),
            ]);
        });
    }

    public function printDeliveryNote(Company $company, string $delivery_note)
    {
        $this->authorize('view', 'orders');

        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->with([
                'order.customer',
                'order.currency',
                'order.billingAddress.country',
                'order.shippingAddress.country',
                'order.company.detail',
                'inventoryLocation.locatable',
                'deliveryService',
                'items.orderItem',
                'boxItems.orderItem',
            ])
            ->firstOrFail();

        return view('catvara.sales-orders.delivery-note', compact('dn'));
    }

    public function printLabel(Company $company, string $delivery_note)
    {
        $this->authorize('view', 'orders');

        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->with([
                'order.customer',
                'order.shippingAddress.country',
                'order.company',
                'items.orderItem',
            ])
            ->firstOrFail();

        return view('catvara.sales-orders.print-label', compact('dn'));
    }

    /* Numbering handled by DeliveryNoteService */

    public function markDeliveryNoteAsDelivered(Company $company, string $delivery_note)
    {
        $this->authorize('edit', 'orders');

        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->firstOrFail();

        $this->deliveryNoteService->markAsDelivered($dn);

        return response()->json([
            'ok' => true,
            'message' => 'Delivery Note marked as DELIVERED.',
        ]);
    }

    public function deleteDeliveryNote(Company $company, string $delivery_note)
    {
        $this->authorize('delete', 'orders');

        $dn = DeliveryNote::where('company_id', $company->id)
            ->where('uuid', $delivery_note)
            ->with(['items.orderItem', 'order'])
            ->firstOrFail();

        return DB::transaction(function () use ($dn) {
            $order = $dn->order;

            // Inventory Integration: Reverse movements before deleting
            foreach ($dn->items as $item) {
                if ($item->orderItem && $item->orderItem->product_variant_id && $dn->inventory_location_id) {
                    $this->inventoryService->postMovement([
                        'company_id' => $dn->company_id,
                        'inventory_location_id' => $dn->inventory_location_id,
                        'product_variant_id' => $item->orderItem->product_variant_id,
                        'reason_code' => 'RESTOCK', // Reversal
                        'quantity' => $item->quantity,
                        'reference_type' => 'delivery_note_reversal',
                        'reference_id' => $dn->id,
                        'performed_by' => Auth::id(),
                    ]);
                }
            }

            // Centralized rollback of fulfillment quantities
            $this->deliveryNoteService->delete($dn);

            // Sync order status after rollback
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
                $order->update(['status_id' => $status->id]);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Delivery Note deleted and quantities restored.',
            ]);
        });
    }

    /**
     * Mark an order as fulfilled (updates stock without creating delivery note)
     */
    public function markAsFulfillment(Request $request, Company $company, string $sales_order)
    {
        $this->authorize('edit', 'orders');

        $order = Order::where('company_id', $company->id)
            ->where('uuid', $sales_order)
            ->with(['items'])
            ->firstOrFail();

        if ($order->is_fulfilled) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already marked as fulfilled.',
            ], 400);
        }

        // Get only warehouse inventory locations for the company (exclude stores)
        $inventoryLocations = InventoryLocation::where('company_id', $company->id)
            ->where('type', 'warehouse')
            ->get();
        if ($inventoryLocations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No warehouse location found. Please set up a warehouse first.',
            ], 400);
        }

        return DB::transaction(function () use ($order, $company, $inventoryLocations) {
            // Update stock for each item - only for PENDING quantity (not already delivered)
            foreach ($order->items as $orderItem) {
                // Calculate pending quantity: order qty - already fulfilled via delivery notes
                $pendingQty = $orderItem->quantity - ($orderItem->fulfilled_quantity ?? 0);

                if ($orderItem->product_variant_id && $pendingQty > 0) {
                    // Collect available stock from all warehouses (stores excluded)
                    $locationStocks = [];
                    $totalAvailable = 0;
                    foreach ($inventoryLocations as $location) {
                        $availableStock = $this->inventoryService->getAvailableStock(
                            $company->id,
                            $location->id,
                            $orderItem->product_variant_id
                        );
                        if ($availableStock > 0) {
                            $locationStocks[] = [
                                'location' => $location,
                                'available' => $availableStock,
                            ];
                            $totalAvailable += $availableStock;
                        }
                    }

                    // Check if total stock across all warehouses is sufficient
                    if ($totalAvailable < $pendingQty) {
                        throw new \Exception("Insufficient stock for item: {$orderItem->product_name}. Required: {$pendingQty}, Available (across all warehouses): {$totalAvailable}");
                    }

                    // Deduct from multiple warehouses as needed
                    $remainingQty = $pendingQty;
                    foreach ($locationStocks as $locStock) {
                        if ($remainingQty <= 0) {
                            break;
                        }

                        // Deduct as much as available from this location
                        $deductQty = min($remainingQty, $locStock['available']);

                        try {
                            $this->inventoryService->postMovement([
                                'company_id' => $company->id,
                                'inventory_location_id' => $locStock['location']->id,
                                'product_variant_id' => $orderItem->product_variant_id,
                                'reason_code' => 'SALE',
                                'quantity' => $deductQty,
                                'reference_type' => 'order_fulfillment',
                                'reference_id' => $order->id,
                                'performed_by' => Auth::id(),
                            ]);
                        } catch (\Exception $e) {
                            throw new \Exception("Failed to deduct stock for item: {$orderItem->product_name}");
                        }

                        $remainingQty -= $deductQty;
                    }
                }
            }

            // Mark order as fulfilled
            $order->update(['is_fulfilled' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as fulfilled. Stock has been updated.',
            ]);
        });
    }
}
