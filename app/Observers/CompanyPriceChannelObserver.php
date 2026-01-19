<?php

namespace App\Observers;

use App\Models\Common\ActivityLog;
use App\Models\Pricing\CompanyPriceChannel;

class CompanyPriceChannelObserver
{
    /**
     * Handle the CompanyPriceChannel "created" event.
     */
    public function created(CompanyPriceChannel $pivot): void
    {
        $company = $pivot->company;
        $channel = $pivot->priceChannel;

        if ($company && $channel) {
            ActivityLog::log(
                $company,
                'enabled_price_channel',
                "Price Channel '{$channel->name}' was enabled",
                [
                    'channel_id' => $channel->id,
                    'channel_name' => $channel->name,
                    'channel_code' => $channel->code,
                    'old_status' => false,
                    'new_status' => true,
                    'risk_level' => 'low',
                ]
            );
        }
    }

    /**
     * Handle the CompanyPriceChannel "deleted" event.
     */
    public function deleted(CompanyPriceChannel $pivot): void
    {
        $company = $pivot->company;
        $channel = $pivot->priceChannel;

        if ($company && $channel) {
            ActivityLog::log(
                $company,
                'disabled_price_channel',
                "Price Channel '{$channel->name}' was disabled",
                [
                    'channel_id' => $channel->id,
                    'channel_name' => $channel->name,
                    'channel_code' => $channel->code,
                    'old_status' => true,
                    'new_status' => false,
                    'risk_level' => 'high',
                ]
            );
        }
    }
}
