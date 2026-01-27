<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\ModuleStoreRequest;
use App\Http\Requests\Admin\Settings\ModuleUpdateRequest;
use App\Models\Auth\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Module::query()
                ->select(['modules.id', 'modules.name', 'modules.slug', 'modules.is_active', 'modules.created_at']);

            if ($request->filled('is_active')) {
                $query->where('modules.is_active', (int) $request->is_active);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('name_html', function ($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center">
                            <i class="fas fa-cube text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">' . e($row->name) . '</p>
                        </div>
                    </div>';
                })

                ->addColumn('slug_html', function ($row) {
                    return '<span class="font-mono text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded">' . e($row->slug) . '</span>';
                })

                ->addColumn('permissions_html', function ($row) {
                    $count = $row->permissions()->count();
                    return '
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-brand-50 text-brand-600 text-xs font-bold">
                        <i class="fas fa-key text-[10px]"></i> ' . $count . '
                    </span>';
                })

                ->addColumn('status_html', function ($row) {
                    return $row->is_active
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                          </span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                          </span>';
                })

                ->addColumn('actions', function ($row) {
                    $editUrl = route('modules.edit', $row->id);
                    return '
                    <a href="' . $editUrl . '" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-600 text-xs font-bold transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </a>';
                })

                ->rawColumns(['name_html', 'slug_html', 'permissions_html', 'status_html', 'actions'])
                ->make(true);
        }

        return view('catvara.admin.modules.index');
    }

    public function create()
    {
        return view('catvara.admin.modules.form');
    }

    public function store(ModuleStoreRequest $request)
    {
        $data = $request->validated();

        $slug = $data['slug'] ?? Str::slug($data['name']);

        Module::create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message'  => __('crud.created', ['name' => 'Module']),
                'redirect' => route('modules.index'),
            ]);
        }

        return redirect()->route('modules.index')->with('success', __('crud.created', ['name' => 'Module']));
    }

    public function edit(string $id)
    {
        $module = Module::findOrFail($id);
        return view('catvara.admin.modules.form', compact('module'));
    }

    public function update(ModuleUpdateRequest $request, string $id)
    {
        $module = Module::findOrFail($id);
        $data = $request->validated();

        $slug = $data['slug'] ?? Str::slug($data['name']);

        $module->update([
            'name'      => $data['name'],
            'slug'      => $slug,
            'is_active' => (bool)($data['is_active'] ?? false),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message'  => __('crud.updated', ['name' => 'Module']),
                'redirect' => route('modules.index'),
            ]);
        }

        return redirect()->route('modules.index')->with('success', __('crud.updated', ['name' => 'Module']));
    }
}
