<?php

namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company\Company;
use App\Models\Auth\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CompanyUserController extends Controller
{
    /**
     * Display users for the current company
     */
    public function index(Request $request)
    {
        $company = active_company();

        if ($request->ajax()) {
            $query = User::query()
                ->select('users.*')
                ->join('company_user', 'company_user.user_id', '=', 'users.id')
                ->where('company_user.company_id', $company->id)
                ->with(['allCompanyRoles' => function ($q) use ($company) {
                    $q->wherePivot('company_id', $company->id);
                }]);

            // Filter: Status
            if ($request->filled('is_active')) {
                $query->where('users.is_active', (int) $request->is_active);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('photo', function ($row) {
                    $src = $row->profile_photo
                        ? asset('storage/' . $row->profile_photo)
                        : asset('theme/adminlte/dist/img/user2-160x160.jpg');
                    return '<img src="' . e($src) . '" class="w-10 h-10 rounded-full object-cover border-2 border-slate-100">';
                })
                ->addColumn('roles', function ($row) use ($company) {
                    $roles = $row->allCompanyRoles->pluck('name')->toArray();
                    if (empty($roles)) {
                        return '<span class="text-slate-300">—</span>';
                    }
                    return collect($roles)->map(fn($r) => '<span class="px-2 py-0.5 rounded bg-purple-50 text-purple-600 text-[10px] font-bold border border-purple-100">' . e($r) . '</span>')->implode(' ');
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active
                        ? '<span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-wider border border-emerald-100">Active</span>'
                        : '<span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-black uppercase tracking-wider border border-slate-100">Inactive</span>';
                })
                ->editColumn('last_login_at', function ($row) {
                    if (!$row->last_login_at) {
                        return '<span class="text-slate-300">Never</span>';
                    }
                    return '<span class="text-slate-600 text-xs font-bold">' . \Carbon\Carbon::parse($row->last_login_at)->format('M d, Y') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = company_route('settings.users.show', ['user' => $row->id]);
                    $editUrl = company_route('settings.users.edit', ['user' => $row->id]);

                    return '
                    <div class="flex items-center justify-end gap-2">
                        <a href="' . $viewUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="View Profile">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['photo', 'roles', 'is_active', 'last_login_at', 'action'])
                ->make(true);
        }

        return view('catvara.settings.users.index', compact('company'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $company = active_company();
        $roles = Role::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        return view('catvara.settings.users.form', compact('company', 'roles'));
    }

    /**
     * Store a new user and link to company.
     */
    public function store(Request $request)
    {
        $company = active_company();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                'user_type' => 'ADMIN', // Default type for company users
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Link to company
            $user->companies()->attach($company->id, [
                'is_owner' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign roles
            foreach ($validated['role_ids'] as $roleId) {
                DB::table('company_user_role')->insert([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('settings.users.index', ['company' => $company->uuid])
                ->with('success', 'User created and assigned to company successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(Company $company, string $id)
    {
        $user = User::query()
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $company->id)
            ->where('users.id', $id)
            ->select('users.*')
            ->firstOrFail();

        $roles = $user->rolesForCompany($company->id)->get();

        return view('catvara.settings.users.show', compact('company', 'user', 'roles'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Company $company, string $id)
    {
        $user = User::query()
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $company->id)
            ->where('users.id', $id)
            ->select('users.*')
            ->firstOrFail();

        $roles = Role::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $userRoleIds = $user->rolesForCompany($company->id)->pluck('roles.id')->toArray();

        return view('catvara.settings.users.form', compact('company', 'user', 'roles', 'userRoleIds'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, Company $company, string $id)
    {
        $user = User::query()
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $company->id)
            ->where('users.id', $id)
            ->select('users.*')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
            }

            // Update roles for this company
            DB::table('company_user_role')
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->delete();

            foreach ($validated['role_ids'] as $roleId) {
                DB::table('company_user_role')->insert([
                    'company_id' => $company->id,
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            $user->forgetCompanyPermissionsCache($company->id);

            return redirect()->route('settings.users.index', ['company' => $company->uuid])
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified user from company.
     */
    public function destroy(Company $company, string $id)
    {
        $user = User::query()
            ->join('company_user', 'company_user.user_id', '=', 'users.id')
            ->where('company_user.company_id', $company->id)
            ->where('users.id', $id)
            ->select('users.*')
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Remove roles for this company
            DB::table('company_user_role')
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->delete();

            // Detach from company
            DB::table('company_user')
                ->where('company_id', $company->id)
                ->where('user_id', $user->id)
                ->delete();

            DB::commit();
            $user->forgetCompanyPermissionsCache($company->id);

            return redirect()->route('settings.users.index', ['company' => $company->uuid])
                ->with('success', 'User removed from company successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to remove user: ' . $e->getMessage()]);
        }
    }
}
