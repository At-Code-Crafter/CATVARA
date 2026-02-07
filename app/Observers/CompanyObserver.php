<?php

namespace App\Observers;

use App\Models\Company\Company;

class CompanyObserver extends BaseActivityObserver
{
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        $this->logCreated($company);
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        $this->logUpdated($company);
    }
}
