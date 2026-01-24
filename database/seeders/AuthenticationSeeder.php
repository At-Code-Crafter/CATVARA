<?php

namespace Database\Seeders;

use App\Models\Auth\Module;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Company\Company;
use App\Models\Company\CompanyStatus;
use App\Models\User;
use Illuminate\Database\Seeder;           // adjust namespace if different
use Illuminate\Support\Facades\DB;     // adjust namespace if different
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'price-channels' => [
                'name' => 'Price Channels',
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
                        'name' => ucfirst($permission).' '.ucfirst($moduleSlug),
                        'module_id' => $module->id,
                        'is_active' => true,
                    ]
                );
            }
        }

    }
}
