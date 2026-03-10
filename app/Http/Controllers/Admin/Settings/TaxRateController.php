<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Tax\TaxGroup;
use App\Models\Tax\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TaxRateController extends Controller
{
    /**
     * Display a listing of tax rates.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $companyId = active_company_id();

            $query = TaxRate::query()
                ->where('company_id', $companyId)
                ->with(['group', 'country', 'state']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('code_badge', function ($row) {
                    return '<span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-black uppercase tracking-wider border border-blue-100">' . e($row->code) . '</span>';
                })
                ->addColumn('rate_info', function ($row) {
                    $rate = number_format((float) $row->rate, 4);
                    return '
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800">' . e($row->name) . '</span>
                            <span class="text-xs text-slate-400">' . $rate . '%</span>
                        </div>
                    ';
                })
                ->addColumn('group_badge', function ($row) {
                    if (!$row->group) {
                        return '<span class="text-slate-400 text-xs">—</span>';
                    }
                    return '<span class="px-3 py-1.5 rounded-lg bg-violet-50 text-violet-600 text-xs font-black uppercase tracking-wider border border-violet-100">' . e($row->group->name) . '</span>';
                })
                ->addColumn('region_info', function ($row) {
                    $country = $row->country ? e($row->country->name) : 'All Countries';
                    $state = $row->state ? e($row->state->name) : 'All States';
                    return '
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800 text-xs">' . $country . '</span>
                            <span class="text-[10px] text-slate-400">' . $state . '</span>
                        </div>
                    ';
                })
                ->addColumn('compound_badge', function ($row) {
                    return $row->is_compound
                        ? '<span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-xs font-black uppercase tracking-wider border border-amber-100">Compound</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-xs font-black uppercase tracking-wider border border-slate-100">Simple</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = company_route('settings.tax-rates.edit', ['tax_rate' => $row->id]);
                    $deleteUrl = company_route('settings.tax-rates.destroy', ['tax_rate' => $row->id]);

                    return '
                        <div class="flex items-center justify-end gap-2">
                            <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this tax rate?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['code_badge', 'rate_info', 'group_badge', 'region_info', 'compound_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('catvara.settings.tax-rates.index');
    }

    /**
     * Show the form for creating a new tax rate.
     */
    public function create()
    {
        $companyId = active_company_id();
        $taxGroups = TaxGroup::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('catvara.settings.tax-rates.form', compact('taxGroups', 'countries'));
    }

    /**
     * Store a newly created tax rate.
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
                Rule::unique('tax_rates', 'code')->where(fn($q) => $q->where('company_id', $companyId)),
            ],
            'name'         => ['required', 'string', 'max:255'],
            'tax_group_id' => ['required', 'exists:tax_groups,id'],
            'rate'         => ['required', 'numeric', 'min:0', 'max:100'],
            'country_id'   => ['nullable', 'exists:countries,id'],
            'state_id'     => ['nullable', 'exists:states,id'],
            'priority'     => ['nullable', 'integer', 'min:0'],
            'is_compound'  => ['nullable', 'boolean'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        TaxRate::create([
            'company_id'   => $companyId,
            'code'         => strtoupper($validated['code']),
            'name'         => $validated['name'],
            'tax_group_id' => $validated['tax_group_id'],
            'rate'         => $validated['rate'],
            'country_id'   => $validated['country_id'] ?? null,
            'state_id'     => $validated['state_id'] ?? null,
            'priority'     => $validated['priority'] ?? 0,
            'is_compound'  => $request->has('is_compound'),
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('settings.tax-rates.index', ['company' => active_company()->uuid])
            ->with('success', 'Tax rate created successfully.');
    }

    /**
     * Show the form for editing the specified tax rate.
     */
    public function edit($company, $id)
    {
        $companyId = active_company_id();
        $taxRate = TaxRate::where('company_id', $companyId)->findOrFail($id);
        $taxGroups = TaxGroup::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $states = $taxRate->country_id
            ? State::where('country_id', $taxRate->country_id)->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('catvara.settings.tax-rates.form', compact('taxRate', 'taxGroups', 'countries', 'states'));
    }

    /**
     * Update the specified tax rate.
     */
    public function update(Request $request, $company, $id)
    {
        $companyId = active_company_id();
        $taxRate = TaxRate::where('company_id', $companyId)->findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash:ascii',
                Rule::unique('tax_rates', 'code')
                    ->where(fn($q) => $q->where('company_id', $companyId))
                    ->ignore($taxRate->id),
            ],
            'name'         => ['required', 'string', 'max:255'],
            'tax_group_id' => ['required', 'exists:tax_groups,id'],
            'rate'         => ['required', 'numeric', 'min:0', 'max:100'],
            'country_id'   => ['nullable', 'exists:countries,id'],
            'state_id'     => ['nullable', 'exists:states,id'],
            'priority'     => ['nullable', 'integer', 'min:0'],
            'is_compound'  => ['nullable', 'boolean'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        $taxRate->update([
            'code'         => strtoupper($validated['code']),
            'name'         => $validated['name'],
            'tax_group_id' => $validated['tax_group_id'],
            'rate'         => $validated['rate'],
            'country_id'   => $validated['country_id'] ?? null,
            'state_id'     => $validated['state_id'] ?? null,
            'priority'     => $validated['priority'] ?? 0,
            'is_compound'  => $request->has('is_compound'),
            'is_active'    => $request->has('is_active'),
        ]);

        return redirect()->route('settings.tax-rates.index', ['company' => active_company()->uuid])
            ->with('success', 'Tax rate updated successfully.');
    }

    /**
     * Remove the specified tax rate.
     */
    public function destroy($company, $id)
    {
        $companyId = active_company_id();
        $taxRate = TaxRate::where('company_id', $companyId)->findOrFail($id);
        $taxRate->delete();

        return redirect()->route('settings.tax-rates.index', ['company' => active_company()->uuid])
            ->with('success', 'Tax rate deleted successfully.');
    }

    /**
     * AJAX: Get states by country.
     */
    public function statesByCountry(Request $request)
    {
        $states = State::where('country_id', $request->country_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($states);
    }
}
