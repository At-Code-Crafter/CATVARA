<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\PermissionStoreRequest;
use App\Http\Requests\Admin\Settings\PermissionUpdateRequest;
use App\Models\Auth\Module;
use App\Models\Auth\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Permission::query()
                ->select([
                    'permissions.id',
                    'permissions.name',
                    'permissions.slug',
                    'permissions.module_id',
                    'permissions.is_active',
                    'permissions.created_at',
                    'modules.name as module_name',
                    'modules.slug as module_slug',
                ])
                ->leftJoin('modules', 'modules.id', '=', 'permissions.module_id');

            if ($request->filled('module_id')) {
                $query->where('permissions.module_id', $request->module_id);
            }

            if ($request->filled('is_active')) {
                $query->where('permissions.is_active', (int) $request->is_active);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('name_display', function ($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center">
                            <i class="fas fa-key text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">' . e($row->name) . '</p>
                            <p class="text-xs text-slate-400 font-mono">' . e($row->slug) . '</p>
                        </div>
                    </div>';
                })

                ->addColumn('module_display', function ($row) {
                    if ($row->module_name) {
                        return '
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-600 text-xs font-bold">
                            <i class="fas fa-cube text-[10px]"></i> ' . e($row->module_name) . '
                        </span>';
                    }
                    return '<span class="text-slate-400 text-xs">—</span>';
                })

                ->addColumn('status', function ($row) {
                    return (int)$row->is_active === 1
                        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                          </span>'
                        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                          </span>';
                })

                ->addColumn('actions', function ($row) {
                    $editUrl = route('permissions.edit', $row->id);
                    return '
                    <a href="' . $editUrl . '" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-600 text-xs font-bold transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </a>';
                })

                ->rawColumns(['name_display', 'module_display', 'status', 'actions'])
                ->make(true);
        }

        $modules = Module::query()->orderBy('name')->get();
        return view('catvara.admin.permissions.index', compact('modules'));
    }

    public function create()
    {
        $modules = Module::query()->orderBy('name')->get();
        return view('catvara.admin.permissions.form', compact('modules'));
    }

    public function store(PermissionStoreRequest $request)
    {
        $data = $request->validated();

        Permission::create([
            'name'      => $data['name'],
            'slug'      => $data['slug'],
            'module_id' => $data['module_id'],
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message'  => __('crud.created', ['name' => 'Permission']),
                'redirect' => route('permissions.index'),
            ]);
        }

        return redirect()->route('permissions.index')->with('success', __('crud.created', ['name' => 'Permission']));
    }

    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);
        $modules = Module::query()->orderBy('name')->get();

        return view('catvara.admin.permissions.form', compact('permission', 'modules'));
    }

    public function update(PermissionUpdateRequest $request, string $id)
    {
        $permission = Permission::findOrFail($id);
        $data = $request->validated();

        $permission->update([
            'name'      => $data['name'],
            'slug'      => $data['slug'],
            'module_id' => $data['module_id'],
            'is_active' => (bool)($data['is_active'] ?? false),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message'  => __('crud.updated', ['name' => 'Permission']),
                'redirect' => route('permissions.index'),
            ]);
        }

        return redirect()->route('permissions.index')->with('success', __('crud.updated', ['name' => 'Permission']));
    }
}
