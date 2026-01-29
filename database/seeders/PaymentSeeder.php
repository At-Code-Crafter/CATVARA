<?php

namespace Database\Seeders;

use App\Models\Accounting\PaymentMethod;
use App\Models\Accounting\PaymentStatus;
use App\Models\Accounting\PaymentTerm;
use App\Models\Company\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Payment Statuses (Global)
        $this->seedPaymentStatuses();

        // Seed Payment Methods per Company
        $this->seedPaymentMethods();
    }

    /**
     * Seed payment statuses (global)
     */
    protected function seedPaymentStatuses(): void
    {
        $statuses = [
            ['code' => 'PENDING', 'name' => 'Pending', 'is_final' => false, 'is_active' => true],
            ['code' => 'CONFIRMED', 'name' => 'Confirmed', 'is_final' => false, 'is_active' => true],
            ['code' => 'FAILED', 'name' => 'Failed', 'is_final' => true, 'is_active' => true],
            ['code' => 'CANCELLED', 'name' => 'Cancelled', 'is_final' => true, 'is_active' => true],
            ['code' => 'REFUNDED', 'name' => 'Refunded', 'is_final' => true, 'is_active' => true],
        ];

        foreach ($statuses as $status) {
            PaymentStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }

        $this->command->info('✓ Payment statuses seeded.');
    }

    /**
     * Seed payment methods for all companies
     */
    protected function seedPaymentMethods(): void
    {
        $methods = [
            ['code' => 'CASH', 'name' => 'Cash', 'type' => 'CASH', 'requires_reference' => false],
            ['code' => 'CARD', 'name' => 'Credit/Debit Card', 'type' => 'CARD', 'requires_reference' => false],
            ['code' => 'BANK_TRANSFER', 'name' => 'Bank Transfer', 'type' => 'BANK', 'requires_reference' => true],
            ['code' => 'CHEQUE', 'name' => 'Cheque', 'type' => 'BANK', 'requires_reference' => true],
            ['code' => 'STRIPE', 'name' => 'Stripe', 'type' => 'GATEWAY', 'requires_reference' => false],
            ['code' => 'PAYPAL', 'name' => 'PayPal', 'type' => 'GATEWAY', 'requires_reference' => false],
            ['code' => 'STORE_CREDIT', 'name' => 'Store Credit', 'type' => 'CREDIT', 'requires_reference' => false],
        ];

        Company::all()->each(function (Company $company) use ($methods) {
            foreach ($methods as $m) {
                PaymentMethod::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $m['code'],
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $m['name'],
                        'type' => $m['type'],
                        'is_active' => ! in_array($m['code'], ['STRIPE', 'PAYPAL']), // Gateways disabled by default
                        'allow_refund' => ! in_array($m['code'], ['CHEQUE', 'STORE_CREDIT']),
                        'requires_reference' => $m['requires_reference'],
                    ]
                );
            }

            $this->command->info("✓ Payment methods seeded for: {$company->name}");
        });
    }

    public function seedPaymentTerms(): void
    {
        $terms = [
            [
                'code' => 'IMMEDIATE',
                'name' => 'Immediate',
                'due_days' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'NET_15',
                'name' => 'Net 15 Days',
                'due_days' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'NET_30',
                'name' => 'Net 30 Days',
                'due_days' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'NET_60',
                'name' => 'Net 60 Days',
                'due_days' => 60,
                'is_active' => true,
            ],
        ];

        foreach ($terms as $term) {
            PaymentTerm::firstOrCreate(
                ['code' => $term['code']],
                $term
            );
        }
    }
}
