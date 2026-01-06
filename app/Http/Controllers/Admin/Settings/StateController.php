<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\State;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{
    /**
     * Display a listing of states.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = State::with('country');

            // Apply filters
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            if ($request->filled('country_id')) {
                $query->where('country_id', $request->country_id);
            }

            return DataTables::eloquent($query)
                ->addColumn('country_name', function ($state) {
                    return $state->country ? $state->country->name : '-';
                })
                ->addColumn('status_badge', function ($state) {
                    return $state->is_active
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                })
                ->addColumn('action', function ($state) {
                    $editUrl = route('states.edit', $state->uuid);
                    $deleteUrl = route('states.destroy', $state->uuid);

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

        $countries = Country::active()->ordered()->get();

        return view('theme.adminlte.settings.states.index', compact('countries'));
    }

    /**
     * Get statistics for states.
     */
    public function stats(Request $request)
    {
        $query = State::query();

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        return response()->json([
            'total_states' => (clone $query)->count(),
            'active_states' => (clone $query)->where('is_active', true)->count(),
            'inactive_states' => (clone $query)->where('is_active', false)->count(),
        ]);
    }

    /**
     * Show the form for creating a new state.
     */
    public function create(Request $request)
    {
        $countries = Country::active()->ordered()->get();
        $selectedCountry = $request->country_id;

        return view('theme.adminlte.settings.states.create', compact('countries', 'selectedCountry'));
    }

    /**
     * Store a newly created state.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:10|unique:states,code,NULL,id,country_id,' . $request->country_id,
            'type' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');

        State::create($validated);

        return redirect()->route('states.index')
            ->with('success', 'State created successfully.');
    }

    /**
     * Show the form for editing the specified state.
     */
    public function edit(State $state)
    {
        $countries = Country::active()->ordered()->get();

        return view('theme.adminlte.settings.states.edit', compact('state', 'countries'));
    }

    /**
     * Update the specified state.
     */
    public function update(Request $request, State $state)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:10|unique:states,code,' . $state->id . ',id,country_id,' . $request->country_id,
            'type' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $state->update($validated);

        return redirect()->route('states.index')
            ->with('success', 'State updated successfully.');
    }

    /**
     * Remove the specified state.
     */
    public function destroy(State $state)
    {
        $state->delete();

        return response()->json([
            'success' => true,
            'message' => 'State deleted successfully.'
        ]);
    }
}
