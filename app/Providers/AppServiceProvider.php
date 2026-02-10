<?php

namespace App\Providers;

use App\Policies\PermissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerPermissionPolicy();
        $this->registerObservers();
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Auth\Module::observe(\App\Observers\ModuleObserver::class);
        \App\Models\Sales\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Catalog\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\Catalog\Brand::observe(\App\Observers\BrandObserver::class);
        \App\Models\Catalog\Attribute::observe(\App\Observers\AttributeObserver::class);
        \App\Models\Pricing\CompanyPriceChannel::observe(\App\Observers\CompanyPriceChannelObserver::class);
        \App\Models\Customer\Customer::observe(\App\Observers\CustomerObserver::class);
        \App\Models\Company\Company::observe(\App\Observers\CompanyObserver::class);
        \App\Models\Company\DocumentSequence::observe(\App\Observers\DocumentSequenceObserver::class);
        \App\Models\Accounting\Invoice::observe(\App\Observers\InvoiceObserver::class);
        \App\Models\Accounting\InvoiceItem::observe(\App\Observers\InvoiceItemObserver::class);
        \App\Models\Accounting\InvoiceAddress::observe(\App\Observers\InvoiceAddressObserver::class);
        \App\Models\Accounting\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\Accounting\PaymentAllocation::observe(\App\Observers\PaymentAllocationObserver::class);
        \App\Models\Accounting\PaymentApplication::observe(\App\Observers\PaymentApplicationObserver::class);
        \App\Models\Sales\OrderItem::observe(\App\Observers\OrderItemObserver::class);
        \App\Models\Sales\Quote::observe(\App\Observers\QuoteObserver::class);
        \App\Models\Sales\QuoteItem::observe(\App\Observers\QuoteItemObserver::class);
        \App\Models\Sales\DeliveryNote::observe(\App\Observers\DeliveryNoteObserver::class);
        \App\Models\Sales\DeliveryNoteItem::observe(\App\Observers\DeliveryNoteItemObserver::class);
        \App\Models\Inventory\CompanyInventorySetting::observe(\App\Observers\CompanyInventorySettingObserver::class);
        \App\Models\Inventory\InventoryBalance::observe(\App\Observers\InventoryBalanceObserver::class);
        \App\Models\Inventory\InventoryMovement::observe(\App\Observers\InventoryMovementObserver::class);
        \App\Models\Inventory\InventoryTransfer::observe(\App\Observers\InventoryTransferObserver::class);
        \App\Models\Inventory\InventoryTransferItem::observe(\App\Observers\InventoryTransferItemObserver::class);
        \App\Models\Pos\PosOrder::observe(\App\Observers\PosOrderObserver::class);
        \App\Models\Pos\PosOrderItem::observe(\App\Observers\PosOrderItemObserver::class);
        \App\Models\Web\WebOrder::observe(\App\Observers\WebOrderObserver::class);
        \App\Models\Web\WebOrderItem::observe(\App\Observers\WebOrderItemObserver::class);
        \App\Models\Web\WebOrderStatus::observe(\App\Observers\WebOrderStatusObserver::class);
        \App\Models\Returns\CreditNote::observe(\App\Observers\CreditNoteObserver::class);
        \App\Models\Returns\CreditNoteItem::observe(\App\Observers\CreditNoteItemObserver::class);

        $this->registerGenericObservers();
    }

    protected function registerGenericObservers(): void
    {
        $allow = config('activity-logging.generic_allow', []);
        $deny = array_flip(config('activity-logging.generic_deny', []));

        foreach ($allow as $modelClass) {
            if (! is_string($modelClass) || $modelClass === '') {
                continue;
            }

            if (isset($deny[$modelClass]) || ! class_exists($modelClass)) {
                continue;
            }

            $modelClass::observe(\App\Observers\GenericActivityObserver::class);
        }
    }

    /**
     * Register the permission policy for string-based authorization.
     */
    protected function registerPermissionPolicy(): void
    {
        // Super admin bypass - checked before any gate
        Gate::before(function ($user) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()
                ? true
                : null;
        });

        // Legacy 'perm' gate for backward compatibility
        Gate::define('perm', function ($user, string $permissionSlug) {
            return method_exists($user, 'hasCompanyPermission')
                ? $user->hasCompanyPermission($permissionSlug)
                : false;
        });

        // Register PermissionPolicy for string-based authorization
        // This allows: @can('view', 'warehouses') and $this->authorize('view', 'warehouses')
        Gate::policy('string', PermissionPolicy::class);

        // Define gates for each action that delegate to PermissionPolicy
        $policy = new PermissionPolicy;
        $actions = ['view', 'create', 'edit', 'update', 'delete', 'access', 'adjust', 'transfer', 'cancel', 'assign', 'manage'];

        foreach ($actions as $action) {
            Gate::define($action, function ($user, string $module) use ($policy, $action) {
                // Check before() first for super admin bypass
                $before = $policy->before($user, $action);
                if ($before !== null) {
                    return $before;
                }

                return $policy->{$action}($user, $module);
            });
        }
    }

    protected function registerRoutes()
    {
        //
    }
}
