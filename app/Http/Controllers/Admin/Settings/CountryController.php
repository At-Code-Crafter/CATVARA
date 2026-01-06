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
                ->addColumn('status_badge', function ($country) {
                    return $country->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                })
                ->addColumn('action', function ($country) {
                    $editUrl = route('countries.edit', $country->uuid);
                    $deleteUrl = route('countries.destroy', $country->uuid);

                    return '
                        <div class="btn-group btn-group-sm">
                            <a href="' . $editUrl . '" class="btn btn-info" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-delete"
                                data-url="' . $deleteUrl . '" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->toJson();
        }

        $regions = Country::select('region')
            ->distinct()
            ->whereNotNull('region')
            ->orderBy('region')
            ->pluck('region');

        return view('theme.adminlte.settings.countries.index', compact('regions'));
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
