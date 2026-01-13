<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentApplication;
use App\Models\Accounting\PaymentMethod;
use App\Models\Accounting\PaymentStatus;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Pricing\Currency;
use App\Models\Sales\Order;
use App\Services\Accounting\PaymentService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of payments
     */
    public function index(Company $company)
    {
        $this->authorize('view', 'payments');

        $companyId = $company->id;

        $statuses = PaymentStatus::active()->get();
        $methods = PaymentMethod::forCompany($companyId)->active()->get();
        $customers = Customer::where('company_id', $companyId)->orderBy('display_name')->get();

        return view('theme.adminlte.accounting.payments.index', compact('statuses', 'methods', 'customers'));
    }

    /**
     * DataTables data source
     */
    public function data(Company $company, Request $request)
    {
        $companyId = $company->id;

        $query = Payment::where('company_id', $companyId)
            ->with(['customer', 'method', 'status', 'currency', 'creator']);

        // Filters
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->editColumn('payment_number', function ($payment) {
                return '<span class="font-weight-bold">' . e($payment->payment_number) . '</span>';
            })
            ->editColumn('paid_at', function ($payment) {
                return $payment->paid_at?->format('M d, Y H:i') ?? '—';
            })
            ->addColumn('customer_name', function ($payment) {
                return $payment->customer?->display_name ?? '<span class="text-muted">Walk-in</span>';
            })
            ->addColumn('method_name', function ($payment) {
                return $payment->method?->name ?? '—';
            })
            ->editColumn('status', function ($payment) {
                $colors = [
                    'PENDING' => 'warning',
                    'CONFIRMED' => 'success',
                    'FAILED' => 'danger',
                    'CANCELLED' => 'secondary',
                    'REFUNDED' => 'info',
                ];
                $color = $colors[$payment->status?->code] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . e($payment->status?->name ?? '—') . '</span>';
            })
            ->editColumn('direction', function ($payment) {
                $icon = $payment->direction === 'IN' ? 'arrow-down text-success' : 'arrow-up text-danger';
                $label = $payment->direction === 'IN' ? 'Received' : 'Refund';
                return '<i class="fas fa-' . $icon . '"></i> ' . $label;
            })
            ->editColumn('amount', function ($payment) {
                $symbol = $payment->currency?->symbol ?? '';
                return '<span class="font-weight-bold">' . $symbol . number_format((float) $payment->amount, 2) . '</span>';
            })
            ->addColumn('unallocated', function ($payment) {
                $symbol = $payment->currency?->symbol ?? '';
                $unallocated = (float) $payment->unallocated_amount;
                $class = $unallocated > 0 ? 'text-warning' : 'text-success';
                return '<span class="' . $class . '">' . $symbol . number_format($unallocated, 2) . '</span>';
            })
            ->addColumn('actions', function ($payment) {
                $showUrl = company_route('accounting.payments.show', ['payment' => $payment->id]);
                $editUrl = $payment->canBeEdited()
                    ? company_route('accounting.payments.edit', ['payment' => $payment->id])
                    : null;

                return view('theme.adminlte.components._table-actions', [
                    'showUrl' => $showUrl,
                    'editUrl' => $editUrl,
                    'deleteUrl' => null,
                    'editSidebar' => false,
                ])->render();
            })
            ->rawColumns(['payment_number', 'customer_name', 'status', 'direction', 'amount', 'unallocated', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Company $company, Request $request)
    {
        $this->authorize('create', 'payments');

        $companyId = $company->id;

        $methods = PaymentMethod::forCompany($companyId)->active()->get();
        $currencies = Currency::where('is_active', true)->get();
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('display_name')->get();

        // If creating from order context
        $order = null;
        if ($request->filled('order_id')) {
            $order = Order::where('company_id', $companyId)->findOrFail($request->order_id);
        }

        return view('theme.adminlte.accounting.payments.create', compact('methods', 'currencies', 'customers', 'order'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Company $company, Request $request)
    {
        $this->authorize('create', 'payments');

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
            'customer_id' => 'nullable|exists:customers,id',
            'source' => 'required|in:WEB,POS,MANUAL,API',
            'direction' => 'required|in:IN,OUT',
            'paid_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exchange_rate' => 'nullable|numeric|min:0.00000001',
            // Application fields (optional)
            'apply_to_type' => 'nullable|in:order,invoice',
            'apply_to_id' => 'nullable|integer',
            'apply_amount' => 'nullable|numeric|min:0.01',
            // Attachments
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:5120',
        ]);

        $companyId = $company->id;

        try {
            $paymentData = [
                'company_id' => $companyId,
                'customer_id' => $request->customer_id,
                'payment_method_id' => $request->payment_method_id,
                'currency_id' => $request->currency_id,
                'amount' => $request->amount,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'source' => $request->source,
                'direction' => $request->direction,
                'paid_at' => $request->paid_at,
                'reference' => $request->reference,
                'description' => $request->description,
            ];

            // Create payment and optionally apply
            if ($request->filled('apply_to_type') && $request->filled('apply_to_id')) {
                $applyType = $request->apply_to_type === 'order' ? Order::class : \App\Models\Accounting\Invoice::class;

                $result = $this->paymentService->createAndApply($paymentData, [
                    'paymentable_type' => $applyType,
                    'paymentable_id' => $request->apply_to_id,
                    'amount' => $request->apply_amount ?? $request->amount,
                ]);

                $payment = $result['payment'];
            } else {
                $payment = $this->paymentService->create($paymentData);
            }

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('payments/' . $payment->id, 'public');

                    \App\Models\Attachment::create([
                        'company_id' => $companyId,
                        'attachable_type' => Payment::class,
                        'attachable_id' => $payment->id,
                        'disk' => 'public',
                        'path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            return redirect()
                ->route('accounting.payments.show', ['company' => active_company()->uuid, 'payment' => $payment->id])
                ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Company $company, Payment $payment)
    {
        $this->authorize('view', 'payments');

        $this->ensureCompanyAccess($payment);

        $payment->load(['customer', 'method', 'status', 'currency', 'creator', 'receiver', 'confirmer', 'applications.paymentable', 'attachments']);

        return view('theme.adminlte.accounting.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the payment
     */
    public function edit(Company $company, Payment $payment)
    {
        $this->authorize('edit', 'payments');

        $this->ensureCompanyAccess($payment);

        if (!$payment->canBeEdited()) {
            return back()->with('error', 'This payment cannot be edited.');
        }

        $companyId = $company->id;
        $paymentMethods = PaymentMethod::forCompany($companyId)->active()->get();
        $currencies = Currency::where('is_active', true)->get();
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('display_name')->get();

        return view('theme.adminlte.accounting.payments.edit', compact('payment', 'paymentMethods', 'currencies', 'customers'));
    }

    /**
     * Update the specified payment
     */
    public function update(Company $company, Request $request, Payment $payment)
    {
        $this->authorize('edit', 'payments');

        $this->ensureCompanyAccess($payment);

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
            'customer_id' => 'nullable|exists:customers,id',
            'paid_at' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exchange_rate' => 'nullable|numeric|min:0.00000001',
            // Attachments
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:5120',
        ]);

        $companyId = $company->id;

        try {
            $this->paymentService->update($payment, [
                'customer_id' => $request->customer_id,
                'payment_method_id' => $request->payment_method_id,
                'currency_id' => $request->currency_id,
                'amount' => $request->amount,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'paid_at' => $request->paid_at,
                'reference' => $request->reference,
                'description' => $request->description,
            ]);

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('payments/' . $payment->id, 'public');

                    \App\Models\Attachment::create([
                        'company_id' => $companyId,
                        'attachable_type' => Payment::class,
                        'attachable_id' => $payment->id,
                        'disk' => 'public',
                        'path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            return redirect()
                ->route('accounting.payments.show', ['company' => active_company()->uuid, 'payment' => $payment->id])
                ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Confirm a pending payment
     */
    public function confirm(Company $company, Payment $payment)
    {
        $this->authorize('edit', 'payments');

        $this->ensureCompanyAccess($payment);

        try {
            $this->paymentService->confirm($payment);
            return back()->with('success', 'Payment confirmed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a payment
     */
    public function cancel(Company $company, Payment $payment)
    {
        $this->authorize('delete', 'payments');

        $this->ensureCompanyAccess($payment);

        try {
            $this->paymentService->cancel($payment);
            return back()->with('success', 'Payment cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Apply payment to a document
     */
    public function apply(Company $company, Request $request, Payment $payment)
    {
        $this->authorize('create', 'allocations');

        $this->ensureCompanyAccess($payment);

        $request->validate([
            'paymentable_type' => 'required|in:order,invoice',
            'paymentable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $type = $request->paymentable_type === 'order' ? Order::class : \App\Models\Accounting\Invoice::class;

            $this->paymentService->apply($payment, [
                'paymentable_type' => $type,
                'paymentable_id' => $request->paymentable_id,
                'amount' => $request->amount,
                'notes' => $request->notes,
            ]);

            return back()->with('success', 'Payment applied successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a payment application
     */
    public function removeApplication(Company $company, PaymentApplication $application)
    {
        $this->authorize('delete', 'allocations');

        if ($application->company_id !== active_company_id()) {
            abort(403);
        }

        try {
            $this->paymentService->removeApplication($application);
            return back()->with('success', 'Payment application removed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get payments summary stats
     */
    public function stats(Company $company, Request $request)
    {
        $companyId = $company->id;

        $summary = $this->paymentService->getSummary(
            $companyId,
            $request->date_from,
            $request->date_to
        );

        return response()->json($summary);
    }

    /**
     * Get unallocated payments for a customer
     */
    public function unallocated(Company $company, Request $request)
    {
        $companyId = $company->id;

        $query = Payment::forCompany($companyId)
            ->confirmed()
            ->unallocated()
            ->with(['method', 'currency']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $payments = $query->orderBy('paid_at', 'desc')->get();

        return response()->json($payments);
    }

    /**
     * Delete an attachment from a payment
     */
    public function deleteAttachment(Company $company, Payment $payment, Request $request)
    {
        $this->authorize('edit', 'payments');

        $this->ensureCompanyAccess($payment);

        if (!$payment->canBeEdited()) {
            return response()->json(['error' => 'Payment cannot be edited'], 403);
        }

        $request->validate([
            'attachment_id' => 'required|integer',
        ]);

        $attachment = \App\Models\Attachment::where('id', $request->attachment_id)
            ->where('attachable_type', Payment::class)
            ->where('attachable_id', $payment->id)
            ->first();

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        // Delete file from storage
        \Illuminate\Support\Facades\Storage::disk($attachment->disk)->delete($attachment->path);

        // Delete record
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get customer's pending orders/invoices for payment allocation
     */
    public function customerDocuments(Company $company, Request $request)
    {
        $companyId = $company->id;
        $customerId = $request->customer_id;
        $type = $request->type ?? 'order';

        if (!$customerId) {
            return response()->json([]);
        }

        if ($type === 'order') {
            // Get orders that are not fully paid
            $orders = Order::where('company_id', $companyId)
                ->where('customer_id', $customerId)
                ->whereHas('status', function ($query) {
                    // Only get orders with active (non-final) statuses
                    $query->where('is_final', false)->where('is_active', true);
                })
                ->with(['paymentApplications', 'status'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($order) {
                    $paidAmount = $order->paymentApplications->sum('amount');
                    $balance = $order->grand_total - $paidAmount;

                    return [
                        'id' => $order->id,
                        'number' => $order->order_number,
                        'date' => $order->created_at->format('M d, Y'),
                        'total' => (float) $order->grand_total,
                        'paid' => (float) $paidAmount,
                        'balance' => (float) $balance,
                        'status' => $order->status?->name ?? 'Unknown',
                        'text' => $order->order_number . ' - Balance: ' . number_format($balance, 2),
                    ];
                })
                ->filter(function ($order) {
                    return $order['balance'] > 0; // Only show orders with balance due
                })
                ->values();

            return response()->json($orders);
        }

        // For invoices (if implemented)
        return response()->json([]);
    }

    /**
     * Ensure payment belongs to active company
     */
    protected function ensureCompanyAccess(Payment $payment): void
    {
        if ($payment->company_id !== active_company_id()) {
            abort(403, 'Access denied.');
        }
    }
}
