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
use App\Models\Sales\Quote;
use App\Models\Sales\QuoteStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class QuoteController extends Controller
{
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
        $companyId = active_company_id();
        $query = Quote::where('quotes.company_id', $companyId)
            ->with(['customer', 'status']);

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
            ->editColumn('quote_number', function ($quote) {
                return '<span class="font-weight-bold">'.e($quote->quote_number).'</span>';
            })
            ->editColumn('created_at', function ($quote) {
                return $quote->created_at->format('M d, Y');
            })
            ->addColumn('customer_name', function ($quote) {
                return $quote->customer->display_name ?? 'N/A';
            })
            ->editColumn('valid_until', function ($quote) {
                if (!$quote->valid_until) {
                    return '—';
                }
                $isExpired = $quote->valid_until->isPast();
                $color = $isExpired ? 'text-danger' : 'text-success';
                return '<span class="'.$color.'">'.$quote->valid_until->format('M d, Y').'</span>';
            })
            ->editColumn('status', function ($quote) {
                $color = 'secondary';
                $code = $quote->status->code ?? '';
                if ($code === 'ACCEPTED') {
                    $color = 'success';
                } elseif ($code === 'DRAFT') {
                    $color = 'warning';
                } elseif ($code === 'SENT') {
                    $color = 'info';
                } elseif ($code === 'REJECTED' || $code === 'EXPIRED') {
                    $color = 'danger';
                } elseif ($code === 'CONVERTED') {
                    $color = 'primary';
                }

                return '<span class="badge badge-'.$color.'">'.e($quote->status->name ?? '—').'</span>';
            })
            ->editColumn('grand_total', function ($quote) {
                return '<span class="font-weight-bold text-dark">'.number_format((float) $quote->grand_total, 2).'</span>';
            })
            ->addColumn('actions', function ($quote) {
                $edit = company_route('quotes.edit', ['quote' => $quote->uuid]);
                $showUrl = company_route('quotes.show', ['quote' => $quote->id]);

                $compact['showUrl'] = $showUrl;
                $compact['editUrl'] = $edit;
                $compact['deleteUrl'] = null;
                $compact['editSidebar'] = false;

                return view('theme.adminlte.components._table-actions', $compact)->render();
            })
            ->rawColumns(['quote_number', 'status', 'grand_total', 'valid_until', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', 'quotes');

        return view('catvara.quotes.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', 'quotes');

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

        $status = QuoteStatus::where('code', 'DRAFT')->first();
        if (! $status) {
            $status = QuoteStatus::firstOrCreate(
                ['code' => 'DRAFT'],
                ['name' => 'Draft', 'is_active' => true]
            );
        }

        // Currency default (adjust as per settings)
        $defaultCurrencyId = 1;

        // Default validity: 30 days
        $validUntil = Carbon::now()->addDays(30);

        $quote = Quote::create([
            'uuid' => Str::uuid(),
            'company_id' => $company->id,
            'customer_id' => $sellToCustomer->id,
            'status_id' => $status->id,
            'quote_number' => $this->generateQuoteNumber($company),
            'created_by' => auth()->id(),
            'currency_id' => $defaultCurrencyId,
            'valid_until' => $validUntil,
        ]);

        // Addresses
        $quote->addresses()->create([
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

        $quote->addresses()->create([
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
            'redirect_url' => company_route('quotes.edit', ['quote' => $quote->uuid]),
        ]);
    }

    public function edit(Company $company, $id)
    {
        $this->authorize('edit', 'quotes');

        $quote = Quote::where('company_id', $company->id)
            ->whereUuid($id)
            ->with(['items.productVariant.product'])
            ->firstOrFail();

        $sellToCustomer = $quote->customer;

        $billToCustomer = $quote->billingAddress
            ? Customer::where('email', $quote->billingAddress->email)->first()
            : $sellToCustomer;

        $initialState = [
            'items' => $quote->items->map(function ($item) {
                return [
                    'variantId' => (string) $item->productVariant->uuid,
                    'product_id' => (string) optional($item->productVariant)->product->uuid,
                    'qty' => (float) $item->quantity,
                    'unitPrice' => (float) $item->unit_price,
                    'discountPercent' => (float) $item->discount_percent,
                    'attrs' => [],
                ];
            })->values(),

            'payment_term_id' => $quote->payment_term_id ?? $sellToCustomer->payment_term_id,
            'shipping' => (float) $quote->shipping_total,
            'additional' => $quote->additional_total ?? 0,
            'vat_rate' => $quote->tax_rate ?? 0,
            'notes' => $quote->notes,
            'currency' => $quote->currency->code ?? 'AED',
            'status' => $quote->status->code ?? 'DRAFT',
            'valid_until' => $quote->valid_until ? $quote->valid_until->format('Y-m-d') : null,
        ];

        $customerDiscount = $sellToCustomer->percentage_discount ?? 0;

        return view('catvara.quotes.edit', compact('sellToCustomer', 'billToCustomer', 'quote', 'initialState', 'customerDiscount'));
    }

    public function update(Request $request, Company $company, $uuid)
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
                'status' => $quote->status->name ?? 'Sent',
            ]);
        }

        if ($request->action === 'accept') {
            $status = QuoteStatus::where('code', 'ACCEPTED')->first();
            if ($status) {
                $quote->update(['status_id' => $status->id, 'accepted_at' => now()]);
            }
            return response()->json([
                'success' => true,
                'status' => $quote->status->name ?? 'Accepted',
            ]);
        }

        if ($request->action === 'reject') {
            $status = QuoteStatus::where('code', 'REJECTED')->first();
            if ($status) {
                $quote->update(['status_id' => $status->id, 'rejected_at' => now()]);
            }
            return response()->json([
                'success' => true,
                'status' => $quote->status->name ?? 'Rejected',
            ]);
        }

        $data = $request->validate([
            'payment_term_id' => ['nullable', 'integer'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'additional' => ['nullable', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
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

            $shipping = (float) ($data['shipping'] ?? 0);
            $additional = (float) ($data['additional'] ?? 0);
            $shippingTotal = max(0, $shipping + $additional);
            $shippingTaxTotal = max(0, $shippingTotal * ($vatRate / 100));

            $currencyId = $this->resolveCurrencyIdFromCode($data['currency']);
            $termSnapshot = $this->resolvePaymentTermSnapshot($data['payment_term_id'] ?? null);

            $calc = $this->calculateFromItemsPayload(
                $data['items'] ?? [],
                $vatRate
            );

            // Update addresses if sell_to/bill_to provided
            if (! empty($data['sell_to'])) {
                $sellToCustomer = Customer::where('company_id', $company->id)->where('uuid', $data['sell_to'])->first();
                if ($sellToCustomer) {
                    $quote->update(['customer_id' => $sellToCustomer->id]);

                    $quote->shippingAddress()->updateOrCreate(
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
                    $quote->billingAddress()->updateOrCreate(
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

            $quote->update([
                'currency_id' => $currencyId,
                'payment_term_id' => $termSnapshot['payment_term_id'],
                'payment_term_name' => $termSnapshot['payment_term_name'],
                'payment_due_days' => $termSnapshot['payment_due_days'],
                'valid_until' => $data['valid_until'] ?? $quote->valid_until,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $calc['subtotal'],
                'discount_total' => $calc['discount_total'],
                'tax_total' => $calc['tax_total'],
                'shipping_total' => $shippingTotal,
                'shipping_tax_total' => $shippingTaxTotal,
                'grand_total' => $calc['items_grand_total'] + $shippingTotal + $shippingTaxTotal,
            ]);

            // Sync items
            $quote->items()->delete();

            foreach ($calc['items_for_db'] as $row) {
                $quote->items()->create($row);
            }

            DB::commit();

            return response()->json([
                'success' => true,
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
        $quote = Quote::where('company_id', $company->id)
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

        if (!$quote->canConvertToOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'This quote cannot be converted to an order. It may be expired, rejected, or already converted.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Get order status
            $orderStatus = OrderStatus::where('code', 'DRAFT')->first();
            if (!$orderStatus) {
                $orderStatus = OrderStatus::firstOrCreate(
                    ['code' => 'DRAFT'],
                    ['name' => 'Draft', 'is_active' => true]
                );
            }

            // Create order
            $order = Order::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'customer_id' => $quote->customer_id,
                'status_id' => $orderStatus->id,
                'order_number' => $this->generateOrderNumber($company),
                'currency_id' => $quote->currency_id,
                'payment_term_id' => $quote->payment_term_id,
                'payment_term_name' => $quote->payment_term_name,
                'payment_due_days' => $quote->payment_due_days,
                'subtotal' => $quote->subtotal,
                'tax_total' => $quote->tax_total,
                'discount_total' => $quote->discount_total,
                'shipping_total' => $quote->shipping_total,
                'shipping_tax_total' => $quote->shipping_tax_total,
                'grand_total' => $quote->grand_total,
                'notes' => $quote->notes,
                'created_by' => auth()->id(),
            ]);

            // Copy items
            foreach ($quote->items as $quoteItem) {
                $order->items()->create([
                    'product_variant_id' => $quoteItem->product_variant_id,
                    'product_name' => $quoteItem->product_name,
                    'variant_description' => $quoteItem->variant_description,
                    'unit_price' => $quoteItem->unit_price,
                    'quantity' => $quoteItem->quantity,
                    'discount_percent' => $quoteItem->discount_percent,
                    'discount_amount' => $quoteItem->discount_amount,
                    'tax_rate' => $quoteItem->tax_rate,
                    'tax_amount' => $quoteItem->tax_amount,
                    'line_total' => $quoteItem->line_total,
                ]);
            }

            // Copy billing address
            if ($quote->billingAddress) {
                $order->addresses()->create([
                    'type' => 'BILLING',
                    'company_id' => $company->id,
                    'address_line_1' => $quote->billingAddress->address_line_1,
                    'address_line_2' => $quote->billingAddress->address_line_2,
                    'city' => $quote->billingAddress->city,
                    'state_id' => $quote->billingAddress->state_id,
                    'zip_code' => $quote->billingAddress->zip_code,
                    'country_id' => $quote->billingAddress->country_id,
                    'phone' => $quote->billingAddress->phone,
                    'email' => $quote->billingAddress->email,
                ]);
            }

            // Copy shipping address
            if ($quote->shippingAddress) {
                $order->addresses()->create([
                    'type' => 'SHIPPING',
                    'company_id' => $company->id,
                    'address_line_1' => $quote->shippingAddress->address_line_1,
                    'address_line_2' => $quote->shippingAddress->address_line_2,
                    'city' => $quote->shippingAddress->city,
                    'state_id' => $quote->shippingAddress->state_id,
                    'zip_code' => $quote->shippingAddress->zip_code,
                    'country_id' => $quote->shippingAddress->country_id,
                    'phone' => $quote->shippingAddress->phone,
                    'email' => $quote->shippingAddress->email,
                ]);
            }

            // Update quote status to CONVERTED and link to order
            $convertedStatus = QuoteStatus::where('code', 'CONVERTED')->first();
            $quote->update([
                'status_id' => $convertedStatus ? $convertedStatus->id : $quote->status_id,
                'order_id' => $order->id,
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
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function printQuote(Company $company, $uuid)
    {
        $quote = Quote::where('company_id', $company->id)
            ->where('uuid', $uuid)
            ->with(['items.productVariant.product', 'customer', 'billingAddress.country', 'shippingAddress.country', 'company', 'currency'])
            ->firstOrFail();

        return view('catvara.quotes.print', compact('quote'));
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
                'discount_percent' => $discPct,
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

    private function generateQuoteNumber($company)
    {
        $prefix = 'QT-'.Carbon::now()->format('Ymd').'-';
        $lastQuote = Quote::where('company_id', $company->id)
            ->where('quote_number', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastQuote) {
            $lastNum = intval(substr($lastQuote->quote_number, strlen($prefix)));

            return $prefix.str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix.'0001';
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
