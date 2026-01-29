<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Common\Country;
use App\Models\Company;
use App\Models\Tax\TaxGroup;
use App\Models\Tax\TaxRate;

class TaxGroupsRatesSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure countries exist
        $countries = Country::query()->orderBy('name')->get();
        if ($countries->isEmpty()) {
            $this->command->error('No countries found. Run CountriesStatesSeeder first.');
            return;
        }

        // Ensure companies exist (your tax tables require company_id)
        $companies = Company::query()->orderBy('id')->get();
        if ($companies->isEmpty()) {
            $this->command->error('No companies found. Seed companies first (tax_groups.company_id is required).');
            return;
        }

        // Ask which companies to seed for
        $seedForAllCompanies = $this->command->confirm('Seed tax groups for ALL companies?', true);

        $targetCompanies = $companies;
        if (!$seedForAllCompanies) {
            $companyChoices = $companies->map(fn ($c) => "{$c->id} - " . ($c->name ?? 'Company'))->toArray();
            $selected = $this->command->choice('Select Company', $companyChoices);
            $companyId = (int) trim(explode('-', $selected)[0]);
            $targetCompanies = $companies->where('id', $companyId);
        }

        // Available country choices
        $countryChoices = $countries->map(fn ($c) => "{$c->id} - {$c->name} ({$c->iso_code_2})")->values()->toArray();
        $selectedCountryIds = [];

        do {
            // Filter out already selected countries (avoid duplicates)
            $remainingChoices = array_values(array_filter($countryChoices, function ($choice) use ($selectedCountryIds) {
                $id = (int) trim(explode('-', $choice)[0]);
                return !in_array($id, $selectedCountryIds, true);
            }));

            if (empty($remainingChoices)) {
                $this->command->warn('No more countries left to select.');
                break;
            }

            $picked = $this->command->choice('Select country to seed tax groups for', $remainingChoices);
            $countryId = (int) trim(explode('-', $picked)[0]);
            $country = $countries->firstWhere('id', $countryId);

            if (!$country) {
                $this->command->error('Invalid country selection.');
                break;
            }

            $selectedCountryIds[] = $countryId;

            $countryTemplate = $this->getCountryTaxTemplate($country->iso_code_2);

            if (!$countryTemplate) {
                $this->command->warn("No predefined template found for {$country->name} ({$country->iso_code_2}).");
                $this->command->warn('Skipping. You can add a template in getCountryTaxTemplate().');
            } else {
                foreach ($targetCompanies as $company) {
                    $this->seedTemplatesForCompany($company->id, $country->id, $country->iso_code_2, $countryTemplate);
                }

                $this->command->info("Seeded tax groups/rates for {$country->name} ({$country->iso_code_2}).");
            }

        } while ($this->command->confirm('Do you want to add tax groups for another country?', false));
    }

    /**
     * Returns templates by ISO2 country code.
     * You can extend this anytime.
     */
    private function getCountryTaxTemplate(string $iso2): ?array
    {
        $iso2 = strtoupper($iso2);

        $templates = [
            // UAE (VAT 5%)
            'AE' => [
                [
                    'code' => 'UAE_VAT_STANDARD',
                    'name' => 'UAE VAT Standard',
                    'description' => 'UAE standard VAT rate',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT5', 'name' => 'VAT 5%', 'rate' => 5.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'UAE_VAT_ZERO',
                    'name' => 'UAE VAT Zero Rated',
                    'description' => 'Zero-rated supplies (0%)',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT0', 'name' => 'VAT 0%', 'rate' => 0.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'UAE_VAT_EXEMPT',
                    'name' => 'UAE VAT Exempt',
                    'description' => 'VAT exempt supplies',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'EXEMPT', 'name' => 'Exempt 0%', 'rate' => 0.0000, 'priority' => 1],
                    ],
                ],
            ],

            // UK (VAT 20/5/0)
            'GB' => [
                [
                    'code' => 'UK_VAT_STANDARD',
                    'name' => 'UK VAT Standard',
                    'description' => 'UK standard VAT rate',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT20', 'name' => 'VAT 20%', 'rate' => 20.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'UK_VAT_REDUCED',
                    'name' => 'UK VAT Reduced',
                    'description' => 'UK reduced VAT rate',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT5', 'name' => 'VAT 5%', 'rate' => 5.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'UK_VAT_ZERO',
                    'name' => 'UK VAT Zero Rated',
                    'description' => 'Zero-rated supplies (0%)',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT0', 'name' => 'VAT 0%', 'rate' => 0.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'UK_VAT_EXEMPT',
                    'name' => 'UK VAT Exempt',
                    'description' => 'VAT exempt supplies',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'EXEMPT', 'name' => 'Exempt 0%', 'rate' => 0.0000, 'priority' => 1],
                    ],
                ],
            ],

            // Germany (VAT 19/7)
            'DE' => [
                [
                    'code' => 'DE_VAT_STANDARD',
                    'name' => 'Germany VAT Standard',
                    'description' => 'Germany standard VAT rate',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT19', 'name' => 'VAT 19%', 'rate' => 19.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'DE_VAT_REDUCED',
                    'name' => 'Germany VAT Reduced',
                    'description' => 'Germany reduced VAT rate',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT7', 'name' => 'VAT 7%', 'rate' => 7.0000, 'priority' => 1],
                    ],
                ],
            ],

            // France (commonly used VAT rates)
            'FR' => [
                [
                    'code' => 'FR_VAT_STANDARD',
                    'name' => 'France VAT Standard',
                    'description' => 'France standard VAT rate',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT20', 'name' => 'VAT 20%', 'rate' => 20.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'FR_VAT_REDUCED_10',
                    'name' => 'France VAT Reduced 10%',
                    'description' => 'France reduced VAT rate (10%)',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT10', 'name' => 'VAT 10%', 'rate' => 10.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'FR_VAT_REDUCED_55',
                    'name' => 'France VAT Reduced 5.5%',
                    'description' => 'France reduced VAT rate (5.5%)',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT5_5', 'name' => 'VAT 5.5%', 'rate' => 5.5000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'FR_VAT_SUPER_REDUCED_21',
                    'name' => 'France VAT Super Reduced 2.1%',
                    'description' => 'France super reduced VAT rate (2.1%)',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'VAT2_1', 'name' => 'VAT 2.1%', 'rate' => 2.1000, 'priority' => 1],
                    ],
                ],
            ],

            // India (generic GST slabs - useful baseline; actual depends on goods/services)
            'IN' => [
                [
                    'code' => 'IN_GST_0',
                    'name' => 'India GST 0%',
                    'description' => 'GST 0% (zero-rated/exempt baseline)',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'GST0', 'name' => 'GST 0%', 'rate' => 0.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'IN_GST_5',
                    'name' => 'India GST 5%',
                    'description' => 'GST 5% slab',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'GST5', 'name' => 'GST 5%', 'rate' => 5.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'IN_GST_12',
                    'name' => 'India GST 12%',
                    'description' => 'GST 12% slab',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'GST12', 'name' => 'GST 12%', 'rate' => 12.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'IN_GST_18',
                    'name' => 'India GST 18%',
                    'description' => 'GST 18% slab',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'GST18', 'name' => 'GST 18%', 'rate' => 18.0000, 'priority' => 1],
                    ],
                ],
                [
                    'code' => 'IN_GST_28',
                    'name' => 'India GST 28%',
                    'description' => 'GST 28% slab',
                    'is_tax_inclusive' => false,
                    'rates' => [
                        ['code' => 'GST28', 'name' => 'GST 28%', 'rate' => 28.0000, 'priority' => 1],
                    ],
                ],
            ],
        ];

        return $templates[$iso2] ?? null;
    }

    private function seedTemplatesForCompany(int $companyId, int $countryId, string $iso2, array $templates): void
    {
        foreach ($templates as $groupData) {
            $group = TaxGroup::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => $groupData['code'],
                ],
                [
                    'name' => $groupData['name'],
                    'description' => $groupData['description'] ?? null,
                    'is_tax_inclusive' => (bool) ($groupData['is_tax_inclusive'] ?? false),
                    'is_active' => true,
                ]
            );

            $rates = $groupData['rates'] ?? [];
            foreach ($rates as $rateData) {
                TaxRate::updateOrCreate(
                    [
                        'tax_group_id' => $group->id,
                        'name' => $rateData['name'],
                    ],
                    [
                        'company_id' => $companyId,
                        'code' => $rateData['code'] ?? null,
                        'rate' => (float) $rateData['rate'],
                        'country_id' => $countryId,
                        'state_id' => null,
                        'is_compound' => (bool) ($rateData['is_compound'] ?? false),
                        'priority' => (int) ($rateData['priority'] ?? 1),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
