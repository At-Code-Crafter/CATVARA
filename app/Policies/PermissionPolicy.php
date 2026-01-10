<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Generic Permission Policy for company-scoped permissions.
 * 
 * Usage in controllers:
 *   $this->authorize('view', 'warehouses');      // checks 'warehouses.view'
 *   $this->authorize('create', 'warehouses');    // checks 'warehouses.create'
 *   $this->authorize('edit', 'warehouses');      // checks 'warehouses.edit'
 *   $this->authorize('delete', 'warehouses');    // checks 'warehouses.delete'
 * 
 * Usage in Blade:
 *   @can('view', 'warehouses') ... @endcan
 *   @can('create', 'orders') ... @endcan
 */
class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     * Super admins bypass all permission checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    /**
     * Check if user can view the module.
     */
    public function view(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.view");
    }

    /**
     * Check if user can create in the module.
     */
    public function create(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.create");
    }

    /**
     * Check if user can edit in the module.
     */
    public function edit(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.edit");
    }

    /**
     * Check if user can update in the module (alias for edit).
     */
    public function update(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.edit");
    }

    /**
     * Check if user can delete in the module.
     */
    public function delete(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.delete");
    }

    /**
     * Check if user can access the module (for modules like POS).
     */
    public function access(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.access");
    }

    /**
     * Check if user can adjust (for inventory).
     */
    public function adjust(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.adjust");
    }

    /**
     * Check if user can transfer (for inventory).
     */
    public function transfer(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.transfer");
    }

    /**
     * Check if user can cancel (for orders).
     */
    public function cancel(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.cancel");
    }

    /**
     * Check if user can assign (for permissions/roles).
     */
    public function assign(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.assign");
    }

    /**
     * Check if user can manage (general management permission).
     */
    public function manage(User $user, string $module): bool
    {
        return $user->hasCompanyPermission("{$module}.manage");
    }

    /**
     * Generic permission check for any action.
     * Usage: $this->authorize('customAction', ['module', 'customAction'])
     */
    public function checkPermission(User $user, string $module, string $action): bool
    {
        return $user->hasCompanyPermission("{$module}.{$action}");
    }
}
