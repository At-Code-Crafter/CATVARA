<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\RoleStoreRequest;
use App\Http\Requests\Admin\Settings\RoleUpdateRequest;
use App\Models\Auth\Module;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index(Company $company, Request $request)
    {
        $this->authorize('view', 'roles');

        if ($request->ajax()) {

            $query = Role::query()
                ->where('company_id', $company->id)
                ->select(['id', 'company_id', 'name', 'slug', 'is_active', 'created_at'])
                ->withCount('permissions');

            if ($request->filled('is_active')) {
                $query->where('is_active', (int) $request->is_active);
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('name_html', function ($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center">
                            <i class="fas fa-user-shield text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">' . e($row->name) . '</p>
                            <p class="text-xs text-slate-400 font-mono">' . e($row->slug) . '</p>
                        </div>
                    </div>';
                })

                ->addColumn('permissions_html', function ($row) {
                    return '
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-brand-50 text-brand-600 text-xs font-bold">
                        <i class="fas fa-key text-[10px]"></i> ' . (int)$row->permissions_count . '
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

                ->addColumn('actions', function ($row) use ($company) {
                    $editUrl = route('settings.roles.edit', ['company' => $company->uuid, 'role' => $row->id]);
                    return '
                    <a href="' . $editUrl . '" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-600 text-xs font-bold transition-colors">
                        <i class="fas fa-edit"></i> Edit
                    </a>';
                })

                ->rawColumns(['name_html', 'permissions_html', 'status_html', 'actions'])
                ->make(true);
        }

        return view('catvara.roles.index', compact('company'));
    }

    public function create(Company $company)
    {
        $this->authorize('create', 'roles');

        $modules = Module::query()
            ->where('is_active', true)
            ->with(['permissions' => function ($q) {
                $q->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('catvara.roles.create', compact('company', 'modules'));
    }

    public function store(Company $company, RoleStoreRequest $request)
    {
        $this->authorize('create', 'roles');

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $slug = $data['slug'] ?? Str::slug($data['name']);

            // Enforce uniqueness per company (roles table already has unique(company_id, slug))
            $role = Role::create([
                'company_id' => $company->id,
                'name'       => $data['name'],
                'slug'       => $slug,
                'is_active'  => (bool)($data['is_active'] ?? true),
            ]);

            $permissionIds = $data['permissions'] ?? [];
            $role->permissions()->sync($permissionIds);

            DB::commit();

            $redirect = route('settings.roles.index', ['company' => $company->uuid]);

            if ($request->ajax()) {
                return response()->json([
                    'message'  => __('crud.created', ['name' => 'Role']),
                    'redirect' => $redirect,
                ]);
            }

            return redirect($redirect)->with('success', __('crud.created', ['name' => 'Role']));
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            throw $e;
        }
    }

    public function edit(Company $company, Role $role)
    {
        $this->authorize('edit', 'roles');

        // Safety: role must belong to company
        abort_unless($role->company_id === $company->id, 404);

        $modules = Module::query()
            ->where('is_active', true)
            ->with(['permissions' => function ($q) {
                $q->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $selected = $role->permissions()->pluck('permissions.id')->toArray();

        return view('catvara.roles.edit', compact('company', 'role', 'modules', 'selected'));
    }

    public function update(Company $company, RoleUpdateRequest $request, Role $role)
    {
        $this->authorize('edit', 'roles');

        abort_unless($role->company_id === $company->id, 404);

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $slug = $data['slug'] ?? Str::slug($data['name']);

            $role->update([
                'name'      => $data['name'],
                'slug'      => $slug,
                'is_active' => (bool)($data['is_active'] ?? false),
            ]);

            $permissionIds = $data['permissions'] ?? [];
            $role->permissions()->sync($permissionIds);

            DB::commit();

            $redirect = route('settings.roles.index', ['company' => $company->uuid]);

            if ($request->ajax()) {
                return response()->json([
                    'message'  => __('crud.updated', ['name' => 'Role']),
                    'redirect' => $redirect,
                ]);
            }

            return redirect($redirect)->with('success', __('crud.updated', ['name' => 'Role']));
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            throw $e;
        }
    }
}
