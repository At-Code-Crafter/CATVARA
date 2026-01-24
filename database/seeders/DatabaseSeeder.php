<?php

namespace Database\Seeders;

use App\Models\Company\Company;
use App\Models\Pricing\Currency;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Company, Currency, Locations, Roles
        $this->call(VapeShopSetupSeeder::class);
        $this->call(InventorySeeder::class);
        $this->call(AuthenticationSeeder::class);
        $this->call(PaymentTermSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CountriesStatesSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(PaymentSeeder::class);

        // 2. Interactive Application Setup (Company & Users)
        $this->command->call('app:setup-application');
    }
}
