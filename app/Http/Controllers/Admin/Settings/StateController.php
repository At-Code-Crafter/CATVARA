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
            $query = State::with('country')
                ->select('states.*')
                ->leftJoin('countries', 'states.country_id', '=', 'countries.id');

            // Apply filters
            if ($request->filled('is_active')) {
                $query->where('states.is_active', $request->is_active);
            }

            if ($request->filled('country_id')) {
                $query->where('states.country_id', $request->country_id);
            }

            return DataTables::eloquent($query)
                ->addColumn('name_html', function ($state) {
                    return '
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-map-marker-alt text-purple-500"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">' . e($state->name) . '</p>
                                ' . ($state->code ? '<p class="text-xs text-slate-400">Code: ' . e($state->code) . '</p>' : '') . '
                            </div>
                        </div>';
                })
                ->addColumn('country_html', function ($state) {
                    if (!$state->country) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-bold rounded bg-blue-50 text-blue-600">' . e($state->country->iso_code_2) . '</span>
                            <span class="text-slate-700">' . e($state->country->name) . '</span>
                        </div>';
                })
                ->addColumn('type_html', function ($state) {
                    if (!$state->type) {
                        return '<span class="text-slate-400">—</span>';
                    }
                    return '<span class="px-2.5 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-600">' . e(ucfirst($state->type)) . '</span>';
                })
                ->addColumn('status_html', function ($state) {
                    return $state->is_active
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active</span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactive</span>';
                })
                ->addColumn('actions', function ($state) {
                    $editUrl = route('states.edit', $state->uuid);
                    $deleteUrl = route('states.destroy', $state->uuid);
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
                ->rawColumns(['name_html', 'country_html', 'type_html', 'status_html', 'actions'])
                ->toJson();
        }

        $countries = Country::active()->ordered()->get();

        return view('catvara.admin.states.index', compact('countries'));
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
