<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Tax\TaxGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TaxGroupController extends Controller
{
    /**
     * Display a listing of tax groups.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $companyId = active_company_id();

            $query = TaxGroup::query()
                ->where('company_id', $companyId)
                ->withCount('rates')
                ->withCount([
                    'rates as active_rates_count' => function ($rateQuery) {
                        $rateQuery->where('is_active', true);
                    },
                ])
                ->withSum([
                    'rates as active_rate_sum' => function ($rateQuery) {
                        $rateQuery->where('is_active', true);
                    },
                ], 'rate');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('code_badge', function ($row) {
                    return '<span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-black uppercase tracking-wider border border-blue-100">' . e($row->code) . '</span>';
                })
                ->addColumn('group_info', function ($row) {
                    $description = $row->description ? e($row->description) : 'No description';

                    return '
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">' . e($row->name) . '</span>
                            <span class="text-xs text-slate-400">' . $description . '</span>
                        </div>
                    ';
                })
                ->addColumn('tax_mode_badge', function ($row) {
                    if ($row->is_tax_inclusive) {
                        return '<span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-xs font-black uppercase tracking-wider border border-amber-100">Inclusive</span>';
                    }

                    return '<span class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-black uppercase tracking-wider border border-indigo-100">Exclusive</span>';
                })
                ->addColumn('rates_summary', function ($row) {
                    $activeRates = (int) ($row->active_rates_count ?? 0);
                    $totalRates = (int) ($row->rates_count ?? 0);
                    $activeRateSum = number_format((float) ($row->active_rate_sum ?? 0), 4);

                    return '
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">' . $activeRates . ' active / ' . $totalRates . ' total</span>
                            <span class="text-xs text-slate-400">' . $activeRateSum . '% total</span>
                        </div>
                    ';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = company_route('settings.tax-groups.edit', ['tax_group' => $row->id]);
                    $deleteUrl = company_route('settings.tax-groups.destroy', ['tax_group' => $row->id]);

                    return '
                        <div class="flex items-center justify-end gap-2">
                            <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this tax group?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['code_badge', 'group_info', 'tax_mode_badge', 'rates_summary', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('catvara.settings.tax-groups.index');
    }

    /**
     * Show the form for creating a new tax group.
     */
    public function create()
    {
        return view('catvara.settings.tax-groups.form');
    }

    /**
     * Store a newly created tax group.
     */
    public function store(Request $request)
    {
        $companyId = active_company_id();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash:ascii',
                Rule::unique('tax_groups', 'code')->where(function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                }),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_tax_inclusive' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        TaxGroup::create([
            'company_id' => $companyId,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_tax_inclusive' => $request->has('is_tax_inclusive'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('settings.tax-groups.index', ['company' => active_company()->uuid])
            ->with('success', 'Tax group created successfully.');
    }

    /**
     * Show the form for editing the specified tax group.
     */
    public function edit(Company $company, $id)
    {
        $taxGroup = TaxGroup::where('company_id', $company->id)->findOrFail($id);

        return view('catvara.settings.tax-groups.form', [
            'taxGroup' => $taxGroup,
        ]);
    }

    /**
     * Update the specified tax group.
     */
    public function update(Request $request, Company $company, $id)
    {
        $taxGroup = TaxGroup::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash:ascii',
                Rule::unique('tax_groups', 'code')
                    ->where(function ($query) use ($company) {
                        $query->where('company_id', $company->id);
                    })
                    ->ignore($taxGroup->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_tax_inclusive' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $taxGroup->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_tax_inclusive' => $request->has('is_tax_inclusive'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('settings.tax-groups.index', ['company' => active_company()->uuid])
            ->with('success', 'Tax group updated successfully.');
    }

    /**
     * Remove the specified tax group.
     */
    public function destroy(Company $company, $id)
    {
        $taxGroup = TaxGroup::where('company_id', $company->id)->findOrFail($id);
        $taxGroup->delete();

        return redirect()->route('settings.tax-groups.index', ['company' => active_company()->uuid])
            ->with('success', 'Tax group deleted successfully.');
    }
}
