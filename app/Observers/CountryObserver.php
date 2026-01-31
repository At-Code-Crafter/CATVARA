<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use App\Models\Common\Country;

class CountryObserver
{
    /**
     * Handle the Country "created" event.
     */
    public function created(Country $country): void
    {
        ActivityLog::log(
            $country,
            'country_created',
            "Country '{$country->name}' was created",
            [
                'country_id' => $country->id,
                'name' => $country->name,
                'iso_code_2' => $country->iso_code_2,
                'iso_code_3' => $country->iso_code_3,
                'numeric_code' => $country->numeric_code,
                'phone_code' => $country->phone_code,
                'currency_code' => $country->currency_code,
                'capital' => $country->capital,
                'region' => $country->region,
                'subregion' => $country->subregion,
                'is_active' => $country->is_active,
            ]
        );
    }

    /**
     * Handle the Country "updated" event.
     */
    public function updated(Country $country): void
    {
        $changes = $country->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'changes' => [],
            'country_id' => $country->id,
            'name' => $country->name,
            'iso_code_2' => $country->iso_code_2,
        ];

        foreach ($changes as $key => $newValue) {
            $properties['changes'][$key] = [
                'old' => $country->getOriginal($key),
                'new' => $newValue,
            ];
        }

        ActivityLog::log(
            $country,
            'country_updated',
            "Country '{$country->name}' was updated",
            $properties
        );
    }
}
