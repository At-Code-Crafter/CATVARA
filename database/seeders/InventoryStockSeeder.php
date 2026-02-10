<?php

namespace Database\Seeders;

use App\Models\Catalog\ProductVariant;
use App\Models\Company\Company;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryStockSeeder extends Seeder
{
    /**
     * Add stock to all product variants.
     */
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            $this->command->error('No company found.');

            return;
        }

        // Get first inventory location
        $location = InventoryLocation::where('company_id', $company->id)->first();

        if (! $location) {
            $this->command->error('No inventory location found. Creating one...');

            $location = InventoryLocation::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'type' => 'warehouse',
                'is_active' => true,
            ]);
        }

        $variants = ProductVariant::where('company_id', $company->id)->get();

        if ($variants->isEmpty()) {
            $this->command->warn('No product variants found.');

            return;
        }

        $this->command->info("Adding stock for {$variants->count()} variants...");

        foreach ($variants as $variant) {
            // Random stock between 10 and 100
            $quantity = rand(10, 100);

            // Check if already has stock
            $existing = InventoryBalance::where('product_variant_id', $variant->id)
                ->where('inventory_location_id', $location->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'quantity' => $quantity,
                    'last_movement_at' => now(),
                ]);
                $this->command->info("Updated stock for {$variant->sku}: {$quantity}");
            } else {
                InventoryBalance::create([
                    'uuid' => Str::uuid(),
                    'company_id' => $company->id,
                    'inventory_location_id' => $location->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'last_movement_at' => now(),
                ]);
                $this->command->info("Added stock for {$variant->sku}: {$quantity}");
            }
        }

        $this->command->info('Inventory stock seeding completed!');
    }
}
