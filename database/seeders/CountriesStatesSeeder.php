<?php

namespace Database\Seeders;

use App\Models\Common\Country;
use App\Models\Common\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountriesStatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'United States',
                'iso_code_2' => 'US',
                'iso_code_3' => 'USA',
                'numeric_code' => '840',
                'phone_code' => '+1',
                'currency_code' => 'USD',
                'capital' => 'Washington D.C.',
                'region' => 'Americas',
                'subregion' => 'Northern America',
                'states' => [
                    ['name' => 'Alabama', 'code' => 'AL', 'type' => 'State'],
                    ['name' => 'Alaska', 'code' => 'AK', 'type' => 'State'],
                    ['name' => 'Arizona', 'code' => 'AZ', 'type' => 'State'],
                    ['name' => 'Arkansas', 'code' => 'AR', 'type' => 'State'],
                    ['name' => 'California', 'code' => 'CA', 'type' => 'State'],
                    ['name' => 'Colorado', 'code' => 'CO', 'type' => 'State'],
                    ['name' => 'Connecticut', 'code' => 'CT', 'type' => 'State'],
                    ['name' => 'Delaware', 'code' => 'DE', 'type' => 'State'],
                    ['name' => 'Florida', 'code' => 'FL', 'type' => 'State'],
                    ['name' => 'Georgia', 'code' => 'GA', 'type' => 'State'],
                    ['name' => 'Hawaii', 'code' => 'HI', 'type' => 'State'],
                    ['name' => 'Idaho', 'code' => 'ID', 'type' => 'State'],
                    ['name' => 'Illinois', 'code' => 'IL', 'type' => 'State'],
                    ['name' => 'Indiana', 'code' => 'IN', 'type' => 'State'],
                    ['name' => 'Iowa', 'code' => 'IA', 'type' => 'State'],
                    ['name' => 'Kansas', 'code' => 'KS', 'type' => 'State'],
                    ['name' => 'Kentucky', 'code' => 'KY', 'type' => 'State'],
                    ['name' => 'Louisiana', 'code' => 'LA', 'type' => 'State'],
                    ['name' => 'Maine', 'code' => 'ME', 'type' => 'State'],
                    ['name' => 'Maryland', 'code' => 'MD', 'type' => 'State'],
                    ['name' => 'Massachusetts', 'code' => 'MA', 'type' => 'State'],
                    ['name' => 'Michigan', 'code' => 'MI', 'type' => 'State'],
                    ['name' => 'Minnesota', 'code' => 'MN', 'type' => 'State'],
                    ['name' => 'Mississippi', 'code' => 'MS', 'type' => 'State'],
                    ['name' => 'Missouri', 'code' => 'MO', 'type' => 'State'],
                    ['name' => 'Montana', 'code' => 'MT', 'type' => 'State'],
                    ['name' => 'Nebraska', 'code' => 'NE', 'type' => 'State'],
                    ['name' => 'Nevada', 'code' => 'NV', 'type' => 'State'],
                    ['name' => 'New Hampshire', 'code' => 'NH', 'type' => 'State'],
                    ['name' => 'New Jersey', 'code' => 'NJ', 'type' => 'State'],
                    ['name' => 'New Mexico', 'code' => 'NM', 'type' => 'State'],
                    ['name' => 'New York', 'code' => 'NY', 'type' => 'State'],
                    ['name' => 'North Carolina', 'code' => 'NC', 'type' => 'State'],
                    ['name' => 'North Dakota', 'code' => 'ND', 'type' => 'State'],
                    ['name' => 'Ohio', 'code' => 'OH', 'type' => 'State'],
                    ['name' => 'Oklahoma', 'code' => 'OK', 'type' => 'State'],
                    ['name' => 'Oregon', 'code' => 'OR', 'type' => 'State'],
                    ['name' => 'Pennsylvania', 'code' => 'PA', 'type' => 'State'],
                    ['name' => 'Rhode Island', 'code' => 'RI', 'type' => 'State'],
                    ['name' => 'South Carolina', 'code' => 'SC', 'type' => 'State'],
                    ['name' => 'South Dakota', 'code' => 'SD', 'type' => 'State'],
                    ['name' => 'Tennessee', 'code' => 'TN', 'type' => 'State'],
                    ['name' => 'Texas', 'code' => 'TX', 'type' => 'State'],
                    ['name' => 'Utah', 'code' => 'UT', 'type' => 'State'],
                    ['name' => 'Vermont', 'code' => 'VT', 'type' => 'State'],
                    ['name' => 'Virginia', 'code' => 'VA', 'type' => 'State'],
                    ['name' => 'Washington', 'code' => 'WA', 'type' => 'State'],
                    ['name' => 'West Virginia', 'code' => 'WV', 'type' => 'State'],
                    ['name' => 'Wisconsin', 'code' => 'WI', 'type' => 'State'],
                    ['name' => 'Wyoming', 'code' => 'WY', 'type' => 'State'],
                ],
            ],
            [
                'name' => 'United Kingdom',
                'iso_code_2' => 'GB',
                'iso_code_3' => 'GBR',
                'numeric_code' => '826',
                'phone_code' => '+44',
                'currency_code' => 'GBP',
                'capital' => 'London',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
                'states' => [
                    ['name' => 'England', 'code' => 'ENG', 'type' => 'Country'],
                    ['name' => 'Scotland', 'code' => 'SCT', 'type' => 'Country'],
                    ['name' => 'Wales', 'code' => 'WLS', 'type' => 'Country'],
                    ['name' => 'Northern Ireland', 'code' => 'NIR', 'type' => 'Province'],
                ],
            ],
            [
                'name' => 'Canada',
                'iso_code_2' => 'CA',
                'iso_code_3' => 'CAN',
                'numeric_code' => '124',
                'phone_code' => '+1',
                'currency_code' => 'CAD',
                'capital' => 'Ottawa',
                'region' => 'Americas',
                'subregion' => 'Northern America',
                'states' => [
                    ['name' => 'Alberta', 'code' => 'AB', 'type' => 'Province'],
                    ['name' => 'British Columbia', 'code' => 'BC', 'type' => 'Province'],
                    ['name' => 'Manitoba', 'code' => 'MB', 'type' => 'Province'],
                    ['name' => 'New Brunswick', 'code' => 'NB', 'type' => 'Province'],
                    ['name' => 'Newfoundland and Labrador', 'code' => 'NL', 'type' => 'Province'],
                    ['name' => 'Nova Scotia', 'code' => 'NS', 'type' => 'Province'],
                    ['name' => 'Ontario', 'code' => 'ON', 'type' => 'Province'],
                    ['name' => 'Prince Edward Island', 'code' => 'PE', 'type' => 'Province'],
                    ['name' => 'Quebec', 'code' => 'QC', 'type' => 'Province'],
                    ['name' => 'Saskatchewan', 'code' => 'SK', 'type' => 'Province'],
                    ['name' => 'Northwest Territories', 'code' => 'NT', 'type' => 'Territory'],
                    ['name' => 'Nunavut', 'code' => 'NU', 'type' => 'Territory'],
                    ['name' => 'Yukon', 'code' => 'YT', 'type' => 'Territory'],
                ],
            ],
            [
                'name' => 'Australia',
                'iso_code_2' => 'AU',
                'iso_code_3' => 'AUS',
                'numeric_code' => '036',
                'phone_code' => '+61',
                'currency_code' => 'AUD',
                'capital' => 'Canberra',
                'region' => 'Oceania',
                'subregion' => 'Australia and New Zealand',
                'states' => [
                    ['name' => 'Australian Capital Territory', 'code' => 'ACT', 'type' => 'Territory'],
                    ['name' => 'New South Wales', 'code' => 'NSW', 'type' => 'State'],
                    ['name' => 'Northern Territory', 'code' => 'NT', 'type' => 'Territory'],
                    ['name' => 'Queensland', 'code' => 'QLD', 'type' => 'State'],
                    ['name' => 'South Australia', 'code' => 'SA', 'type' => 'State'],
                    ['name' => 'Tasmania', 'code' => 'TAS', 'type' => 'State'],
                    ['name' => 'Victoria', 'code' => 'VIC', 'type' => 'State'],
                    ['name' => 'Western Australia', 'code' => 'WA', 'type' => 'State'],
                ],
            ],
            [
                'name' => 'India',
                'iso_code_2' => 'IN',
                'iso_code_3' => 'IND',
                'numeric_code' => '356',
                'phone_code' => '+91',
                'currency_code' => 'INR',
                'capital' => 'New Delhi',
                'region' => 'Asia',
                'subregion' => 'Southern Asia',
                'states' => [
                    ['name' => 'Andhra Pradesh', 'code' => 'AP', 'type' => 'State'],
                    ['name' => 'Bihar', 'code' => 'BR', 'type' => 'State'],
                    ['name' => 'Delhi', 'code' => 'DL', 'type' => 'Territory'],
                    ['name' => 'Gujarat', 'code' => 'GJ', 'type' => 'State'],
                    ['name' => 'Karnataka', 'code' => 'KA', 'type' => 'State'],
                    ['name' => 'Kerala', 'code' => 'KL', 'type' => 'State'],
                    ['name' => 'Madhya Pradesh', 'code' => 'MP', 'type' => 'State'],
                    ['name' => 'Maharashtra', 'code' => 'MH', 'type' => 'State'],
                    ['name' => 'Punjab', 'code' => 'PB', 'type' => 'State'],
                    ['name' => 'Rajasthan', 'code' => 'RJ', 'type' => 'State'],
                    ['name' => 'Tamil Nadu', 'code' => 'TN', 'type' => 'State'],
                    ['name' => 'Telangana', 'code' => 'TG', 'type' => 'State'],
                    ['name' => 'Uttar Pradesh', 'code' => 'UP', 'type' => 'State'],
                    ['name' => 'West Bengal', 'code' => 'WB', 'type' => 'State'],
                ],
            ],
            [
                'name' => 'Germany',
                'iso_code_2' => 'DE',
                'iso_code_3' => 'DEU',
                'numeric_code' => '276',
                'phone_code' => '+49',
                'currency_code' => 'EUR',
                'capital' => 'Berlin',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'states' => [
                    ['name' => 'Baden-Württemberg', 'code' => 'BW', 'type' => 'State'],
                    ['name' => 'Bavaria', 'code' => 'BY', 'type' => 'State'],
                    ['name' => 'Berlin', 'code' => 'BE', 'type' => 'State'],
                    ['name' => 'Brandenburg', 'code' => 'BB', 'type' => 'State'],
                    ['name' => 'Hamburg', 'code' => 'HH', 'type' => 'State'],
                    ['name' => 'Hesse', 'code' => 'HE', 'type' => 'State'],
                    ['name' => 'North Rhine-Westphalia', 'code' => 'NW', 'type' => 'State'],
                    ['name' => 'Saxony', 'code' => 'SN', 'type' => 'State'],
                ],
            ],
            [
                'name' => 'France',
                'iso_code_2' => 'FR',
                'iso_code_3' => 'FRA',
                'numeric_code' => '250',
                'phone_code' => '+33',
                'currency_code' => 'EUR',
                'capital' => 'Paris',
                'region' => 'Europe',
                'subregion' => 'Western Europe',
                'states' => [
                    ['name' => 'Île-de-France', 'code' => 'IDF', 'type' => 'Region'],
                    ['name' => 'Provence-Alpes-Côte d\'Azur', 'code' => 'PAC', 'type' => 'Region'],
                    ['name' => 'Auvergne-Rhône-Alpes', 'code' => 'ARA', 'type' => 'Region'],
                    ['name' => 'Nouvelle-Aquitaine', 'code' => 'NAQ', 'type' => 'Region'],
                    ['name' => 'Occitanie', 'code' => 'OCC', 'type' => 'Region'],
                ],
            ],
            [
                'name' => 'United Arab Emirates',
                'iso_code_2' => 'AE',
                'iso_code_3' => 'ARE',
                'numeric_code' => '784',
                'phone_code' => '+971',
                'currency_code' => 'AED',
                'capital' => 'Abu Dhabi',
                'region' => 'Asia',
                'subregion' => 'Western Asia',
                'states' => [
                    ['name' => 'Abu Dhabi', 'code' => 'AZ', 'type' => 'Emirate'],
                    ['name' => 'Dubai', 'code' => 'DU', 'type' => 'Emirate'],
                    ['name' => 'Sharjah', 'code' => 'SH', 'type' => 'Emirate'],
                    ['name' => 'Ajman', 'code' => 'AJ', 'type' => 'Emirate'],
                    ['name' => 'Ras Al Khaimah', 'code' => 'RK', 'type' => 'Emirate'],
                    ['name' => 'Fujairah', 'code' => 'FU', 'type' => 'Emirate'],
                    ['name' => 'Umm Al Quwain', 'code' => 'UQ', 'type' => 'Emirate'],
                ],
            ],
            [
                'name' => 'Pakistan',
                'iso_code_2' => 'PK',
                'iso_code_3' => 'PAK',
                'numeric_code' => '586',
                'phone_code' => '+92',
                'currency_code' => 'PKR',
                'capital' => 'Islamabad',
                'region' => 'Asia',
                'subregion' => 'Southern Asia',
                'states' => [
                    ['name' => 'Punjab', 'code' => 'PB', 'type' => 'Province'],
                    ['name' => 'Sindh', 'code' => 'SD', 'type' => 'Province'],
                    ['name' => 'Khyber Pakhtunkhwa', 'code' => 'KP', 'type' => 'Province'],
                    ['name' => 'Balochistan', 'code' => 'BA', 'type' => 'Province'],
                    ['name' => 'Islamabad Capital Territory', 'code' => 'IS', 'type' => 'Territory'],
                    ['name' => 'Gilgit-Baltistan', 'code' => 'GB', 'type' => 'Territory'],
                    ['name' => 'Azad Kashmir', 'code' => 'JK', 'type' => 'Territory'],
                ],
            ],
        ];

        foreach ($countries as $countryData) {
            $states = $countryData['states'] ?? [];
            unset($countryData['states']);

            $country = Country::updateOrCreate(
                ['iso_code_2' => $countryData['iso_code_2']],
                array_merge($countryData, [
                    'uuid' => (string) Str::uuid(),
                    'is_active' => true,
                ])
            );

            foreach ($states as $stateData) {
                State::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'code' => $stateData['code'],
                    ],
                    array_merge($stateData, [
                        'uuid' => (string) Str::uuid(),
                        'country_id' => $country->id,
                        'is_active' => true,
                    ])
                );
            }
        }

        $this->command->info('Countries and States seeded successfully!');
    }
}
