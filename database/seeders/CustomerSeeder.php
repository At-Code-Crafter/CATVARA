<?php

namespace Database\Seeders;

use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            $this->command->error('No company found. Please create a company first.');

            return;
        }

        // Get UAE country and Dubai state for addresses
        $uae = Country::where('iso_code_2', 'AE')->first();
        $dubai = $uae ? State::where('country_id', $uae->id)->first() : null;

        $customers = [
            [
                'type' => 'COMPANY',
                'display_name' => 'Al Maktoum Trading LLC',
                'legal_name' => 'Al Maktoum Trading Limited Liability Company',
                'email' => 'info@almaktoumtrading.ae',
                'phone' => '+971 4 123 4567',
                'tax_number' => 'TRN100123456789',
                'customer_code' => 'CUST-001',
                'notes' => 'Premium wholesale client',
                'percentage_discount' => 5.00,
                'address' => [
                    'address_line_1' => 'Office 501, Business Bay Tower',
                    'address_line_2' => 'Business Bay',
                    'city' => 'Dubai',
                    'zip_code' => '12345',
                ],
            ],
            [
                'type' => 'COMPANY',
                'display_name' => 'Emirates Vape Distribution',
                'legal_name' => 'Emirates Vape Distribution FZ-LLC',
                'email' => 'orders@emiratesvape.com',
                'phone' => '+971 4 987 6543',
                'tax_number' => 'TRN100987654321',
                'customer_code' => 'CUST-002',
                'notes' => 'Main distributor for northern emirates',
                'percentage_discount' => 10.00,
                'address' => [
                    'address_line_1' => 'Warehouse 23, Jebel Ali Free Zone',
                    'address_line_2' => null,
                    'city' => 'Dubai',
                    'zip_code' => '17000',
                ],
            ],
            [
                'type' => 'INDIVIDUAL',
                'display_name' => 'Ahmed Al Rashid',
                'legal_name' => null,
                'email' => 'ahmed.rashid@gmail.com',
                'phone' => '+971 50 123 4567',
                'tax_number' => null,
                'customer_code' => 'CUST-003',
                'notes' => 'Regular retail customer',
                'percentage_discount' => 0,
                'address' => [
                    'address_line_1' => 'Villa 45, Al Barsha 1',
                    'address_line_2' => 'Near Mall of Emirates',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'INDIVIDUAL',
                'display_name' => 'Sarah Johnson',
                'legal_name' => null,
                'email' => 'sarah.j@outlook.com',
                'phone' => '+971 55 987 6543',
                'tax_number' => null,
                'customer_code' => 'CUST-004',
                'notes' => 'Expat customer - prefers English communication',
                'percentage_discount' => 0,
                'address' => [
                    'address_line_1' => 'Apt 1203, Marina Heights',
                    'address_line_2' => 'Dubai Marina',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'COMPANY',
                'display_name' => 'Smoke & Mirrors FZCO',
                'legal_name' => 'Smoke & Mirrors Free Zone Company',
                'email' => 'procurement@smokeandmirrors.ae',
                'phone' => '+971 4 555 1234',
                'tax_number' => 'TRN100555123456',
                'customer_code' => 'CUST-005',
                'notes' => 'Chain of retail stores - 5 locations',
                'percentage_discount' => 15.00,
                'address' => [
                    'address_line_1' => 'Shop G-15, City Centre Deira',
                    'address_line_2' => 'Deira',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'COMPANY',
                'display_name' => 'Gulf Vapor Supplies',
                'legal_name' => 'Gulf Vapor Supplies LLC',
                'email' => 'info@gulfvapor.com',
                'phone' => '+971 6 234 5678',
                'tax_number' => 'TRN100234567890',
                'customer_code' => 'CUST-006',
                'notes' => 'Sharjah-based wholesaler',
                'percentage_discount' => 8.00,
                'address' => [
                    'address_line_1' => 'Industrial Area 12, Plot 45',
                    'address_line_2' => null,
                    'city' => 'Sharjah',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'INDIVIDUAL',
                'display_name' => 'Mohammed Al Hamadi',
                'legal_name' => null,
                'email' => 'mhamadi@yahoo.com',
                'phone' => '+971 50 777 8888',
                'tax_number' => null,
                'customer_code' => 'CUST-007',
                'notes' => 'VIP customer - priority service',
                'percentage_discount' => 5.00,
                'address' => [
                    'address_line_1' => 'Villa 12, Palm Jumeirah',
                    'address_line_2' => 'Frond C',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'COMPANY',
                'display_name' => 'Cloud Nine Vape Shop',
                'legal_name' => 'Cloud Nine Trading LLC',
                'email' => 'orders@cloudnine.ae',
                'phone' => '+971 4 333 4444',
                'tax_number' => 'TRN100333444555',
                'customer_code' => 'CUST-008',
                'notes' => 'Single store in JBR',
                'percentage_discount' => 3.00,
                'address' => [
                    'address_line_1' => 'Shop 23, The Walk JBR',
                    'address_line_2' => 'Jumeirah Beach Residence',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'INDIVIDUAL',
                'display_name' => 'Fatima Al Zaabi',
                'legal_name' => null,
                'email' => 'fatima.zaabi@gmail.com',
                'phone' => '+971 56 111 2222',
                'tax_number' => null,
                'customer_code' => 'CUST-009',
                'notes' => 'New customer',
                'percentage_discount' => 0,
                'address' => [
                    'address_line_1' => 'Apt 804, Burj Views Tower A',
                    'address_line_2' => 'Downtown Dubai',
                    'city' => 'Dubai',
                    'zip_code' => '00000',
                ],
            ],
            [
                'type' => 'COMPANY',
                'display_name' => 'Abu Dhabi Vape Center',
                'legal_name' => 'Abu Dhabi Vape Center LLC',
                'email' => 'sales@advapecenter.ae',
                'phone' => '+971 2 666 7777',
                'tax_number' => 'TRN100666777888',
                'customer_code' => 'CUST-010',
                'notes' => 'Abu Dhabi distributor - COD only',
                'percentage_discount' => 12.00,
                'address' => [
                    'address_line_1' => 'Warehouse 8, Mussafah Industrial',
                    'address_line_2' => 'M-17',
                    'city' => 'Abu Dhabi',
                    'zip_code' => '00000',
                ],
            ],
        ];

        foreach ($customers as $data) {
            $addressData = $data['address'];
            unset($data['address']);

            // Check if customer already exists
            $existing = Customer::where('company_id', $company->id)
                ->where('customer_code', $data['customer_code'])
                ->first();

            if ($existing) {
                $this->command->info("Customer {$data['display_name']} already exists, skipping...");

                continue;
            }

            $customer = Customer::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'type' => $data['type'],
                'display_name' => $data['display_name'],
                'legal_name' => $data['legal_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'tax_number' => $data['tax_number'],
                'customer_code' => $data['customer_code'],
                'notes' => $data['notes'],
                'percentage_discount' => $data['percentage_discount'],
                'is_active' => true,
            ]);

            // Create address
            $customer->address()->create([
                'company_id' => $company->id,
                'address_line_1' => $addressData['address_line_1'],
                'address_line_2' => $addressData['address_line_2'],
                'city' => $addressData['city'],
                'zip_code' => $addressData['zip_code'],
                'state_id' => $dubai?->id,
                'country_id' => $uae?->id,
            ]);

            $this->command->info("Created customer: {$data['display_name']}");
        }

        $this->command->info('Customer seeding completed!');
    }
}
