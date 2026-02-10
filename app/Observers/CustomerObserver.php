<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use App\Models\Customer\Customer;
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Str;

class CustomerObserver
{
    public function __construct(
        protected DocumentNumberService $docNumberService
    ) {}

    /**
     * Handle the Customer "creating" event.
     */
    public function creating(Customer $customer): void
    {
        if (empty($customer->uuid)) {
            $customer->uuid = (string) Str::uuid();
        }

        if (empty($customer->customer_code) && $customer->company_id) {
            $customer->customer_code = $this->docNumberService->generate(
                companyId: $customer->company_id,
                documentType: 'CUSTOMER',
                channel: 'CRM',
                year: null, // Customer codes usually don't reset yearly, they are global or perpetual
                fallbackPrefix: 'C-'
            );
        }
    }

    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        ActivityLog::log(
            $customer,
            'customer_created',
            "Customer '{$customer->display_name}' was created",
            [
                'customer_id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'type' => $customer->type,
                'display_name' => $customer->display_name,
            ]
        );
    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        // Get only dirty attributes, excluding timestamps
        $changes = $customer->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'customer_id' => $customer->id,
            'customer_code' => $customer->customer_code,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $customer->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $customer,
            'customer_updated',
            "Customer '{$customer->display_name}' was updated",
            $properties
        );
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        ActivityLog::log(
            $customer,
            'customer_deleted',
            "Customer '{$customer->display_name}' was deleted"
        );
    }
}
