<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreQuoteRequest;
use App\Http\Requests\Sales\UpdateQuoteCustomersRequest;
use App\Http\Requests\Sales\UpdateQuoteRequest;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\Sales\Order;
use App\Models\Sales\Quote;
use App\Models\Sales\QuoteStatus;
use App\Services\Sales\SalesCalculationService;
use App\Services\Sales\SalesDocumentService;
use App\Services\Sales\TaxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class QuoteController extends Controller
{
    public function __construct(
        protected SalesCalculationService $calcService,
        protected SalesDocumentService $docService,
        protected TaxService $taxService,
        protected \App\Services\Common\DocumentNumberService $docNumberService
    ) {}

    public function index()
    {
        $this->authorize('view', 'quotes');

        $companyId = active_company_id();
        $statuses = QuoteStatus::all();
        $customers = Customer::where('company_id', $companyId)->orderBy('display_name')->get();

        return view('catvara.quotes.index', compact('statuses', 'customers'));
    }

    public function data(Request $request)
    {
        $this->authorize('view', 'quotes');

        $companyId = active_company_id();
        $query = Quote::where('quotes.company_id', $companyId)
            ->with(['customer', 'status', 'currency']);

        // Filters
        if ($request->filled('status_id')) {
            $query->where('quotes.status_id', $request->status_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('quotes.customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('quotes.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('quotes.created_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->editColumn('quote_number', fn ($quote) => '<span class="font-weight-bold">'.e($quote->quote_number).'</span>')
            ->editColumn('created_at', fn ($quote) => optional($quote->created_at)->format('M d, Y'))
            ->addColumn('customer_name', fn ($quote) => $quote->customer->display_name ?? 'N/A')
            ->editColumn('valid_until', function ($quote) {
                if (! $quote->valid_until) {
                    return '—';
                }
                $isExpired = $quote->valid_until->isPast();
                $color = $isExpired ? 'text-danger' : 'text-success';

                return '<span class="'.$color.'">'.$quote->valid_until->format('M d, Y').'</span>';
            })
            ->editColumn('status', function ($quote) {
                $code = $quote->status->code ?? '';
                $color = 'secondary';
                if ($code === 'ACCEPTED') {
                    $color = 'success';
                } elseif ($code === 'DRAFT') {
                    $color = 'warning';
                } elseif ($code === 'SENT') {
                    $color = 'info';
                } elseif (in_array($code, ['REJECTED', 'EXPIRED'])) {
                    $color = 'danger';
                } elseif ($code === 'CONVERTED') {
                    $color = 'primary';
                }

                return '<span class="badge badge-'.$color.'">'.e($quote->status->name ?? '—').'</span>';
            })
            ->editColumn('grand_total', function ($quote) {
                $amount = number_format((float) $quote->grand_total, 2);
                $cur = $quote->currency->code ?? '';

                return '<span class="font-weight-bold text-dark">'.e($cur).' '.$amount.'</span>';
            })
            ->addColumn('actions', function ($quote) {
                $editUrl = company_route('quotes.edit', ['quote' => $quote->uuid]);
                $showUrl = company_route('quotes.show', ['quote' => $quote->id]);

                $compact['showUrl'] = $showUrl;
                $compact['editUrl'] = $editUrl;
                $compact['deleteUrl'] = null;
                $compact['editSidebar'] = false;

                return view('theme.adminlte.components._table-actions', $compact)->render();
            })
            ->rawColumns(['quote_number', 'status', 'grand_total', 'valid_until', 'actions'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $this->authorize('create', 'quotes');

        $editQuote = null;
        if ($request->filled('edit_quote')) {
            $editQuote = Quote::where('company_id', $request->company->id)
                ->where('uuid', $request->edit_quote)
                ->first();
        }

        return view('catvara.quotes.create', [
            'editQuote' => $editQuote,
        ]);
    }

    public function store(StoreQuoteRequest $request)
    {
        $this->authorize('create', 'quotes');

        $request->validated();

        $company = $request->company;

        $billToCustomer = Customer::where('company_id', $company->id)
            ->where('uuid', $request->bill_to)
            ->firstOrFail();

        $shipToCustomer = $request->filled('ship_to')
            ? Customer::where('company_id', $company->id)->where('uuid', $request->ship_to)->firstOrFail()
            : $billToCustomer;

        $status = QuoteStatus::firstOrCreate(
            ['code' => 'DRAFT'],
            ['name' => 'Draft', 'is_active' => true]
        );

        // Currency default: company base currency -> first currency
        $defaultCurrencyId = $company->base_currency_id
            ?? (int) (Currency::query()->value('id') ?? 1);

        // Default validity: 30 days
        $validUntil = Carbon::now()->addDays(30);

        return DB::transaction(function () use ($request, $company, $billToCustomer, $shipToCustomer, $status, $defaultCurrencyId, $validUntil) {
            $quote = Quote::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,

                'customer_id' => $billToCustomer->id,
                'shipping_customer_id' => $shipToCustomer->id,

                'status_id' => $status->id,
                'quote_number' => $this->docNumberService->generate(
                    companyId: $company->id,
                    documentType: 'QUOTE',
                    channel: 'SALES',
                    year: now()->year
                ),
                'created_by' => Auth::id(),

                'currency_id' => $defaultCurrencyId,
                'base_currency_id' => $company->base_currency_id ?? null,
                'fx_rate' => 1,

                'valid_until' => $validUntil,
            ]);

            // Address snapshots
            $this->docService->syncAddressSnapshots($quote, $billToCustomer, $shipToCustomer);

            $redirectUrl = company_route('quotes.edit', ['quote' => $quote->uuid]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quote created successfully.',
                    'quote' => $quote,
                    'redirect_url' => $redirectUrl,
                ]);
            }

            return redirect()->to($redirectUrl);
        });
    }

    /**
     * Update customers (bill_to / ship_to) for an existing quote.
     */
    public function updateCustomers(UpdateQuoteCustomersRequest $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'quotes');

        $request->validated();

        $quote = Quote::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $billToCustomer = Customer::where('company_id', $company->id)
            ->where('uuid', $request->bill_to)
            ->firstOrFail();

        $shipToCustomer = $request->filled('ship_to')
            ? Customer::where('company_id', $company->id)->where('uuid', $request->ship_to)->firstOrFail()
            : $billToCustomer;

        return DB::transaction(function () use ($quote, $billToCustomer, $shipToCustomer, $request) {
            $quote->update([
                'customer_id' => $billToCustomer->id,
                'shipping_customer_id' => $shipToCustomer->id,
            ]);

            $this->docService->syncAddressSnapshots($quote, $billToCustomer, $shipToCustomer);

            if ($request->ajax()) {
                $quote->load(['addresses', 'customer', 'shippingCustomer']);
                $billToCustomerModel = $quote->customer;
                $billToAddress = $quote->addresses->where('type', 'BILLING')->first();
                $shipToAddress = $quote->addresses->where('type', 'SHIPPING')->first();

                $customerDiscount = $billToCustomerModel->percentage_discount ?? 0;
                $defaultPaymentTermId = $billToCustomerModel->payment_term_id ?? null;

                return response()->json([
                    'success' => true,
                    'message' => 'Quote customers updated successfully.',
                    'quote' => $quote,
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

            return redirect()->to(company_route('quotes.edit', ['quote' => $quote->uuid]));
        });
    }

    public function customerSwitcher(Request $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'quotes');

        $quote = Quote::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $type = $request->input('type', 'BILLING');
        $search = $request->input('q');
        $customerType = $request->input('customer_type');

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

        return view('catvara.quotes.partials._customer_switcher', compact('customers', 'quote', 'type'))->render();
    }

    public function edit(Company $company, $uuid)
    {
        $this->authorize('edit', 'quotes');

        $quote = Quote::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items.productVariant.product.category', 'customer', 'addresses', 'shippingCustomer', 'currency', 'status'])
            ->firstOrFail();

        $billToCustomer = $quote->addresses->where('type', 'BILLING')->first();
        $shipToCustomer = $quote->addresses->where('type', 'SHIPPING')->first();

        $initialState = [
            'items' => $quote->items->map(function ($item) {
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

            'payment_term_id' => $quote->payment_term_id ?? $quote->customer?->payment_term_id,
            'shipping' => (float) $quote->shipping_total,
            'additional' => 0,
            'tax_group_id' => $quote->tax_group_id,
            'global_discount_percent' => (float) $quote->global_discount_percent,
            'global_discount_amount' => (float) $quote->global_discount_amount,
            'notes' => $quote->notes,
            'currency' => $quote->currency->code ?? ($company->baseCurrency->code ?? 'AED'),
            'status' => $quote->status->code ?? 'DRAFT',
            'valid_until' => $quote->valid_until ? $quote->valid_until->format('Y-m-d') : null,
        ];

        $customerDiscount = $quote->customer?->percentage_discount ?? 0;

        $taxGroups = \App\Models\Tax\TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        $baseCurrency = $company->baseCurrency;
        $exchangeRates = $company->exchangeRates()->with('targetCurrency')->get();
        $enabledCurrencies = collect([$baseCurrency])->merge($exchangeRates->pluck('targetCurrency'))->filter()->unique('id');

        return view('catvara.quotes.edit', compact(
            'billToCustomer',
            'shipToCustomer',
            'quote',
            'initialState',
            'customerDiscount',
            'taxGroups',
            'enabledCurrencies'
        ));
    }

    public function update(UpdateQuoteRequest $request, Company $company, $uuid)
    {
        $this->authorize('edit', 'quotes');

        $quote = Quote::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Handle Status Changes
        if ($request->action === 'send') {
            $status = QuoteStatus::where('code', 'SENT')->first();
            if ($status) {
                $quote->update(['status_id' => $status->id, 'sent_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'status' => $quote->fresh()->status->name ?? 'Sent',
            ]);
        }

        if ($request->action === 'accept') {
            $status = QuoteStatus::where('code', 'ACCEPTED')->first();
            if ($status) {
                $quote->update(['status_id' => $status->id, 'accepted_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'status' => $quote->fresh()->status->name ?? 'Accepted',
            ]);
        }

        if ($request->action === 'reject') {
            $status = QuoteStatus::where('code', 'REJECTED')->first();
            if ($status) {
                $quote->update(['status_id' => $status->id, 'rejected_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'status' => $quote->fresh()->status->name ?? 'Rejected',
            ]);
        }

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
                'customer_id' => $quote->customer_id,
            ]);

            // Update quote header
            $quote->update([
                'currency_id' => $currencyId,
                'tax_group_id' => $taxGroupId,

                'payment_term_id' => $termSnapshot['payment_term_id'],
                'payment_term_name' => $termSnapshot['payment_term_name'],
                'payment_due_days' => $termSnapshot['payment_due_days'],

                'valid_until' => $request->valid_until ?? $quote->valid_until,

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
            $quote->items()->delete();
            foreach ($calc['items_for_db'] as $row) {
                $quote->items()->create($row);
            }

            $quote->load(['items.productVariant.product', 'customer', 'shippingCustomer', 'currency', 'status', 'addresses']);

            DB::commit();

            return response()->json([
                'success' => true,
                'quote' => $quote,
                'fx_rate' => $quote->fx_rate,
                'status' => $quote->status->name ?? 'Draft',
                'totals' => [
                    'subtotal' => (float) $quote->subtotal,
                    'discount_total' => (float) $quote->discount_total,
                    'tax_total' => (float) $quote->tax_total,
                    'shipping_total' => (float) $quote->shipping_total,
                    'shipping_tax_total' => (float) $quote->shipping_tax_total,
                    'grand_total' => (float) $quote->grand_total,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function show(Company $company, $id)
    {
        $this->authorize('view', 'quotes');

        $quote = Quote::where('company_id', $company->id)
            ->where('id', $id)
            ->with([
                'items.productVariant.product',
                'customer',
                'shippingCustomer',
                'billingAddress.country',
                'shippingAddress.country',
                'company',
                'currency',
                'status',
                'creator',
                'paymentTerm',
                'order',
            ])
            ->firstOrFail();

        return view('catvara.quotes.show', compact('quote'));
    }

    /**
     * Generate Order from Quote
     */
    public function generateOrder(Request $request, Company $company, $id)
    {
        $this->authorize('create', 'orders');

        $quote = Quote::where('company_id', $company->id)
            ->where('id', $id)
            ->with(['items', 'billingAddress', 'shippingAddress'])
            ->firstOrFail();

        if (! $quote->canConvertToOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'This quote cannot be converted to an order. It may be expired, rejected, or already converted.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Use OrderService for centralized conversion logic
            $orderService = app(\App\Services\Sales\OrderService::class);
            $order = $orderService->createFromQuote($quote);

            // Update quote status to CONVERTED and link to order
            $convertedStatus = QuoteStatus::where('code', 'CONVERTED')->first();
            $quote->update([
                'status_id' => $convertedStatus ? $convertedStatus->id : $quote->status_id,
                'order_id' => $order->id,
                'accepted_at' => $quote->accepted_at ?? now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully from quote.',
                'order_id' => $order->id,
                'order_uuid' => $order->uuid,
                'redirect_url' => company_route('sales-orders.edit', ['sales_order' => $order->uuid]),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: '.$e->getMessage(),
            ], 422);
        }
    }

    public function printQuote(Company $company, $uuid)
    {
        $this->authorize('view', 'quotes');

        $quote = Quote::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with([
                'items.productVariant.product',
                'customer',
                'shippingCustomer',
                'billingAddress.country',
                'shippingAddress.country',
                'company.banks',
                'company.detail',
                'currency',
                'paymentTerm',
            ])
            ->firstOrFail();

        return view('catvara.quotes.print', compact('quote'));
    }

    /* ===================== HELPERS ===================== */
    // Helper methods moved to SalesDocumentService

}
