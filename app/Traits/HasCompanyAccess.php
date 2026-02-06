<?php

namespace App\Traits;

use App\Models\Auth\CompanyUser;
use App\Models\Auth\CompanyUserRole;

trait HasCompanyAccess
{
    /**
     * Assign user to a company.
     */
    public function assignToCompany(int $companyId, bool $isOwner = false, bool $isActive = true): void
    {
        CompanyUser::updateOrCreate(
            ['company_id' => $companyId, 'user_id' => $this->id],
            ['is_owner' => $isOwner, 'is_active' => $isActive]
        );
    }

    /**
     * Remove user from a company.
     */
    public function removeFromCompany(int $companyId): void
    {
        CompanyUserRole::where('company_id', $companyId)
            ->where('user_id', $this->id)
            ->delete();

        CompanyUser::where('company_id', $companyId)
            ->where('user_id', $this->id)
            ->delete();
    }

    /**
     * Assign a role to the user for a specific company.
     */
    public function assignRole(int $roleId, int $companyId): void
    {
        CompanyUserRole::updateOrCreate(
            ['company_id' => $companyId, 'user_id' => $this->id, 'role_id' => $roleId],
            []
        );
    }

    /**
     * Sync roles for a specific company.
     */
    public function syncRoles(array $roleIds, int $companyId): void
    {
        CompanyUserRole::where('company_id', $companyId)
            ->where('user_id', $this->id)
            ->delete();

        foreach ($roleIds as $roleId) {
            $this->assignRole((int) $roleId, $companyId);
        }
    }

    /**
     * Remove a specific role for a company.
     */
    public function removeRole(int $roleId, int $companyId): void
    {
        CompanyUserRole::where('company_id', $companyId)
            ->where('user_id', $this->id)
            ->where('role_id', $roleId)
            ->delete();
    }
}
