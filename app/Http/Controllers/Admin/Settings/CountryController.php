<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CountryController extends Controller
{
    /**
     * Display a listing of countries.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Country::withCount('states');

            // Apply filters
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('region')) {
                $query->where('region', $request->region);
            }

            return DataTables::eloquent($query)
                ->addColumn('name_html', function ($country) {
                    return '
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-blue-600">' . substr($country->name, 0, 2) . '</span>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">' . e($country->name) . '</p>
                                <p class="text-xs text-slate-400">' . e($country->capital ?? 'No capital') . '</p>
                            </div>
                        </div>';
                })
                ->addColumn('iso_html', function ($country) {
                    return '
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-bold rounded bg-slate-100 text-slate-700">' . e($country->iso_code_2) . '</span>
                            <span class="px-2 py-1 text-xs font-medium rounded bg-slate-50 text-slate-500">' . e($country->iso_code_3) . '</span>
                        </div>';
                })
                ->addColumn('region_html', function ($country) {
                    if (!$country->region) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '
                        <div>
                            <p class="font-medium text-slate-700">' . e($country->region) . '</p>
                            ' . ($country->subregion ? '<p class="text-xs text-slate-400">' . e($country->subregion) . '</p>' : '') . '
                        </div>';
                })
                ->addColumn('states_html', function ($country) {
                    $count = $country->states_count;
                    $bgColor = $count > 0 ? 'bg-purple-50 text-purple-600' : 'bg-slate-50 text-slate-400';
                    return '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold ' . $bgColor . '">' . $count . '</span>';
                })
                ->addColumn('status_html', function ($country) {
                    return $country->is_active
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active</span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactive</span>';
                })
                ->addColumn('actions', function ($country) {
                    $editUrl = route('countries.edit', $country->uuid);
                    $deleteUrl = route('countries.destroy', $country->uuid);
                    return '
                        <div class="flex items-center justify-end gap-1">
                            <a href="' . $editUrl . '" class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <button type="button" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all btn-delete" data-url="' . $deleteUrl . '" title="Delete">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['name_html', 'iso_html', 'region_html', 'states_html', 'status_html', 'actions'])
                ->toJson();
        }

        $regions = Country::select('region')
            ->distinct()
            ->whereNotNull('region')
            ->orderBy('region')
            ->pluck('region');

        return view('catvara.admin.countries.index', compact('regions'));
    }

    /**
     * Get statistics for countries.
     */
    public function stats(Request $request)
    {
        $query = Country::query();

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        return response()->json([
            'total_countries' => (clone $query)->count(),
            'active_countries' => (clone $query)->where('is_active', true)->count(),
            'inactive_countries' => (clone $query)->where('is_active', false)->count(),
            'total_states' => \App\Models\Common\State::count(),
        ]);
    }

    /**
     * Show the form for creating a new country.
     */
    public function create()
    {
        return view('theme.adminlte.settings.countries.create');
    }

    /**
     * Store a newly created country.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'iso_code_2' => 'required|string|size:2|unique:countries,iso_code_2',
            'iso_code_3' => 'required|string|size:3|unique:countries,iso_code_3',
            'numeric_code' => 'nullable|string|max:3',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'capital' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'subregion' => 'nullable|string|max:100',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Country::create($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Country created successfully.');
    }

    /**
     * Show the form for editing the specified country.
     */
    public function edit(Country $country)
    {
        return view('theme.adminlte.settings.countries.edit', compact('country'));
    }

    /**
     * Update the specified country.
     */
    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'iso_code_2' => 'required|string|size:2|unique:countries,iso_code_2,' . $country->id,
            'iso_code_3' => 'required|string|size:3|unique:countries,iso_code_3,' . $country->id,
            'numeric_code' => 'nullable|string|max:3',
            'phone_code' => 'nullable|string|max:10',
            'currency_code' => 'nullable|string|max:3',
            'capital' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:50',
            'subregion' => 'nullable|string|max:100',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $country->update($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Country updated successfully.');
    }

    /**
     * Remove the specified country.
     */
    public function destroy(Country $country)
    {
        // Check if country has states
        if ($country->states()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete country with existing states. Please delete states first.'
            ], 422);
        }

        $country->delete();

        return response()->json([
            'success' => true,
            'message' => 'Country deleted successfully.'
        ]);
    }

    /**
     * Get states for a country (API endpoint).
     */
    public function getStates(Country $country)
    {
        $states = $country->states()
            ->active()
            ->ordered()
            ->get(['id', 'uuid', 'name', 'code']);

        return response()->json($states);
    }
}
