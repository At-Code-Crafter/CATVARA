<?php

use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\Admin\AdminPaymentTermController;
use App\Http\Controllers\Admin\CMS\TinyMCEController;
use App\Http\Controllers\Admin\CompanyContextController;
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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Entry Routes
|--------------------------------------------------------------------------
*/

/**
 * Root: redirect to login.
 */
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Application Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Company Context (no {company} in URL)
    |--------------------------------------------------------------------------
    | - Company selection
    | - Switching current company in session
    | - Normalized dashboard entry point
    */

    /**
     * Company selection (no company middleware here).
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

    /*
    |--------------------------------------------------------------------------
    | Global Settings (no {company} in URL)
    |--------------------------------------------------------------------------
    | Keep route names as-is (matches blades):
    | - companies.* (if any in future)
    | - users.* (explicit names('users'))
    */

    Route::prefix('settings')->group(function () {

        /**
         * Tenants
         */
        Route::resource('tenants', TenantController::class)->except(['destroy']);

        /**
         * Currencies & Price Channels
         */
        Route::resource('currencies', CurrencyController::class);
        Route::resource('price-channels', PriceChannelController::class);

        /**
         * Countries & States
         */
        Route::resource('countries', CountryController::class);

        Route::resource('states', StateController::class);

        /**
         * Global Users
         */
        Route::resource('users', UserController::class)->except(['destroy'])->names('users');

        /**
         * Modules & Permissions
         */
        Route::resource('modules', ModuleController::class)->except(['destroy']);
        Route::resource('permissions', PermissionController::class)->except(['destroy']);

        /**
         * Admin: Payment Methods (all companies)
         */
        Route::get('payment-methods', [AdminPaymentMethodController::class, 'index'])
            ->name('admin.payment-methods.index');

        /**
         * Admin: Payment Terms (all companies)
         */
        Route::get('payment-terms', [AdminPaymentTermController::class, 'index'])
            ->name('admin.payment-terms.index');

        /**
         * Admin: Exchange Rates (all companies)
         */
        Route::get('exchange-rates', [\App\Http\Controllers\Admin\AdminExchangeRateController::class, 'index'])
            ->name('admin.exchange-rates.index');

        /**
         * Admin: Activity Logs (all companies)
         */
        Route::get('activity-logs', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'index'])
            ->name('admin.activity-logs.index');

        /**
         * User company-role assignment endpoints (used in show.blade.php)
         */
    });

    /*
    |--------------------------------------------------------------------------
    | CMS (Global)
    |--------------------------------------------------------------------------
    */

    Route::prefix('cms')->as('cms.')->group(function () {
        Route::post('upload/tinymce', [TinyMCEController::class, 'upload'])->name('upload.tinymce');
    });

    /*
    |--------------------------------------------------------------------------
    | Superadmin Routes
    |--------------------------------------------------------------------------
    | Group superadmin-only endpoints here. Add middleware/prefix as needed.
    */

    Route::prefix('superadmin')
        ->as('superadmin.')
        ->group(function () {
            // TODO: add superadmin routes here.
        });
});
