<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UserAssignCompanyRequest;
use App\Http\Requests\Admin\Settings\UserStoreRequest;
use App\Http\Requests\Admin\Settings\UserUpdateRequest;
use App\Models\Auth\Role;
use App\Models\Company\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'users');

        if ($request->ajax()) {
            $query = User::query()
                ->select('users.*') // Select all to ensure we have IDs for routes
                ->withCount('companies');

            // Filter: Status
            if ($request->filled('is_active')) {
                $query->where('is_active', (int) $request->is_active);
            }

            // Filter: User Type
            if ($request->filled('user_type')) {
                $query->where('user_type', $request->user_type);
            }

            // Filter: Company
            if ($request->filled('company_id')) {
                $query->whereHas('companies', function ($q) use ($request) {
                    $q->where('companies.id', $request->company_id);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('photo', function ($row) {
                    $src = $row->profile_photo
                        ? asset('storage/'.$row->profile_photo)
                        : asset('theme/adminlte/dist/img/user2-160x160.jpg');

                    return '<img src="'.e($src).'" class="img-circle elevation-2 border" style="width:40px;height:40px;object-fit:cover;">';
                })

                ->editColumn('user_type', function ($row) {
                    $badge = ($row->user_type === 'SUPER_ADMIN') ? 'badge-dark' : 'badge-secondary';

                    return '<span class="badge '.$badge.' text-uppercase px-2 py-1" style="letter-spacing:.4px;">'.e($row->user_type).'</span>';
                })

                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Active</span>'
                        : '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i>Inactive</span>';
                })

                ->editColumn('last_login_at', function ($row) {
                    if (! $row->last_login_at) {
                        return '<span class="text-muted">—</span>';
                    }

                    return '<span class="small text-muted font-weight-bold">'.e(\Carbon\Carbon::parse($row->last_login_at)->format('d M, Y h:i A')).'</span>';
                })

                ->addColumn('action', function ($row) {
                    $viewUrl = route('users.show', $row->id);
                    $editUrl = route('users.edit', $row->id);

                    return '
                    <div class="flex items-center justify-end gap-2">
                        <a href="'.$viewUrl.'" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="View Profile">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="'.$editUrl.'" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>';
                })

                ->rawColumns(['photo', 'user_type', 'is_active', 'last_login_at', 'action'])
                ->make(true);
        }

        $companies = Company::orderBy('name')->get(['id', 'name', 'code']);

        return view('catvara.users.index', compact('companies'));
    }

    public function create()
    {
        $this->authorize('create', 'users');

        return view('catvara.users.create');
    }

    public function store(UserStoreRequest $request)
    {
        $this->authorize('create', 'users');

        $data = $request->validated();

        DB::beginTransaction();

        try {
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('users', 'public');
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'user_type' => $data['user_type'] ?? 'ADMIN',
                'profile_photo' => $photoPath,
                'email_verified_at' => $data['email_verified_at'] ?? null,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => __('crud.created', ['name' => 'User']),
                    'redirect' => route('users.index'),
                ]);
            }

            return redirect()->route('users.index')->with('success', __('crud.created', ['name' => 'User']));
        } catch (\Throwable $e) {
            DB::rollBack();

            if (! empty($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            throw $e;
        }
    }

    public function show(string $id)
    {
        $this->authorize('view', 'users');

        $user = User::query()
            ->with(['companies' => function ($q) {
                $q->select('companies.id', 'companies.uuid', 'companies.name', 'companies.code')
                    ->withPivot(['is_owner', 'is_active'])
                    ->withTimestamps();
            }, 'allCompanyRoles'])
            ->findOrFail($id);

        // all companies for assignment dropdown - now with roles
        $companies = Company::query()
            ->select('id', 'uuid', 'name', 'code')
            ->with(['roles' => function ($q) {
                $q->select('id', 'company_id', 'name')->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('catvara.users.show', compact('user', 'companies'));
    }

    public function edit(string $id)
    {
        $this->authorize('edit', 'users');

        $user = User::findOrFail($id);

        return view('catvara.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, string $id)
    {
        $this->authorize('edit', 'users');

        $user = User::findOrFail($id);
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $photoPath = $user->profile_photo;

            if ($request->hasFile('profile_photo')) {
                $newPath = $request->file('profile_photo')->store('users', 'public');

                if (! empty($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                }

                $photoPath = $newPath;
            }

            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'user_type' => $data['user_type'] ?? $user->user_type,
                'profile_photo' => $photoPath,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => __('crud.updated', ['name' => 'User']),
                    'redirect' => route('users.index'),
                ]);
            }

            return redirect()->route('users.index')->with('success', __('crud.updated', ['name' => 'User']));
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            throw $e;
        }
    }

    /**
     * Ajax: roles list by company_id (for assignment form)
     */
    public function rolesByCompany(Request $request)
    {
        $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $roles = Role::query()
            ->select('id', 'name')
            ->where('company_id', $request->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($roles);
    }

    /**
     * Assign user to company + role (single role per company for now)
     */
    public function assignCompany(UserAssignCompanyRequest $request, User $user)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            // Sync the main pivot (is_owner and is_active)
            $user->assignToCompany($data['company_id'], (bool) ($data['is_owner'] ?? false), (bool) ($data['is_active'] ?? false));

            // Sync the roles (Company User Role)
            $user->syncRoles($data['role_ids'] ?? [], (int) $data['company_id']);

            DB::commit();

            $user->forgetCompanyPermissionsCache((int) $data['company_id']);

            $msg = "Access permissions for {$user->name} updated successfully.";

            return $request->ajax()
                ? response()->json(['message' => $msg, 'redirect' => route('users.show', $user->id)])
                : redirect()->route('users.show', $user->id)->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $request->ajax()
                ? response()->json(['message' => 'Failed to update access: '.$e->getMessage()], 500)
                : back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function removeCompany(Request $request, User $user)
    {
        $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        DB::beginTransaction();
        try {
            // Remove from company (and its roles)
            $user->removeFromCompany((int) $request->company_id);

            DB::commit();

            $user->forgetCompanyPermissionsCache((int) $request->company_id);

            if ($request->ajax()) {
                return response()->json([
                    'message' => __('crud.updated', ['name' => 'User Company Access']),
                    'redirect' => route('users.show', $user->id),
                ]);
            }

            return redirect()->route('users.show', $user->id)->with('success', __('crud.updated', ['name' => 'User Company Access']));
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            throw $e;
        }
    }
}
