<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Auth\Module;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;

use App\Models\Company\Company;           // adjust namespace if different
use App\Models\Company\CompanyStatus;     // adjust namespace if different

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * MODULE → PERMISSIONS MATRIX (GLOBAL)
         * Format: 'module_slug' => ['name' => 'Display Name', 'permissions' => ['action1', 'action2', ...]]
         * Permission slug becomes: module_slug.action (e.g., warehouses.view)
         */
        $matrix = [
            // Access Control
            'users' => [
                'name' => 'User Management',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'roles' => [
                'name' => 'Roles',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'permissions' => [
                'name' => 'Permissions',
                'permissions' => ['view', 'assign'],
            ],
            'modules' => [
                'name' => 'Modules',
                'permissions' => ['view', 'manage'],
            ],

            // Company
            'company' => [
                'name' => 'Company',
                'permissions' => ['view', 'edit'],
            ],
            'companies' => [
                'name' => 'Companies (Tenants)',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],

            // Sales
            'orders' => [
                'name' => 'Sales Orders',
                'permissions' => ['view', 'create', 'edit', 'delete', 'cancel'],
            ],
            'invoices' => [
                'name' => 'Invoices',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'credit-notes' => [
                'name' => 'Credit Notes',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'quotes' => [
                'name' => 'Quotes',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],

            // Catalog
            'categories' => [
                'name' => 'Categories',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'products' => [
                'name' => 'Products',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'attributes' => [
                'name' => 'Attributes',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],

            // Inventory
            'inventory' => [
                'name' => 'Inventory',
                'permissions' => ['view', 'adjust', 'transfer'],
            ],
            'warehouses' => [
                'name' => 'Warehouses',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'stores' => [
                'name' => 'Stores',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'inventory-reasons' => [
                'name' => 'Inventory Reasons',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'transfers' => [
                'name' => 'Stock Transfers',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],

            // Customers
            'customers' => [
                'name' => 'Customers',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],

            // Accounting
            'payments' => [
                'name' => 'Payments',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'allocations' => [
                'name' => 'Allocations',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'refunds' => [
                'name' => 'Refunds',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],

            // POS
            'pos' => [
                'name' => 'POS',
                'permissions' => ['access', 'view', 'create'],
            ],

            // Reports
            'reports' => [
                'name' => 'Reports',
                'permissions' => ['view'],
            ],

            // Settings
            'currencies' => [
                'name' => 'Currencies',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'payment-terms' => [
                'name' => 'Payment Terms',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'countries' => [
                'name' => 'Countries',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
            'states' => [
                'name' => 'States',
                'permissions' => ['view', 'create', 'edit', 'delete'],
            ],
        ];

        foreach ($matrix as $moduleSlug => $data) {

            $module = Module::updateOrCreate(
                ['slug' => $moduleSlug],
                [
                    'name' => $data['name'],
                    'is_active' => true,
                ]
            );

            foreach ($data['permissions'] as $permission) {
                Permission::updateOrCreate(
                    ['slug' => "{$moduleSlug}.{$permission}"],
                    [
                        'name' => ucfirst($permission) . ' ' . ucfirst($moduleSlug),
                        'module_id' => $module->id,
                        'is_active' => true,
                    ]
                );
            }
        }

        /**
         * SUPER ADMIN (simple + required only)
         */
        $email = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'Admin@12345');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        // 1) Ensure ACTIVE company status exists
        $activeStatus = CompanyStatus::updateOrCreate(
            ['code' => 'ACTIVE'],
            ['name' => 'Active', 'is_active' => true]
        );

        // 2) Ensure at least one company exists
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'uuid' => (string) Str::uuid(),
                'name' => 'Default Company',
                'legal_name' => 'Default Company',
                'code' => 'DEFAULT',
                'company_status_id' => $activeStatus->id,
            ]);
        }

        // 3) Create/Update super admin user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'is_active' => true,
                'user_type' => 'SUPER_ADMIN',
            ]
        );

        // 4) Link user to company (owner)
        DB::table('company_user')->updateOrInsert(
            ['company_id' => $company->id, 'user_id' => $user->id],
            ['is_owner' => true, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
        );

        // 5) Create/Update role in that company
        $role = Role::updateOrCreate(
            ['company_id' => $company->id, 'slug' => 'admin'],
            ['name' => 'Admin', 'is_active' => true]
        );

        // 6) Attach ALL permissions to that role
        $permissionIds = Permission::pluck('id')->toArray();
        foreach ($permissionIds as $pid) {
            DB::table('role_permission')->updateOrInsert(
                ['role_id' => $role->id, 'permission_id' => $pid],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // 7) Assign role to user for that company
        DB::table('company_user_role')->updateOrInsert(
            ['company_id' => $company->id, 'user_id' => $user->id, 'role_id' => $role->id],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
}
