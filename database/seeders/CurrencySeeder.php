<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pricing\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'decimal_places' => 2,
                'is_active' => true,
            ]
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
