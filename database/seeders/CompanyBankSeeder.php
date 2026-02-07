<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company\Company;
use App\Models\Company\CompanyBank;
use App\Models\Pricing\Currency;

class CompanyBankSeeder extends Seeder
{
    public function run()
    {
        $company = Company::first();
        if (!$company) return;

        $currency = Currency::where('code', 'AED')->first() ?? Currency::first();

        CompanyBank::firstOrCreate(
            [
                'company_id' => $company->id,
                'account_number' => '1234567890',
            ],
            [
                'bank_name' => 'Emirates NBD',
                'account_name' => $company->name,
                'iban' => 'AE00000000001234567890',
                'swift_code' => 'ENBDAE21XXX',
                'branch' => 'Business Bay',
                'currency_id' => $currency->id,
                'is_active' => true,
            ]
        );
    }
}
