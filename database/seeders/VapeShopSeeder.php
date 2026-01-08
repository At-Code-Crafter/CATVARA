<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Company\Company;
use App\Models\Pricing\Currency;
use App\Models\Pricing\PriceChannel;
use App\Models\Pricing\VariantPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VapeShopSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = database_path('seeders/data/products_full.json');

        if (! File::exists($jsonPath)) {
            $this->command->error("File not found: $jsonPath");

            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        if (! isset($data['products']) || ! is_array($data['products'])) {
            $this->command->error('Invalid JSON format: missing products[]');

            return;
        }

        // FIND COMPANY BY CODE
        $company = Company::where('code', 'UK-VAPE')->first();
        if (! $company) {
            $this->command->error("Company 'UK-VAPE' not found. Ensure DatabaseSeeder runs CompanySeeder.");

            return;
        }

        $currency = Currency::first();
        if (! $currency) {
            $this->command->error('No currency found. Please seed currencies first.');

            return;
        }

        $channel = PriceChannel::where('code', 'WEBSITE')->first() ?? PriceChannel::first();
        if (! $channel) {
            $this->command->error('No price channel found. Please seed price channels first.');

            return;
        }

        // ATTRIBUTE NORMALIZATION MAP
        $attrMap = [
            'choose color' => 'Color',
            'choose colour' => 'Color',
            'choose colors' => 'Color',
            'colour' => 'Color',
            'select color' => 'Color',
            'select colour' => 'Color',

            'choose flavor' => 'Flavor',
            'choose flavour' => 'Flavor',
            'flavour' => 'Flavor',
            'select flavor' => 'Flavor',

            'strength' => 'Nicotine',
            'nicotine strength' => 'Nicotine',
            'mg' => 'Nicotine',
        ];

        foreach ($data['products'] as $item) {
            try {
                $title = $item['title'] ?? '(no-title)';
                $this->command->info("Importing: {$title}");

                // 1) CATEGORY
                $categoryName = ! empty($item['product_type']) ? $item['product_type'] : 'Uncategorized';
                $categorySlug = Str::slug($categoryName);

                $category = Category::firstOrCreate(
                    ['company_id' => $company->id, 'slug' => $categorySlug],
                    ['name' => $categoryName, 'is_active' => true]
                );

                // 2) PRODUCT (use slug/handle as the stable unique key)
                $productSlug = $item['handle'] ?? Str::slug($title);

                $product = Product::firstOrNew([
                    'company_id' => $company->id,
                    'slug' => $productSlug,
                ]);

                if (! $product->exists) {
                    $product->uuid = (string) Str::uuid();
                }

                $product->category_id = $category->id;
                $product->name = $title;
                $product->description = $item['body_html'] ?? '';
                $product->is_active = true;
                $product->save();

                // Build a map of local variants by Shopify variant ID (for image linking)
                $variantIdToModel = [];

                // 3) VARIANTS + ATTRIBUTES + PRICES
                if (! empty($item['variants']) && is_array($item['variants'])) {
                    foreach ($item['variants'] as $variantData) {

                        // Requirement #3: Keep SKU as Shopify Variant ID if SKU is null (Shopify variant ID is unique)
                        $shopifyVariantId = $variantData['id'] ?? null;
                        if (! $shopifyVariantId) {
                            // If a variant has no id (unexpected), skip safely
                            continue;
                        }

                        $sku = ! empty($variantData['sku'])
                            ? (string) $variantData['sku']
                            : (string) $shopifyVariantId;

                        $variant = ProductVariant::firstOrNew([
                            'company_id' => $company->id,
                            'sku' => $sku,
                        ]);

                        if (! $variant->exists) {
                            $variant->uuid = (string) Str::uuid();
                        }

                        $variant->product_id = $product->id;
                        $variant->cost_price = ((float) ($variantData['price'] ?? 0)) * 0.5;
                        $variant->barcode = $variantData['barcode'] ?? null;
                        $variant->is_active = true;
                        $variant->save();

                        $variantIdToModel[(string) $shopifyVariantId] = $variant;

                        VariantPrice::updateOrCreate(
                            [
                                'company_id' => $company->id,
                                'product_variant_id' => $variant->id,
                                'price_channel_id' => $channel->id,
                                'currency_id' => $currency->id,
                            ],
                            [
                                'price' => $variantData['price'] ?? 0,
                                'valid_from' => now(),
                                'is_active' => true,
                            ]
                        );

                        // Attach option attributes/values to variant
                        $attachedAnyOption = false;

                        if (! empty($item['options']) && is_array($item['options'])) {
                            foreach ($item['options'] as $index => $optDefinition) {
                                $rawName = $optDefinition['name'] ?? null;
                                if (! $rawName) {
                                    continue;
                                }

                                // Normalize Attribute Name
                                $lowerName = strtolower(trim($rawName));
                                $normName = $attrMap[$lowerName] ?? $rawName;

                                $optionValue = $variantData['option'.($index + 1)] ?? null;

                                // Skip "Default Title" or empty
                                if (! $optionValue || $optionValue === 'Default Title') {
                                    continue;
                                }

                                $attachedAnyOption = true;

                                $attrCode = Str::slug($normName);

                                $attribute = Attribute::firstOrCreate(
                                    ['company_id' => $company->id, 'code' => $attrCode],
                                    ['name' => $normName]
                                );

                                // Ensure Category Linkage (safe non-duplicate)
                                $category->attributes()->syncWithoutDetaching([$attribute->id]);

                                $attrValue = AttributeValue::firstOrCreate(
                                    ['attribute_id' => $attribute->id, 'value' => (string) $optionValue],
                                    []
                                );

                                $variant->attributeValues()->syncWithoutDetaching([$attrValue->id]);
                            }
                        }

                        // Requirement #1:
                        // If no attribute exists for this variant but price exists,
                        // ensure Attribute "Standard" exists and category is linked. Value stays NULL (no AttributeValue attachment).
                        $price = (float) ($variantData['price'] ?? 0);
                        if (! $attachedAnyOption && $price > 0) {
                            $standardAttr = Attribute::firstOrCreate(
                                ['company_id' => $company->id, 'code' => 'standard'],
                                ['name' => 'Standard']
                            );

                            $category->attributes()->syncWithoutDetaching([$standardAttr->id]);
                            // Value: NULL => do NOT create AttributeValue, do NOT attach to variant
                        }

                        // Requirement #4 (also): variant has featured image
                        // if (! empty($variantData['featured_image']['src'])) {
                        //     $this->downloadAndAttachImage(
                        //         attachableType: ProductVariant::class,
                        //         attachableId: $variant->id,
                        //         companyId: $company->id,
                        //         imageUrl: $variantData['featured_image']['src'],
                        //         baseDir: "products/{$product->slug}/variants/{$shopifyVariantId}",
                        //         isPrimary: true
                        //     );
                        // }
                    }
                }

                // 4) PRODUCT + VARIANT IMAGES (from item['images'] list)
                // Requirement #2: Save image with whatever name coming from Shopify, so rerun checks exist
                // if (! empty($item['images']) && is_array($item['images'])) {
                //     foreach ($item['images'] as $idx => $img) {
                //         $src = $img['src'] ?? null;
                //         if (! $src) {
                //             continue;
                //         }

                //         $variantIds = $img['variant_ids'] ?? [];
                //         $variantIds = is_array($variantIds) ? $variantIds : [];

                //         // If variant_ids empty => product image
                //         if (count($variantIds) === 0) {
                //             $this->downloadAndAttachImage(
                //                 attachableType: Product::class,
                //                 attachableId: $product->id,
                //                 companyId: $company->id,
                //                 imageUrl: $src,
                //                 baseDir: "products/{$product->slug}",
                //                 isPrimary: ($idx === 0) // first image primary
                //             );

                //             continue;
                //         }

                //         // Else => attach to each referenced variant
                //         foreach ($variantIds as $vId) {
                //             $vId = (string) $vId;
                //             if (! isset($variantIdToModel[$vId])) {
                //                 // If variant wasn't created for some reason, skip
                //                 continue;
                //             }

                //             $variant = $variantIdToModel[$vId];

                //             $this->downloadAndAttachImage(
                //                 attachableType: ProductVariant::class,
                //                 attachableId: $variant->id,
                //                 companyId: $company->id,
                //                 imageUrl: $src,
                //                 baseDir: "products/{$product->slug}/variants/{$vId}",
                //                 isPrimary: true
                //             );
                //         }
                //     }
                // }

            } catch (\Exception $e) {
                $title = $item['title'] ?? '(no-title)';
                $this->command->error("Failed importing {$title}: ".$e->getMessage());
            }
        }
    }

    /**
     * Downloads an image if missing, stores with ORIGINAL filename from URL (Requirement #2),
     * and creates/updates Attachment row against Product or ProductVariant (Requirement #4).
     */
    protected function downloadAndAttachImage(
        string $attachableType,
        int $attachableId,
        int $companyId,
        string $imageUrl,
        string $baseDir,
        bool $isPrimary = false
    ): void {
        $fileName = $this->extractOriginalFileName($imageUrl);
        if (! $fileName) {
            return;
        }

        // Keep the incoming filename; store inside a product/variant folder to avoid cross-product collisions.
        $diskPath = trim($baseDir, '/').'/'.$fileName;

        // Download if not exists
        if (! Storage::disk('public')->exists($diskPath)) {
            try {
                $res = Http::timeout(60)
                    ->retry(2, 500)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->get($imageUrl);

                if ($res->successful()) {
                    Storage::disk('public')->put($diskPath, $res->body());
                }
            } catch (\Exception $imgErr) {
                // non-fatal
            }
        }

        if (! Storage::disk('public')->exists($diskPath)) {
            return;
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'jpg';
        $mime = $this->guessImageMime($ext);

        // Create or update attachment record (idempotent)
        // Use (attachable + path) as stable key.
        $attachment = Attachment::updateOrCreate(
            [
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
                'company_id' => $companyId,
                'disk' => 'public',
                'path' => $diskPath,
            ],
            [
                'file_name' => $fileName,
                'mime_type' => $mime,
                'is_primary' => (bool) $isPrimary,
            ]
        );

        // If setting as primary, optionally demote others (keeps only one primary per attachable)
        if ($isPrimary) {
            Attachment::where('company_id', $companyId)
                ->where('attachable_type', $attachableType)
                ->where('attachable_id', $attachableId)
                ->where('id', '!=', $attachment->id)
                ->update(['is_primary' => false]);
        }
    }

    protected function extractOriginalFileName(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! $path) {
            return null;
        }

        $base = basename($path);
        $base = urldecode($base);

        // Shopify names are usually safe; still normalize a bit
        $base = preg_replace('/\s+/', '-', $base);
        $base = preg_replace('/[^A-Za-z0-9\-\._]/', '', $base);

        return $base ?: null;
    }

    protected function guessImageMime(string $ext): string
    {
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/'.$ext,
        };
    }
}
