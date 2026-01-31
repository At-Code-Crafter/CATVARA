<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Setup Company, Currency, Locations, Roles
        $this->call(VapeShopSetupSeeder::class);
        $this->call(InventorySeeder::class);
        $this->call(AuthenticationSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CountriesStatesSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(TaxGroupSeeder::class);

        // 2. Interactive Application Setup (Company & Users)
        $this->command->call('app:setup-application');
    }
}
