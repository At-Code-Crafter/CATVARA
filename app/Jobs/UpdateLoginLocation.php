<?php

namespace App\Jobs;

use App\Models\UserLoginActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateLoginLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $activity;

    /**
     * Create a new job instance.
     */
    public function __construct(UserLoginActivity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $ip = $this->activity->ip_address;

            // Skip for local IPs
            if (in_array($ip, ['127.0.0.1', '::1'])) {
                return;
            }

            // Provider 1: ip-api.com
            $location = $this->fetchFromIpApi($ip);

            // Provider 2: freeipapi.com (Fallback)
            if (! $location) {
                $location = $this->fetchFromFreeIpApi($ip);
            }

            if ($location) {
                $this->activity->update([
                    'location' => $location,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch location for IP {$this->activity->ip_address}: ".$e->getMessage());
        }
    }

    protected function fetchFromIpApi($ip)
    {
        try {
            $response = Http::timeout(3)->withoutVerifying()->get("http://ip-api.com/json/{$ip}");
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'success') {
                    return trim(sprintf(
                        '%s, %s, %s',
                        $data['city'] ?? '',
                        $data['regionName'] ?? '',
                        $data['country'] ?? ''
                    ), ', ');
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch location for IP {$this->activity->ip_address}: ".$e->getMessage());
        }

        return null;
    }

    protected function fetchFromFreeIpApi($ip)
    {
        try {
            $response = Http::timeout(3)->withoutVerifying()->get("https://free.freeipapi.com/api/json/{$ip}");
            if ($response->successful()) {
                $data = $response->json();

                return trim(sprintf(
                    '%s, %s, %s',
                    $data['cityName'] ?? '',
                    $data['regionName'] ?? '',
                    $data['countryName'] ?? ''
                ), ', ');
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch location for IP {$this->activity->ip_address}: ".$e->getMessage());
        }

        return null;
    }
}
