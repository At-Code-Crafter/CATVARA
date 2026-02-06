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
        \App\Models\Auth\Role::observe(\App\Observers\RoleObserver::class);
        \App\Models\Auth\Module::observe(\App\Observers\ModuleObserver::class);
        \App\Models\Auth\Permission::observe(\App\Observers\PermissionObserver::class);
        \App\Models\Common\Country::observe(\App\Observers\CountryObserver::class);
        \App\Models\Common\State::observe(\App\Observers\StateObserver::class);
        \App\Models\Accounting\PaymentMethod::observe(\App\Observers\PaymentMethodObserver::class);
        \App\Models\Accounting\PaymentTerm::observe(\App\Observers\PaymentTermObserver::class);
        \App\Models\Sales\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Catalog\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\Catalog\Brand::observe(\App\Observers\BrandObserver::class);
        \App\Models\Catalog\Attribute::observe(\App\Observers\AttributeObserver::class);
        \App\Models\Pricing\CompanyPriceChannel::observe(\App\Observers\CompanyPriceChannelObserver::class);
        \App\Models\Customer\Customer::observe(\App\Observers\CustomerObserver::class);
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
