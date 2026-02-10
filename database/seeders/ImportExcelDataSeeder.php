<?php

namespace Database\Seeders;

use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Models\Company\Company;
use App\Models\Customer\Customer;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLocation;
use App\Models\Pricing\PriceChannel;
use App\Models\Pricing\VariantPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelDataSeeder extends Seeder
{
    /**
     * Import products and customers from Excel files.
     */
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            $this->command->error('No company found. Run VapeShopSetupSeeder first.');

            return;
        }

        // Clear existing data
        $this->command->info('Clearing existing products and customers...');
        $this->clearExistingData($company);

        // Import customers
        $this->command->info('Importing customers...');
        $this->importCustomers($company);

        // Import products
        $this->command->info('Importing products...');
        $this->importProducts($company);

        $this->command->info('Import completed!');
    }

    private function clearExistingData(Company $company): void
    {
        // Clear inventory balances first (due to FK)
        InventoryBalance::where('company_id', $company->id)->delete();

        // Clear prices (due to FK)
        VariantPrice::where('company_id', $company->id)->delete();

        // Clear product variants
        ProductVariant::where('company_id', $company->id)->delete();

        // Clear products
        Product::where('company_id', $company->id)->forceDelete();

        // Clear categories
        Category::where('company_id', $company->id)->delete();

        // Clear brands
        Brand::where('company_id', $company->id)->delete();

        // Clear customer addresses first
        $customerIds = Customer::where('company_id', $company->id)->withTrashed()->pluck('id');
        Address::whereIn('addressable_id', $customerIds)
            ->where('addressable_type', Customer::class)
            ->delete();

        // Clear customers (force delete to bypass soft delete)
        Customer::where('company_id', $company->id)->withTrashed()->forceDelete();

        $this->command->info('Existing data cleared.');
    }

    private function importCustomers(Company $company): void
    {
        $filePath = base_path('public_html/customers.xlsx');

        if (! file_exists($filePath)) {
            $this->command->warn('customers.xlsx not found in public_html folder.');

            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        // Skip header row
        $headers = array_shift($rows);

        $uk = Country::where('iso_code_2', 'GB')->first();
        $defaultCountry = $uk ?? Country::first();

        $count = 0;
        foreach ($rows as $row) {
            if (empty($row[3])) {
                continue;
            } // Skip empty rows

            $type = strtoupper(trim($row[2] ?? 'B2C'));
            $isCompany = $type === 'B2B';

            // Generate unique customer code
            $count++;
            $customerCode = ! empty($row[1]) ? trim($row[1]) : 'CUST'.str_pad($count, 4, '0', STR_PAD_LEFT);

            // Make sure code is unique by appending count if needed
            $existingCode = Customer::where('company_id', $company->id)->where('customer_code', $customerCode)->exists();
            if ($existingCode) {
                $customerCode = $customerCode.'-'.$count;
            }

            $customer = Customer::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'customer_code' => $customerCode,
                'type' => $isCompany ? 'company' : 'individual',
                'display_name' => $row[3], // Name
                'legal_name' => $row[4] ?? null, // Legal Name
                'email' => $row[5] ?? null,
                'phone' => $row[6] ?? null,
                'tax_number' => $row[7] ?? null,
                'percentage_discount' => floatval($row[14] ?? 0),
                'is_active' => true,
            ]);

            // Create address for customer
            if ($row[8] || $row[10] || $row[13]) {
                Address::create([
                    'addressable_type' => Customer::class,
                    'addressable_id' => $customer->id,
                    'company_id' => $company->id,
                    'type' => 'primary',
                    'address_line_1' => $row[8] ?? null,
                    'address_line_2' => $row[9] ?? null,
                    'city' => $row[10] ?? null,
                    'country_id' => $defaultCountry?->id,
                    'zip_code' => $row[13] ?? null,
                ]);
            }

            $this->command->info("Created customer: {$customer->display_name}");
        }

        $this->command->info("Imported {$count} customers.");
    }

    private function importProducts(Company $company): void
    {
        $filePath = base_path('public_html/products.xlsx');

        if (! file_exists($filePath)) {
            $this->command->warn('products.xlsx not found in public_html folder.');

            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        // Skip header row
        $headers = array_shift($rows);

        // Get or create default brand
        $defaultBrand = Brand::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'OXVA'],
            ['is_active' => true]
        );

        // Get price channels (global, not per company)
        $posChannel = PriceChannel::where('code', 'POS')->first();
        $webChannel = PriceChannel::where('code', 'WEBSITE')->first();
        $b2bChannel = PriceChannel::where('code', 'B2B')->first();

        // Get inventory location
        $location = InventoryLocation::where('company_id', $company->id)->first();

        // Group rows by product name
        $productGroups = [];
        foreach ($rows as $row) {
            if (empty($row[5]) || empty($row[8])) {
                continue;
            } // Skip rows without product name or SKU
            $productName = trim($row[5]);
            if (! isset($productGroups[$productName])) {
                $productGroups[$productName] = [
                    'category_name' => $row[1] ?? 'General',
                    'brand_name' => $row[3] ?? 'OXVA',
                    'status' => $row[6] ?? 'Active',
                    'variants' => [],
                ];
            }
            $productGroups[$productName]['variants'][] = $row;
        }

        $productCount = 0;
        $variantCount = 0;

        foreach ($productGroups as $productName => $data) {
            // Get or create category
            $category = Category::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['category_name']],
                ['slug' => Str::slug($data['category_name']), 'is_active' => true]
            );

            // Get or create brand
            $brandName = trim($data['brand_name']) ?: 'OXVA';
            $brand = Brand::firstOrCreate(
                ['company_id' => $company->id, 'name' => $brandName],
                ['is_active' => true]
            );

            // Create product
            $product = Product::create([
                'company_id' => $company->id,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $productName,
                'slug' => Str::slug($productName).'-'.Str::random(4),
                'is_active' => strtolower($data['status']) === 'active',
            ]);

            $productCount++;
            $this->command->info("Created product: {$productName}");

            // Create variants
            foreach ($data['variants'] as $row) {
                $sku = trim($row[8]);
                $variantAttrs = $row[9] ?? '';
                $costPrice = floatval($row[10] ?? 0);
                $posPrice = floatval($row[11] ?? 0);
                $webPrice = floatval($row[12] ?? 0);
                $b2bPrice = floatval($row[13] ?? 0);
                $storeStock = intval($row[14] ?? 0);
                $warehouseStock = intval($row[15] ?? 0);
                $totalStock = intval($row[16] ?? 0);

                $variant = ProductVariant::create([
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'barcode' => $sku,
                    'cost_price' => $costPrice,
                    'is_active' => true,
                ]);

                $variantCount++;

                // Get default currency
                $currency = \App\Models\Pricing\Currency::first();

                // Create prices for each channel
                if ($posChannel && $posPrice > 0) {
                    VariantPrice::create([
                        'company_id' => $company->id,
                        'product_variant_id' => $variant->id,
                        'price_channel_id' => $posChannel->id,
                        'currency_id' => $currency?->id,
                        'price' => $posPrice,
                        'is_active' => true,
                    ]);
                }

                if ($webChannel && $webPrice > 0) {
                    VariantPrice::create([
                        'company_id' => $company->id,
                        'product_variant_id' => $variant->id,
                        'price_channel_id' => $webChannel->id,
                        'currency_id' => $currency?->id,
                        'price' => $webPrice,
                        'is_active' => true,
                    ]);
                }

                if ($b2bChannel && $b2bPrice > 0) {
                    VariantPrice::create([
                        'company_id' => $company->id,
                        'product_variant_id' => $variant->id,
                        'price_channel_id' => $b2bChannel->id,
                        'currency_id' => $currency?->id,
                        'price' => $b2bPrice,
                        'is_active' => true,
                    ]);
                }

                // Create inventory balance
                if ($location && $totalStock > 0) {
                    InventoryBalance::create([
                        'uuid' => Str::uuid(),
                        'company_id' => $company->id,
                        'inventory_location_id' => $location->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $totalStock,
                        'last_movement_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info("Imported {$productCount} products with {$variantCount} variants.");
    }
}
