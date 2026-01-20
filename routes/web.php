<?php

use App\Http\Controllers\Admin\CMS\TinyMCEController;
use App\Http\Controllers\Admin\CompanyContextController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Settings\CountryController;
use App\Http\Controllers\Admin\Settings\CurrencyController;
use App\Http\Controllers\Admin\Settings\ModuleController;
use App\Http\Controllers\Admin\Settings\PaymentTermController;
use App\Http\Controllers\Admin\Settings\PermissionController;
use App\Http\Controllers\Admin\Settings\PriceChannelController;
use App\Http\Controllers\Admin\Settings\RoleController;
use App\Http\Controllers\Admin\Settings\RolePermissionController;
use App\Http\Controllers\Admin\Settings\StateController;
use App\Http\Controllers\Admin\Settings\TenantController;
use App\Http\Controllers\Admin\Settings\UserController;
use App\Models\Company\Company;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\Route;

/**
 * Bind {company} by UUID (Laravel 12 compatible)
 */
Route::bind('company', function ($value) {
    return Company::where('uuid', $value)->firstOrFail();
});

/**
 * Bind {order} by ID (Laravel 12 compatible)
 */
Route::bind('order', function ($value) {
    return Order::findOrFail($value);
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    /**
     * Company selection (no company middleware here)
     */
    Route::get('select-company', [CompanyContextController::class, 'select'])
        ->name('company.select');

    Route::post('select-company', [CompanyContextController::class, 'store'])
        ->name('company.select.store');

    Route::post('switch-company', [CompanyContextController::class, 'switch'])
        ->name('company.switch');

    Route::post('switch-company/reset', [CompanyContextController::class, 'reset'])
        ->name('company.switch.reset');

    /**
     * Dashboard entry point (normalized)
     * Controller decides:
     * - if user has current company in session => redirect to /{company}/dashboard
     * - if user has one company => auto-select and redirect
     * - else => redirect to select-company
     */
    Route::get('dashboard', [CompanyContextController::class, 'dashboardEntry'])
        ->name('dashboard');

    /**
     * Global settings (no {company} in URL)
     * Keep route names: companies.*, users.* (matches your blades)
     */
    Route::prefix('settings')->group(function () {

        Route::resource('tenants', TenantController::class)->except(['destroy']);
        Route::get('tenants/load/stats', [TenantController::class, 'stats'])->name('tenants.stats');

        Route::resource('currencies', CurrencyController::class);
        Route::resource('price-channels', PriceChannelController::class);

        // Countries & States (Global Settings)
        Route::resource('countries', CountryController::class);
        Route::get('countries/load/stats', [CountryController::class, 'stats'])->name('countries.stats');
        Route::get('countries/{country}/states', [CountryController::class, 'getStates'])->name('countries.states');

        Route::resource('states', StateController::class);
        Route::get('states/load/stats', [StateController::class, 'stats'])->name('states.stats');

        Route::resource('users', UserController::class)->except(['destroy'])->names('users');

        Route::resource('modules', ModuleController::class)->except(['destroy']);
        Route::resource('permissions', PermissionController::class)->except(['destroy']);

        // User company-role assignment endpoints (used in show.blade.php)
        Route::get('users/roles/by-company', [UserController::class, 'rolesByCompany'])
            ->name('users.roles.byCompany');

        Route::post('users/{user}/assign-company', [UserController::class, 'assignCompany'])
            ->name('users.assignCompany');

        Route::post('users/{user}/remove-company', [UserController::class, 'removeCompany'])
            ->name('users.removeCompany');
    });

    /**
     * CMS routes (global, but you can wrap in company.selected if needed)
     */
    Route::prefix('cms')->as('cms.')->group(function () {
        Route::post('upload/tinymce', [TinyMCEController::class, 'upload'])->name('upload.tinymce');
    });

    /**
     * Company-scoped application (everything operational)
     */
    Route::prefix('{company}')
        ->middleware(['company.access', 'company.context'])
        ->group(function () {

            Route::get('dashboard', [DashboardController::class, 'dashboard'])
                ->name('company.dashboard');

            /**
             * Catalog Management
             */
            Route::prefix('catalog')->as('catalog.')->group(function () {
                Route::resource('categories', \App\Http\Controllers\Admin\Catalog\CategoryController::class);
                Route::get('categories/{category}/attributes', [\App\Http\Controllers\Admin\Catalog\CategoryController::class, 'getAttributes'])
                    ->name('categories.attributes');

                // Brands
                Route::resource('brands', \App\Http\Controllers\Admin\Catalog\BrandController::class);

                // Attributes
                Route::resource('attributes', \App\Http\Controllers\Admin\Catalog\AttributeController::class)->except(['show', 'destroy']);

                // Products
                Route::get('products/export', [\App\Http\Controllers\Admin\Catalog\ProductController::class, 'export'])->name('products.export');
                Route::get('products/import', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'index'])->name('products.import');
                Route::post('products/import/upload', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'upload'])->name('products.import.upload');
                Route::post('products/import/preview', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'preview'])->name('products.import.preview');
                Route::post('products/import/process', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'process'])->name('products.import.process');
                Route::resource('products', \App\Http\Controllers\Admin\Catalog\ProductController::class);
            });

            /**
             * Inventory Management
             */
            Route::prefix('inventory')->as('inventory.')->group(function () {
                Route::resource('inventory', \App\Http\Controllers\Admin\Inventory\InventoryController::class);
                Route::get('balances/data', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'balancesData'])->name('balances.data');
                Route::get('inventory/adjust', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'create'])->name('inventory.adjust');
                Route::get('adjust', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'create'])->name('adjust');
                Route::post('adjust', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'store'])->name('store');
                Route::post('transfer', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'transfer'])->name('transfer');

                // Transfers
                Route::resource('transfers', \App\Http\Controllers\Admin\Inventory\TransferController::class);
                Route::post('transfers/{transfer}/approve', [\App\Http\Controllers\Admin\Inventory\TransferController::class, 'approve'])->name('transfers.approve');
                Route::post('transfers/{transfer}/ship', [\App\Http\Controllers\Admin\Inventory\TransferController::class, 'ship'])->name('transfers.ship');
                Route::post('transfers/{transfer}/receive', [\App\Http\Controllers\Admin\Inventory\TransferController::class, 'receive'])->name('transfers.receive');

                // Movement History
                Route::get('movements', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'movements'])->name('movements');

                // Inventory Management CRUDs
                Route::resource('warehouses', \App\Http\Controllers\Admin\Inventory\WarehouseController::class);
                Route::resource('stores', \App\Http\Controllers\Admin\Inventory\StoreController::class);
                Route::resource('reasons', \App\Http\Controllers\Admin\Inventory\InventoryReasonController::class);

                // Variant Inventory Details
                Route::get('variant/{product_variant}/details', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'variantDetails'])->name('variant.details');
            });
            Route::prefix('settings')->as('settings.')->group(function () {

                Route::resource('roles', RoleController::class)->except(['show', 'destroy']);
                Route::resource('payment-terms', PaymentTermController::class);
                Route::resource('payment-methods', \App\Http\Controllers\Admin\Settings\PaymentMethodController::class);
                Route::resource('users', \App\Http\Controllers\Admin\Company\CompanyUserController::class);
                
                Route::get('company-profile', [\App\Http\Controllers\Admin\Company\CompanyProfileController::class, 'edit'])->name('company-profile.edit');
                Route::put('company-profile', [\App\Http\Controllers\Admin\Company\CompanyProfileController::class, 'update'])->name('company-profile.update');
            });

            /**
             * Customers Management
             */
            Route::get('customers/export', [\App\Http\Controllers\Admin\CustomerController::class, 'export'])->name('customers.export');
            Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
            Route::get('customers/load/stats', [\App\Http\Controllers\Admin\CustomerController::class, 'stats'])->name('customers.stats');
            Route::get('customers-search', [\App\Http\Controllers\Admin\CustomerController::class, 'search'])->name('customers.search');

            /**
             * Quotes Management (DISABLED)
             */
            // Route::resource('quotes', \App\Http\Controllers\Admin\QuoteController::class);
            // Route::get('quotes/load/stats', [\App\Http\Controllers\Admin\QuoteController::class, 'stats'])->name('quotes.stats');
            // Route::post('quotes/{quote}/send', [\App\Http\Controllers\Admin\QuoteController::class, 'send'])->name('quotes.send');
            // Route::post('quotes/{quote}/accept', [\App\Http\Controllers\Admin\QuoteController::class, 'accept'])->name('quotes.accept');
            // Route::post('quotes/{quote}/cancel', [\App\Http\Controllers\Admin\QuoteController::class, 'cancel'])->name('quotes.cancel');
            // Route::post('quotes/{quote}/convert-to-order', [\App\Http\Controllers\Admin\QuoteController::class, 'convertToOrder'])->name('quotes.convertToOrder');

            // Custom routes BEFORE resource to avoid conflicts
            Route::get('sales-orders/{sales_order}/print', [\App\Http\Controllers\Admin\Sales\SalesOrderController::class, 'printOrder'])->name('sales-orders.print');
            Route::post('sales-orders/{sales_order}/generate-invoice', [\App\Http\Controllers\Admin\Accounting\InvoiceController::class, 'storeFromOrder'])->name('sales-orders.generate-invoice');
            Route::get('invoices/{invoice}/print', [\App\Http\Controllers\Admin\Accounting\InvoiceController::class, 'print'])->name('invoices.print');
            Route::get('sales-orders-data', [\App\Http\Controllers\Admin\Sales\SalesOrderController::class, 'data'])->name('sales-orders.data');

            Route::resource('sales-orders', \App\Http\Controllers\Admin\Sales\SalesOrderController::class);

            Route::get('load-customers', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadCustomers'])->name('load-customers');
            Route::get('load-products', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadProducts'])->name('load-products');
            Route::get('load-payment-terms', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadPaymentTerms'])->name('load-payment-terms');
            Route::get('load-payment-methods', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadPaymentMethods'])->name('load-payment-methods');
            // Route::get('customers/create', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'createCustomer'])->name('customers.create');
            // Route::post('customers', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'storeCustomer'])->name('customers.store');
            Route::get('load-currencies', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadCurrencies'])->name('load-currencies');

            /**
             * Accounting Management
             */
            Route::prefix('accounting')->as('accounting.')->group(function () {
                // Payments
                Route::get('payments/data', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'data'])->name('payments.data');
                Route::get('payments/stats', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'stats'])->name('payments.stats');
                Route::get('payments/unallocated', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'unallocated'])->name('payments.unallocated');
                Route::get('payments/customer-documents', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'customerDocuments'])->name('payments.customer-documents');
                Route::post('payments/{payment}/confirm', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'confirm'])->name('payments.confirm');
                Route::post('payments/{payment}/cancel', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'cancel'])->name('payments.cancel');
                Route::post('payments/{payment}/apply', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'apply'])->name('payments.apply');
                Route::delete('payments/applications/{application}', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'removeApplication'])->name('payments.applications.remove');
                Route::delete('payments/{payment}/attachment', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'deleteAttachment'])->name('payments.deleteAttachment');
                Route::resource('payments', \App\Http\Controllers\Admin\Accounting\PaymentController::class);
            });

            /**
             * Activity Logs
             */
            Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
            Route::get('activity-logs/{id}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])->name('activity-logs.show');
        });

    // Invoice preview - OUTSIDE company group to avoid model binding
    Route::get('{company}/invoice-preview/{orderid}', [\App\Http\Controllers\Admin\Sales\SalesOrderController::class, 'invoicePreview'])->whereUuid('company')->whereNumber('orderid')->name('invoice-preview');
});

require __DIR__.'/auth.php';
