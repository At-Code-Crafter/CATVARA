<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Company\CompanyBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CompanyBankController extends Controller
{
    /**
     * Display a listing of company banks.
     */
    public function index(Request $request)
    {
        $this->authorize('view', 'company-banks');

        if ($request->ajax()) {
            $companyId = active_company_id();
            $query = CompanyBank::where('company_id', $companyId)->with('currency');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('bank_info', function ($row) {
                    return '
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">' . e($row->bank_name) . '</span>
                            <span class="text-xs text-slate-400">' . e($row->branch ?? 'N/A') . '</span>
                        </div>
                    ';
                })
                ->addColumn('account_info', function ($row) {
                    return '
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">' . e($row->account_name) . '</span>
                            <span class="text-xs text-slate-400 font-mono">' . e($row->account_number) . '</span>
                        </div>
                    ';
                })
                ->addColumn('currency_badge', function ($row) {
                    $currencyCode = $row->currency->code ?? 'N/A';
                    return '<span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-black uppercase tracking-wider border border-blue-100">' . $currencyCode . '</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = company_route('settings.company-banks.edit', ['company_bank' => $row->id]);
                    $deleteUrl = company_route('settings.company-banks.destroy', ['company_bank' => $row->id]);

                    return '
                        <div class="flex items-center justify-end gap-2">
                            <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this bank account?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['bank_info', 'account_info', 'currency_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('catvara.settings.company-banks.index');
    }

    /**
     * Show the form for creating a new company bank.
     */
    public function create()
    {
        $this->authorize('create', 'company-banks');

        $currencies = DB::table('currencies')->select('id', 'code', 'name')->get();

        return view('catvara.settings.company-banks.form', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Store a newly created company bank.
     */
    public function store(Request $request)
    {
        $this->authorize('create', 'company-banks');

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'iban' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'bic_code' => 'nullable|string|max:20',
            'sort_code' => 'nullable|string|max:20',
            'branch' => 'nullable|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        CompanyBank::create([
            'company_id' => active_company_id(),
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'iban' => $validated['iban'],
            'swift_code' => $validated['swift_code'],
            'bic_code' => $validated['bic_code'] ?? null,
            'sort_code' => $validated['sort_code'] ?? null,
            'branch' => $validated['branch'],
            'currency_id' => $validated['currency_id'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('settings.company-banks.index', ['company' => active_company()->uuid])
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Show the form for editing the specified company bank.
     */
    public function edit(Company $company, $id)
    {
        $this->authorize('edit', 'company-banks');

        $companyBank = CompanyBank::where('company_id', $company->id)->findOrFail($id);
        $currencies = DB::table('currencies')->select('id', 'code', 'name')->get();

        return view('catvara.settings.company-banks.form', [
            'companyBank' => $companyBank,
            'currencies' => $currencies,
        ]);
    }

    /**
     * Update the specified company bank.
     */
    public function update(Request $request, Company $company, $id)
    {
        $this->authorize('edit', 'company-banks');

        $companyBank = CompanyBank::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'iban' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'bic_code' => 'nullable|string|max:20',
            'sort_code' => 'nullable|string|max:20',
            'branch' => 'nullable|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $companyBank->update([
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'iban' => $validated['iban'],
            'swift_code' => $validated['swift_code'],
            'bic_code' => $validated['bic_code'] ?? null,
            'sort_code' => $validated['sort_code'] ?? null,
            'branch' => $validated['branch'],
            'currency_id' => $validated['currency_id'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('settings.company-banks.index', ['company' => active_company()->uuid])
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Remove the specified company bank.
     */
    public function destroy(Company $company, $id)
    {
        $this->authorize('delete', 'company-banks');

        $companyBank = CompanyBank::where('company_id', $company->id)->findOrFail($id);
        $companyBank->delete();

        return redirect()->route('settings.company-banks.index', ['company' => active_company()->uuid])
            ->with('success', 'Bank account deleted successfully.');
    }
}
