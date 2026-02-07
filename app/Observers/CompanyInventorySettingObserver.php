<?php

namespace App\Observers;

use App\Models\Inventory\CompanyInventorySetting;

class CompanyInventorySettingObserver extends BaseActivityObserver
{
    /**
     * Handle the CompanyInventorySetting "created" event.
     */
    public function created(CompanyInventorySetting $companyInventorySetting): void
    {
        $this->logCreated($companyInventorySetting);
    }

    /**
     * Handle the CompanyInventorySetting "updated" event.
     */
    public function updated(CompanyInventorySetting $companyInventorySetting): void
    {
        $this->logUpdated($companyInventorySetting);
    }
}
