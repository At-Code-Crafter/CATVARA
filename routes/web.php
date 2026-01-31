<?php

use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\Admin\AdminPaymentTermController;
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
use App\Http\Controllers\Admin\Settings\StateController;
use App\Http\Controllers\Admin\Settings\TenantController;
use App\Http\Controllers\Admin\Settings\UserController;
use App\Models\Company\Company;
use App\Models\Sales\Order;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Model Bindings (Laravel 12 compatible)
|--------------------------------------------------------------------------
*/

/**
 * Bind {company} by UUID.
 */
Route::bind('company', function ($value) {
    return Company::where('uuid', $value)->firstOrFail();
});

/**
 * Bind {order} by ID.
 */
Route::bind('order', function ($value) {
    return Order::findOrFail($value);
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('{company}')
        ->middleware(['company.access', 'company.context'])
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard & Global Search
            |--------------------------------------------------------------------------
            */

            Route::get('dashboard', [DashboardController::class, 'dashboard'])
                ->name('company.dashboard');

            /*
            |--------------------------------------------------------------------------
            | Catalog Management
            |--------------------------------------------------------------------------
            */

            Route::prefix('catalog')->as('catalog.')->group(function () {

                /**
                 * Categories
                 */
                Route::resource('categories', \App\Http\Controllers\Admin\Catalog\CategoryController::class);
                Route::get('categories/{category}/attributes', [\App\Http\Controllers\Admin\Catalog\CategoryController::class, 'getAttributes'])
                    ->name('categories.attributes');

                /**
                 * Brands
                 */
                Route::resource('brands', \App\Http\Controllers\Admin\Catalog\BrandController::class);

                /**
                 * Attributes
                 */
                Route::resource('attributes', \App\Http\Controllers\Admin\Catalog\AttributeController::class)->except(['show', 'destroy']);

                /**
                 * Products (Export/Import + CRUD)
                 */
                Route::get('products/export', [\App\Http\Controllers\Admin\Catalog\ProductController::class, 'export'])
                    ->name('products.export');

                Route::get('products/import', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'index'])
                    ->name('products.import');

                Route::post('products/import/upload', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'upload'])
                    ->name('products.import.upload');

                Route::post('products/import/preview', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'preview'])
                    ->name('products.import.preview');

                Route::post('products/import/process', [\App\Http\Controllers\Admin\Catalog\ProductImportController::class, 'process'])
                    ->name('products.import.process');

                Route::resource('products', \App\Http\Controllers\Admin\Catalog\ProductController::class);
            });

            /*
            |--------------------------------------------------------------------------
            | Inventory Management
            |--------------------------------------------------------------------------
            */

            Route::prefix('inventory')->as('inventory.')->group(function () {

                /**
                 * Inventory CRUD (resource)
                 */
                Route::resource('inventory', \App\Http\Controllers\Admin\Inventory\InventoryController::class);

                /**
                 * Adjustments & Transfers (custom endpoints)
                 * (kept as-is; no URL/name changes)
                 */
                Route::get('inventory/adjust', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'create'])
                    ->name('inventory.adjust');

                Route::get('adjust', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'create'])
                    ->name('adjust');

                Route::post('adjust', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'store'])
                    ->name('store');

                Route::post('transfer', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'transfer'])
                    ->name('transfer');

                /**
                 * Transfers
                 */
                Route::resource('transfers', \App\Http\Controllers\Admin\Inventory\TransferController::class);
                Route::post('transfers/{transfer}/approve', [\App\Http\Controllers\Admin\Inventory\TransferController::class, 'approve'])->name('transfers.approve');
                Route::post('transfers/{transfer}/ship', [\App\Http\Controllers\Admin\Inventory\TransferController::class, 'ship'])->name('transfers.ship');
                Route::post('transfers/{transfer}/receive', [\App\Http\Controllers\Admin\Inventory\TransferController::class, 'receive'])->name('transfers.receive');

                /**
                 * Movement History
                 */
                Route::get('movements', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'movements'])
                    ->name('movements');

                /**
                 * Inventory Management CRUDs
                 */
                Route::resource('warehouses', \App\Http\Controllers\Admin\Inventory\WarehouseController::class);
                Route::resource('stores', \App\Http\Controllers\Admin\Inventory\StoreController::class);
                Route::resource('reasons', \App\Http\Controllers\Admin\Inventory\InventoryReasonController::class);

                /**
                 * Variant Inventory Details
                 */
                Route::get('variant/{product_variant}/details', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'variantDetails'])
                    ->name('variant.details');
            });

            /*
            |--------------------------------------------------------------------------
            | Company Settings (Scoped)
            |--------------------------------------------------------------------------
            */

            Route::prefix('settings')->as('settings.')->group(function () {

                /**
                 * Roles / Payment Terms / Payment Methods / Users / Exchange Rates
                 */
                Route::resource('roles', RoleController::class)->except(['show', 'destroy']);
                Route::resource('payment-terms', PaymentTermController::class);
                Route::resource('payment-methods', \App\Http\Controllers\Admin\Settings\PaymentMethodController::class);
                Route::resource('users', \App\Http\Controllers\Admin\Company\CompanyUserController::class);
                Route::resource('exchange-rates', \App\Http\Controllers\Admin\Settings\ExchangeRateController::class);

                /**
                 * Company Profile
                 */
                Route::get('company-profile', [\App\Http\Controllers\Admin\Company\CompanyProfileController::class, 'edit'])
                    ->name('company-profile.edit');

                Route::put('company-profile', [\App\Http\Controllers\Admin\Company\CompanyProfileController::class, 'update'])
                    ->name('company-profile.update');
            });

            /*
            |--------------------------------------------------------------------------
            | Customers Management
            |--------------------------------------------------------------------------
            */

            Route::get('customers/export', [\App\Http\Controllers\Admin\CustomerController::class, 'export'])->name('customers.export');
            Route::get('customers/import', [\App\Http\Controllers\Admin\CustomerImportController::class, 'index'])->name('customers.import');
            Route::post('customers/import/upload', [\App\Http\Controllers\Admin\CustomerImportController::class, 'upload'])->name('customers.import.upload');
            Route::post('customers/import/preview', [\App\Http\Controllers\Admin\CustomerImportController::class, 'preview'])->name('customers.import.preview');
            Route::post('customers/import/process', [\App\Http\Controllers\Admin\CustomerImportController::class, 'process'])->name('customers.import.process');

            Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);

            /*
            |--------------------------------------------------------------------------
            | Quotes Management
            |--------------------------------------------------------------------------
            */

            Route::get('quotes/{quote}/print', [\App\Http\Controllers\Admin\Sales\QuoteController::class, 'printQuote'])->name('quotes.print');
            Route::post('quotes/{quote}/generate-order', [\App\Http\Controllers\Admin\Sales\QuoteController::class, 'generateOrder'])->name('quotes.generate-order');
            Route::resource('quotes', \App\Http\Controllers\Admin\Sales\QuoteController::class);

            /*
            |--------------------------------------------------------------------------
            | Sales Orders + POS Components
            |--------------------------------------------------------------------------
            */

            /**
             * Custom routes BEFORE resource to avoid conflicts.
             */
            Route::prefix('sales-orders')
                ->as('sales-orders.')
                ->controller(\App\Http\Controllers\Admin\Sales\SalesOrderController::class)
                ->group(function () {
                    Route::get('{sales_order}/print', 'printOrder')->name('print');
                    Route::post('{sales_order}/generate-invoice', 'storeFromOrder')->name('generate-invoice');
                    Route::put('{sales_order}/update-customers', 'updateCustomers')->name('update-customers');
                    Route::get('{sales_order}/customer-switcher', 'customerSwitcher')->name('customer-switcher');
                    Route::get('{sales_order}/finalize', 'finalize')->name('finalize');
                    Route::post('{sales_order}/finalize', 'finalizeStore')->name('finalize.store');
                });

            Route::resource('sales-orders', \App\Http\Controllers\Admin\Sales\SalesOrderController::class);

            /**
             * POS Components (modals)
             */
            Route::get('pos/components/variant-modal', [\App\Http\Controllers\Admin\Sales\SalesOrderComponentController::class, 'variantModal'])
                ->name('pos.components.variant-modal');

            Route::get('pos/components/custom-item-modal', [\App\Http\Controllers\Admin\Sales\SalesOrderComponentController::class, 'customItemModal'])
                ->name('pos.components.custom-item-modal');

            /**
             * Invoices
             */
            Route::get('invoices/{invoice}/print', [\App\Http\Controllers\Admin\Accounting\InvoiceController::class, 'print'])
                ->name('invoices.print');

            /*
            |--------------------------------------------------------------------------
            | Accounting Management
            |--------------------------------------------------------------------------
            */

            Route::prefix('accounting')->as('accounting.')->group(function () {

                Route::post('payments/{payment}/confirm', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'confirm'])
                    ->name('payments.confirm');

                Route::post('payments/{payment}/cancel', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'cancel'])
                    ->name('payments.cancel');

                Route::post('payments/{payment}/apply', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'apply'])
                    ->name('payments.apply');

                Route::delete('payments/applications/{application}', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'removeApplication'])
                    ->name('payments.applications.remove');

                Route::delete('payments/{payment}/attachment', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'deleteAttachment'])
                    ->name('payments.deleteAttachment');

                Route::resource('payments', \App\Http\Controllers\Admin\Accounting\PaymentController::class);
            });

            /*
            |--------------------------------------------------------------------------
            | Activity Logs (Company scoped)
            |--------------------------------------------------------------------------
            */

            Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])
                ->name('activity-logs.index');

            Route::get('activity-logs/{id}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'show'])
                ->name('activity-logs.show');
        });

    /*
    |--------------------------------------------------------------------------
    | Invoice Preview (OUTSIDE company group to avoid model binding)
    |--------------------------------------------------------------------------
    | NOTE: Keep exact URL and route name.
    */

    Route::get('{company}/invoice-preview/{orderid}', [\App\Http\Controllers\Admin\Sales\SalesOrderController::class, 'invoicePreview'])
        ->whereUuid('company')
        ->whereNumber('orderid')
        ->name('invoice-preview');
});

require __DIR__.'/auth.php';
