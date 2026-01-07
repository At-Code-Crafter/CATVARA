<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accounting\PaymentTerm;

class PaymentTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
