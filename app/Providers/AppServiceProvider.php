<?php

namespace App\Providers;

use App\Policies\PermissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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
        $policy = new PermissionPolicy();
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
