<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
| AJAX endpoints grouped here. URLs/names remain unchanged.
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Global (no {company} in URL)
    |--------------------------------------------------------------------------
    */

    Route::prefix('settings')->group(function () {
        Route::get('tenants/load/stats', [\App\Http\Controllers\Admin\Settings\TenantController::class, 'stats'])
            ->name('tenants.stats');

        Route::get('countries/load/stats', [\App\Http\Controllers\Admin\Settings\CountryController::class, 'stats'])
            ->name('countries.stats');

        Route::get('countries/{country}/states', [\App\Http\Controllers\Admin\Settings\CountryController::class, 'getStates'])
            ->name('countries.states');

        Route::get('states/load/stats', [\App\Http\Controllers\Admin\Settings\StateController::class, 'stats'])
            ->name('states.stats');

        Route::get('users/roles/by-company', [\App\Http\Controllers\Admin\Settings\UserController::class, 'rolesByCompany'])
            ->name('users.roles.byCompany');

        Route::post('users/{user}/assign-company', [\App\Http\Controllers\Admin\Settings\UserController::class, 'assignCompany'])
            ->name('users.assignCompany');

        Route::post('users/{user}/remove-company', [\App\Http\Controllers\Admin\Settings\UserController::class, 'removeCompany'])
            ->name('users.removeCompany');
    });

    /*
    |--------------------------------------------------------------------------
    | Company Scoped AJAX
    |--------------------------------------------------------------------------
    */

    Route::prefix('{company}')
        ->middleware(['company.access', 'company.context'])
        ->group(function () {

            Route::get('global-search', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])
                ->name('global-search');

            Route::prefix('inventory')->as('inventory.')->group(function () {
                Route::get('balances/data', [\App\Http\Controllers\Admin\Inventory\InventoryController::class, 'balancesData'])
                    ->name('balances.data');
            });

            Route::get('customers/load/stats', [\App\Http\Controllers\Admin\CustomerController::class, 'stats'])
                ->name('customers.stats');

            Route::get('customers-search', [\App\Http\Controllers\Admin\CustomerController::class, 'search'])
                ->name('customers.search');

            Route::get('quotes-data', [\App\Http\Controllers\Admin\Sales\QuoteController::class, 'data'])
                ->name('quotes.data');

            Route::get('sales-orders-data', [\App\Http\Controllers\Admin\Sales\SalesOrderController::class, 'data'])
                ->name('sales-orders.data');

            Route::get('load-customers', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadCustomers'])
                ->name('load-customers');

            Route::get('load-products', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadProducts'])
                ->name('load-products');

            Route::get('load-payment-terms', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadPaymentTerms'])
                ->name('load-payment-terms');

            Route::get('load-payment-methods', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadPaymentMethods'])
                ->name('load-payment-methods');

            Route::get('load-currencies', [\App\Http\Controllers\Admin\Sales\OrderController::class, 'loadCurrencies'])
                ->name('load-currencies');

            Route::prefix('accounting')->as('accounting.')->group(function () {
                Route::get('payments/data', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'data'])
                    ->name('payments.data');

                Route::get('payments/stats', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'stats'])
                    ->name('payments.stats');

                Route::get('payments/unallocated', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'unallocated'])
                    ->name('payments.unallocated');

                Route::get('payments/customer-documents', [\App\Http\Controllers\Admin\Accounting\PaymentController::class, 'customerDocuments'])
                    ->name('payments.customer-documents');
            });
        });
});
