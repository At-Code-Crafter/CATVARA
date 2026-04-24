<?php

namespace Database\Seeders;

use App\Models\Company\Company;
use App\Models\Sales\DeliveryService;
use Illuminate\Database\Seeder;

class DeliveryServiceSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Vape Shop Distro (Self)', 'code' => 'SELF', 'sort_order' => 1],
            ['name' => 'FedEx', 'code' => 'FEDEX', 'sort_order' => 2],
            ['name' => 'DHL', 'code' => 'DHL', 'sort_order' => 3],
            ['name' => 'UPS', 'code' => 'UPS', 'sort_order' => 4],
            ['name' => 'DPD', 'code' => 'DPD', 'sort_order' => 5],
            ['name' => 'Royal Mail 48H', 'code' => 'RM48', 'sort_order' => 6],
            ['name' => 'Royal Mail 24H', 'code' => 'RM24', 'sort_order' => 7],
        ];

        Company::all()->each(function (Company $company) use ($defaults) {
            foreach ($defaults as $item) {
                DeliveryService::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $item['name']],
                    [
                        'code' => $item['code'],
                        'sort_order' => $item['sort_order'],
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
