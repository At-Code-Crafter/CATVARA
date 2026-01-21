<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerStoreRequest;
use App\Http\Requests\Admin\CustomerUpdateRequest;
use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Company $company)
    {
        $this->authorize('view', 'customers');
        if ($request->ajax()) {

            $query = Customer::query()
                ->select(
                    'customers.id',
                    'customers.uuid',
                    'customers.display_name',
                    'customers.type',
                    'customers.email',
                    'customers.phone',
                    'customers.legal_name',
                    'customers.is_active',
                    'customers.percentage_discount',
                    'customers.created_at'
                )
                ->where('customers.company_id', $company->id);

            // Filters
            if ($request->filled('type')) {
                $query->where('customers.type', $request->type);
            }

            if ($request->filled('is_active')) {
                $query->where('customers.is_active', $request->is_active);
            }

            if ($request->filled('payment_term_id')) {
                $query->where('customers.payment_term_id', $request->payment_term_id);
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('customers.created_at', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59'
                ]);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('email', function ($row) {
                    return $row->email
                        ? '<a href="mailto:' . e($row->email) . '">' . e($row->email) . '</a>'
                        : '<span class="text-muted">—</span>';
                })

                ->editColumn('phone', function ($row) {
                    return $row->phone
                        ? '<a href="tel:' . e($row->phone) . '">' . e($row->phone) . '</a>'
                        : '<span class="text-muted">—</span>';
                })

                ->addColumn('status_badge', function ($row) {
                    if ($row->is_active) {
                        return '<span class="badge badge-success">Active</span>';
                    }

                    return '<span class="badge badge-danger">Inactive</span>';
                })

                ->editColumn('percentage_discount', function ($row) {
                    if ($row->percentage_discount > 0) {
                        return '<span class="text-success font-weight-bold">' . (float) $row->percentage_discount . '%</span>';
                    }
                    return '<span class="text-muted">-</span>';
                })

                ->editColumn('created_at', function ($row) {
                    return $row->created_at
                        ? \Carbon\Carbon::parse($row->created_at)->format('d-M-Y h:i A')
                        : '<span class="text-muted">—</span>';
                })

                ->addColumn('action', function ($row) use ($company) {
                    $compact['showUrl'] = route('customers.show', [$company->uuid, $row->id]);
                    $compact['editUrl'] = route('customers.edit', [$company->uuid, $row->id]);
                    $compact['deleteUrl'] = null;
                    $compact['editSidebar'] = false;

                    return view('theme.adminlte.components._table-actions', $compact)->render();
                })

                ->rawColumns([
                    'email',
                    'phone',
                    'status_badge',
                    'percentage_discount',
                    'created_at',
                    'action',
                ])
                ->make(true);
        }

        return view('catvara.customers.index', compact('company'));
    }

    /**
     * Stats API for dashboard cards
     */
    public function stats(Request $request, Company $company)
    {
        $baseQuery = Customer::where('company_id', $company->id);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $baseQuery->whereBetween('created_at', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        }

        $all = (clone $baseQuery)->count();
        $active = (clone $baseQuery)->where('is_active', true)->count();
        $inactive = (clone $baseQuery)->where('is_active', false)->count();
        $companies = (clone $baseQuery)->where('type', 'COMPANY')->count();
        $individuals = (clone $baseQuery)->where('type', 'INDIVIDUAL')->count();

        return response()->json([
            'all_customers' => $all,
            'active_customers' => $active,
            'inactive_customers' => $inactive,
            'company_customers' => $companies,
            'individual_customers' => $individuals,
        ]);
    }

    /**
     * Search customers for Select2 AJAX
     */
    public function search(Request $request, Company $company)
    {
        $search = $request->input('q', '');
        $perPage = 15;

        $query = Customer::where('company_id', $company->id)
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'LIKE', "%{$search}%")
                    ->orWhere('legal_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('display_name')
            ->paginate($perPage, ['id', 'display_name', 'email', 'phone']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Company $company)
    {
        $this->authorize('create', 'customers');

        $countries = Country::active()->ordered()->get();
        $paymentTerms = \App\Models\Accounting\PaymentTerm::where('is_active', true)->get();

        return view('catvara.customers.create', compact('company', 'countries', 'paymentTerms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request, Company $company)
    {
        $this->authorize('create', 'customers');

        $data = $request->validated();

        DB::beginTransaction();

        try {
            $customer = Customer::create([
                'company_id' => $company->id,
                'type' => $data['type'],
                'display_name' => $data['display_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'legal_name' => $data['legal_name'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'percentage_discount' => $data['percentage_discount'] ?? 0,
            ]);

            Address::create([
                'company_id' => $company->id,
                'addressable_id' => $customer->id,
                'addressable_type' => Customer::class,
                'address_line_1' => $data['address_line_1'] ?? null,
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'zip_code' => $data['zip_code'] ?? null,
                'country_id' => $data['country_id'] ?? null,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Customer Created Successfully',
                    'redirect' => route('customers.index', $company->uuid),
                ]);
            }

            return redirect()
                ->route('customers.index', $company->uuid)
                ->with('success', 'Customer Created Successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company, string $id)
    {
        $this->authorize('view', 'customers');

        $customer = Customer::where('company_id', $company->id)
            ->with(['address', 'orders.status', 'orders.currency'])
            ->findOrFail($id);

        // Stats Calculation
        $stats = [
            'orders_count' => $customer->orders()->count(),
            'orders_draft' => $customer->orders()->whereHas('status', fn($q) => $q->where('code', 'DRAFT'))->count(),
            'orders_completed' => $customer->orders()->whereHas('status', fn($q) => $q->where('code', 'FULFILLED'))->count(),
            'invoices_paid' => \App\Models\Accounting\Invoice::where('customer_id', $customer->id)->whereHas('status', fn($q) => $q->where('code', 'PAID'))->count(),
            'invoices_unpaid' => \App\Models\Accounting\Invoice::where('customer_id', $customer->id)->whereHas('status', fn($q) => $q->whereIn('code', ['ISSUED', 'PARTIALLY_PAID', 'OVERDUE']))->count(),
            'total_spent' => $customer->orders()->whereHas('status', fn($q) => $q->where('code', 'FULFILLED'))->sum('grand_total'),
            'total_overdue' => \App\Models\Accounting\Invoice::where('customer_id', $customer->id)->whereHas('status', fn($q) => $q->where('code', 'OVERDUE'))->sum('grand_total'), // Or use balance due if available, assuming grand_total for now
        ];

        return view('catvara.customers.show', compact('company', 'customer', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company, string $id)
    {
        $this->authorize('edit', 'customers');

        $customer = Customer::where('company_id', $company->id)->with('address')->findOrFail($id);
        $countries = Country::active()->ordered()->get();
        $states = $customer->address?->country_id ? State::where('country_id', $customer->address->country_id)->active()->ordered()->get() : collect();
        $paymentTerms = \App\Models\Accounting\PaymentTerm::where('is_active', true)->get();

        return view('catvara.customers.edit', compact('company', 'customer', 'countries', 'states', 'paymentTerms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerUpdateRequest $request, Company $company, string $id)
    {
        $this->authorize('edit', 'customers');

        $customer = Customer::where('company_id', $company->id)->findOrFail($id);
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $customer->update([
                'type' => $data['type'],
                'display_name' => $data['display_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'legal_name' => $data['legal_name'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'percentage_discount' => $data['percentage_discount'] ?? 0,
            ]);

            Address::updateOrCreate([
                'company_id' => $request->company->id,
                'addressable_id' => $customer->id,
                'addressable_type' => Customer::class,
            ], [
                'address_line_1' => $data['address_line_1'],
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'] ?? null,
                'state_id' => $data['state_id'],
                'country_id' => $data['country_id'],
                'zip_code' => $data['zip_code'],
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Customer Updated Successfully',
                    'redirect' => route('customers.index', $company->uuid),
                ]);
            }

            return redirect()
                ->route('customers.index', $company->uuid)
                ->with('success', 'Customer Updated Successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }
    }


    public function loadCustomers(Request $request, Company $company)
    {
        // Simple list for the Sales Order selection UI
        $customers = Customer::where('company_id', $company->id)
            ->where('is_active', true)
            ->select('id', 'uuid', 'display_name', 'email', 'phone', 'type', 'legal_name')
            ->orderBy('display_name')
            ->get();

        return response()->json($customers);
    }

    /**
     * Export customers to CSV.
     */
    public function export(Request $request, Company $company)
    {
        $this->authorize('view', 'customers');

        $headers = [
            'Customer ID',
            'Customer Code',
            'Name',
            'Legal Name',
            'Email',
            'Phone',
            'Tax Number',
            'Address Line 1',
            'Address Line 2',
            'City/Town',
            'State',
            'Country',
            'Postal Code',
            'Discount Percentage',
            'Payment Terms',
        ];

        $customers = Customer::where('company_id', $company->id)
            ->with(['address.state', 'address.country', 'paymentTerm'])
            ->get();

        $csvData = [];
        $csvData[] = $headers;

        foreach ($customers as $customer) {
            // Format phone as Excel text formula to prevent scientific notation
            $phone = $customer->phone ?? '';
            if (!empty($phone)) {
                $phone = '="' . $phone . '"';
            }

            $csvData[] = [
                $customer->id,
                $customer->customer_code,
                $customer->display_name,
                $customer->legal_name ?? '',
                $customer->email ?? '',
                $phone,
                $customer->tax_number ?? '',
                $customer->address->address_line_1 ?? '',
                $customer->address->address_line_2 ?? '',
                $customer->address->city ?? '',
                $customer->address->state->name ?? '',
                $customer->address->country->name ?? '',
                $customer->address->zip_code ?? '',
                (float) $customer->percentage_discount . '%',
                $customer->paymentTerm->name ?? 'N/A',
            ];
        }

        $filename = 'customers_export_' . date('Y-m-d_His') . '.csv';

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
