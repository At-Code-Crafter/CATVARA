<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Customer\CustomerExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerStoreRequest;
use App\Http\Requests\Admin\CustomerUpdateRequest;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Company\Company;
use App\Models\Tax\TaxGroup;
use App\Repositories\Customer\CustomerRepository;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    protected $customerService;

    protected $customerRepository;

    public function __construct(CustomerService $customerService, CustomerRepository $customerRepository)
    {
        $this->customerService = $customerService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Company $company)
    {
        $this->authorize('view', 'customers');
        if ($request->ajax()) {

            $query = $this->customerRepository->query($company->id);

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
                    $request->date_from.' 00:00:00',
                    $request->date_to.' 23:59:59',
                ]);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('email', function ($row) {
                    return $row->email
                        ? '<a href="mailto:'.e($row->email).'">'.e($row->email).'</a>'
                        : '<span class="text-muted">—</span>';
                })

                ->editColumn('phone', function ($row) {
                    return $row->phone
                        ? '<a href="tel:'.e($row->phone).'">'.e($row->phone).'</a>'
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
                        return '<span class="text-success font-weight-bold">'.(float) $row->percentage_discount.'%</span>';
                    }

                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('tax_profile', function ($row) {
                    if ($row->is_tax_exempt) {
                        return '<span class="badge badge-warning">Tax Exempt</span>';
                    }

                    return $row->tax_group_name
                        ? '<span class="badge badge-info">'.e($row->tax_group_name).'</span>'
                        : '<span class="text-muted">—</span>';
                })

                ->editColumn('created_at', function ($row) {
                    return $row->created_at
                        ? \Carbon\Carbon::parse($row->created_at)->format('d-M-Y h:i A')
                        : '<span class="text-muted">—</span>';
                })

                ->addColumn('action', function ($row) use ($company) {
                    // $compact['showUrl'] = route('customers.show', [$company->uuid, $row->id]);
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
                    'tax_profile',
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
        $stats = $this->customerService->getDashboardStats(
            $company->id,
            $request->date_from,
            $request->date_to
        );

        return response()->json($stats);
    }

    /**
     * Search customers for Select2 AJAX
     */
    public function search(Request $request, Company $company)
    {
        return $this->customerRepository->search(
            $company->id,
            $request->input('q', ''),
            15
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Company $company)
    {
        $this->authorize('create', 'customers');

        $countries = Country::active()->ordered()->get();
        $paymentTerms = \App\Models\Accounting\PaymentTerm::where('is_active', true)->get();
        $taxGroups = TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('catvara.customers.create', compact('company', 'countries', 'paymentTerms', 'taxGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request, Company $company)
    {
        $this->authorize('create', 'customers');

        try {
            $data = $request->validated();
            $data['company_id'] = $company->id;

            $this->customerService->createCustomer($data);

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

        $customer = $this->customerRepository->findById((int) $id, $company->id, ['address', 'orders.status', 'orders.currency']);

        $stats = $this->customerService->getCustomerStats($customer);

        return view('catvara.customers.show', compact('company', 'customer', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company, string $id)
    {
        $this->authorize('edit', 'customers');

        $customer = $this->customerRepository->findById((int) $id, $company->id, ['address']);
        $countries = Country::active()->ordered()->get();
        $states = $customer->address?->country_id ? State::where('country_id', $customer->address->country_id)->active()->ordered()->get() : collect();
        $paymentTerms = \App\Models\Accounting\PaymentTerm::where('is_active', true)->get();
        $taxGroups = TaxGroup::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('catvara.customers.edit', compact('company', 'customer', 'countries', 'states', 'paymentTerms', 'taxGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerUpdateRequest $request, Company $company, string $id)
    {
        $this->authorize('edit', 'customers');

        try {
            $customer = $this->customerRepository->findById((int) $id, $company->id);
            $this->customerService->updateCustomer($customer, $request->validated());

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
            \Illuminate\Support\Facades\Log::error('CUSTOMER_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'customer_id' => $id,
            ]);

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
        $customers = $this->customerRepository->getAllForCompany($company->id);

        return response()->json($customers);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company, string $id)
    {
        $this->authorize('delete', 'customers');

        try {
            $customer = $this->customerRepository->findById((int) $id, $company->id);
            $this->customerRepository->delete($customer);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Customer Deleted Successfully',
                ]);
            }

            return redirect()
                ->route('customers.index', $company->uuid)
                ->with('success', 'Customer Deleted Successfully');

        } catch (\Throwable $e) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }

            throw $e;
        }
    }

    /**
     * Export customers to Excel.
     */
    public function export(Request $request, Company $company)
    {
        $this->authorize('view', 'customers');

        $filename = 'customers_export_'.date('Y-m-d_His').'.xlsx';

        return Excel::download(new CustomerExport($company->id), $filename);
    }
}
