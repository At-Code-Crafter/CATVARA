<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use App\Models\Catalog\Brand;
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
    /**
     * GLOBAL FLAG
     * - true  => save all products (even if variants have no pricing)
     * - false => skip products that have NO variants with valid price (>0)
     */
    protected bool $IMPORT_ALL_PRODUCTS = false;

    public function run()
    {
        $jsonPath = database_path('seeders/data/products_full.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("File not found: $jsonPath");
            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        if (!isset($data['products']) || !is_array($data['products'])) {
            $this->command->error('Invalid JSON format: missing products[]');
            return;
        }

        $company = Company::where('code', 'UK-VAPE')->first();
        if (!$company) {
            $this->command->error("Company 'UK-VAPE' not found. Ensure DatabaseSeeder runs CompanySeeder.");
            return;
        }

        $currency = Currency::first();
        if (!$currency) {
            $this->command->error('No currency found. Please seed currencies first.');
            return;
        }

        $channel = PriceChannel::where('code', 'WEBSITE')->first() ?? PriceChannel::first();
        if (!$channel) {
            $this->command->error('No price channel found. Please seed price channels first.');
            return;
        }

        // Ensure directories exist (public disk)
        Storage::disk('public')->makeDirectory('products');
        Storage::disk('public')->makeDirectory('variants');

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

                /**
                 * STRICT MODE:
                 * If IMPORT_ALL_PRODUCTS=false, then we only import a product if
                 * at least one variant has price > 0 in the source JSON.
                 */
                if (!$this->IMPORT_ALL_PRODUCTS && !$this->productHasAnyPricedVariant($item)) {
                    $this->command->warn("Skipping (no variant pricing): {$title}");
                    continue;
                }

                // 1) CATEGORY
                $categoryName = !empty($item['product_type']) ? $item['product_type'] : 'Uncategorized';
                $categorySlug = Str::slug($categoryName);

                $category = Category::firstOrCreate(
                    ['company_id' => $company->id, 'slug' => $categorySlug],
                    ['name' => $categoryName, 'is_active' => true]
                );

                // 2) PRODUCT (stable key: handle/slug)
                $productSlug = $item['handle'] ?? Str::slug($title);

                $product = Product::firstOrNew([
                    'company_id' => $company->id,
                    'slug' => $productSlug,
                ]);

                if (!$product->exists) {
                    $product->uuid = (string) Str::uuid();
                }

                // 2.1) BRAND
                $brandName = !empty($item['vendor']) ? $item['vendor'] : null;
                if ($brandName) {
                    $brandSlug = Str::slug($brandName);
                    $brand = Brand::firstOrCreate(
                        ['company_id' => $company->id, 'slug' => $brandSlug],
                        ['uuid' => (string) Str::uuid(), 'name' => $brandName, 'is_active' => true]
                    );
                    $product->brand_id = $brand->id;
                }

                $product->category_id = $category->id;
                $product->name = $title;
                $product->description = $item['body_html'] ?? '';
                $product->is_active = true;
                $product->save();

                // We'll set product->image from FIRST found image (only if empty)
                $productImageSet = !empty($product->image);

                // 3) VARIANTS + ATTRIBUTES + PRICES + IMAGES
                if (!empty($item['variants']) && is_array($item['variants'])) {
                    foreach ($item['variants'] as $variantData) {

                        $shopifyVariantId = $variantData['id'] ?? null;
                        if (!$shopifyVariantId) {
                            continue;
                        }

                        $sku = !empty($variantData['sku'])
                            ? (string) $variantData['sku']
                            : (string) $shopifyVariantId;

                        /**
                         * STRICT MODE (variant-level):
                         * If IMPORT_ALL_PRODUCTS=false, do NOT save variants without valid price.
                         */
                        $price = (float) ($variantData['price'] ?? 0);
                        if (!$this->IMPORT_ALL_PRODUCTS && $price <= 0) {
                            continue;
                        }

                        $variant = ProductVariant::firstOrNew([
                            'company_id' => $company->id,
                            'sku' => $sku,
                        ]);

                        if (!$variant->exists) {
                            $variant->uuid = (string) Str::uuid();
                        }

                        $variant->product_id = $product->id;
                        $variant->cost_price = $price * 0.5;
                        $variant->barcode = $variantData['barcode'] ?? null;
                        $variant->is_active = true;
                        $variant->save();

                        VariantPrice::updateOrCreate(
                            [
                                'company_id' => $company->id,
                                'product_variant_id' => $variant->id,
                                'price_channel_id' => $channel->id,
                                'currency_id' => $currency->id,
                            ],
                            [
                                'price' => $price,
                                'valid_from' => now(),
                                'is_active' => true,
                            ]
                        );

                        // Attributes/values
                        $attachedAnyOption = false;

                        if (!empty($item['options']) && is_array($item['options'])) {
                            foreach ($item['options'] as $index => $optDefinition) {
                                $rawName = $optDefinition['name'] ?? null;
                                if (!$rawName) {
                                    continue;
                                }

                                $lowerName = strtolower(trim($rawName));
                                $normName = $attrMap[$lowerName] ?? $rawName;

                                $optionValue = $variantData['option' . ($index + 1)] ?? null;
                                if (!$optionValue || $optionValue === 'Default Title') {
                                    continue;
                                }

                                $attachedAnyOption = true;

                                $attrCode = Str::slug($normName);

                                $attribute = Attribute::firstOrCreate(
                                    ['company_id' => $company->id, 'code' => $attrCode],
                                    ['name' => $normName]
                                );

                                $category->attributes()->syncWithoutDetaching([$attribute->id]);

                                $attrValue = AttributeValue::firstOrCreate(
                                    ['attribute_id' => $attribute->id, 'value' => (string) $optionValue],
                                    []
                                );

                                $variant->attributeValues()->syncWithoutDetaching([$attrValue->id]);
                            }
                        }

                        // If no options attached, but variant has price > 0, ensure category has Standard attr
                        if (!$attachedAnyOption && $price > 0) {
                            $standardAttr = Attribute::firstOrCreate(
                                ['company_id' => $company->id, 'code' => 'standard'],
                                ['name' => 'Standard']
                            );

                            $category->attributes()->syncWithoutDetaching([$standardAttr->id]);
                        }

                        /**
                         * IMAGE RESOLUTION (FIX):
                         * Many variants have featured_image=null in JSON.
                         * So we resolve via fallbacks:
                         * 1) variant.featured_image.src
                         * 2) product.images[] where variant_ids contains variant id
                         * 3) product.image.src
                         * 4) product.images[0].src
                         */
                        $variantImg = $this->resolveVariantImageUrl($item, $variantData);

                        if ($variantImg) {
                            // Save variant attachment (no re-download if exists)
                            $this->downloadAndAttachImage(
                                attachableType: ProductVariant::class,
                                attachableId: $variant->id,
                                companyId: $company->id,
                                imageUrl: $variantImg,
                                baseDir: 'variants/',
                                isPrimary: true
                            );

                            // Set product->image from FIRST found image (only once)
                            if (!$productImageSet) {
                                $productImageSet = $this->ensureProductMainImageFromUrl(
                                    product: $product,
                                    imageUrl: $variantImg
                                );
                            }
                        } else {
                            // Helpful debugging (non-fatal)
                            $this->command->warn("No image resolved for variant {$shopifyVariantId} (product: {$title})");
                        }
                    }
                }

                /**
                 * STRICT MODE CLEANUP:
                 * If strict mode and we ended up saving zero variants (because all had price <=0),
                 * remove the product to keep DB clean.
                 */
                if (!$this->IMPORT_ALL_PRODUCTS) {
                    $savedVariantsCount = ProductVariant::where('company_id', $company->id)
                        ->where('product_id', $product->id)
                        ->count();

                    if ($savedVariantsCount === 0) {
                        $this->command->warn("Deleting product (no priced variants saved): {$title}");
                        $product->delete();
                    }
                }

            } catch (\Exception $e) {
                $title = $item['title'] ?? '(no-title)';
                $this->command->error("Failed importing {$title}: " . $e->getMessage());
            }
        }
    }

    /**
     * STRICT MODE: checks if item has at least one variant with price > 0
     */
    protected function productHasAnyPricedVariant(array $item): bool
    {
        if (empty($item['variants']) || !is_array($item['variants'])) {
            return false;
        }

        foreach ($item['variants'] as $variantData) {
            $price = (float) ($variantData['price'] ?? 0);
            if ($price > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve a variant image URL using Shopify JSON structure fallbacks:
     * 1) variants[].featured_image.src
     * 2) images[] where variant_ids contains variant id
     * 3) image.src (product main image, if present)
     * 4) images[0].src
     */
    protected function resolveVariantImageUrl(array $productItem, array $variantData): ?string
    {
        // 1) variant.featured_image.src
        $featured = $variantData['featured_image'] ?? null;
        if (is_array($featured) && !empty($featured['src'])) {
            return (string) $featured['src'];
        }

        $variantId = $variantData['id'] ?? null;

        // 2) product.images[] match by variant_ids
        if ($variantId && !empty($productItem['images']) && is_array($productItem['images'])) {
            foreach ($productItem['images'] as $img) {
                if (empty($img['src'])) {
                    continue;
                }

                $variantIds = $img['variant_ids'] ?? [];
                if (is_array($variantIds) && in_array($variantId, $variantIds, true)) {
                    return (string) $img['src'];
                }
            }
        }

        // 3) product.image.src (some Shopify exports provide a main "image" object)
        if (!empty($productItem['image']) && is_array($productItem['image']) && !empty($productItem['image']['src'])) {
            return (string) $productItem['image']['src'];
        }

        // 4) first product image
        if (!empty($productItem['images'][0]['src'])) {
            return (string) $productItem['images'][0]['src'];
        }

        return null;
    }

    /**
     * Sets $product->image using an image URL.
     * - stores file in: products/{original_filename_without_query}
     * - does not download if already exists
     * - keeps original Shopify filename (without ?v=...)
     */
    protected function ensureProductMainImageFromUrl(Product $product, string $imageUrl): bool
    {
        $fileName = $this->extractOriginalFileName($imageUrl);
        if (!$fileName) {
            return false;
        }

        $diskPath = "products/{$fileName}";

        Storage::disk('public')->makeDirectory('products');

        if (!Storage::disk('public')->exists($diskPath)) {
            $body = $this->downloadImage($imageUrl);
            if ($body) {
                Storage::disk('public')->put($diskPath, $body);
            }
        }

        if (!Storage::disk('public')->exists($diskPath)) {
            return false;
        }

        $product->image = $diskPath;
        $product->save();

        return true;
    }

    protected function downloadImage(string $imageUrl): ?string
    {
        try {
            $res = Http::timeout(60)
                ->retry(2, 500)
                ->withoutVerifying()
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get($imageUrl);

            if ($res->successful()) {
                return $res->body();
            }

            // Debug log (non-fatal)
            $this->command?->warn("Image download failed: {$res->status()} {$imageUrl}");

        } catch (\Exception $e) {
            $this->command?->warn("Image download exception: {$e->getMessage()} | {$imageUrl}");
        }

        return null;
    }

    protected function downloadAndAttachImage(
        string $attachableType,
        int $attachableId,
        int $companyId,
        string $imageUrl,
        string $baseDir,
        bool $isPrimary = false
    ): void {
        $fileName = $this->extractOriginalFileName($imageUrl);
        if (!$fileName) {
            return;
        }

        $baseDir = trim($baseDir, '/') . '/';
        Storage::disk('public')->makeDirectory(trim($baseDir, '/'));

        $diskPath = $baseDir . $fileName;

        if (!Storage::disk('public')->exists($diskPath)) {
            $body = $this->downloadImage($imageUrl);
            if ($body) {
                Storage::disk('public')->put($diskPath, $body);
            }
        }

        if (!Storage::disk('public')->exists($diskPath)) {
            return;
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'jpg';

        $attachment = Attachment::updateOrCreate(
            [
                'company_id' => $companyId,
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
                'disk' => 'public',
                'path' => $diskPath,
            ],
            [
                'file_name' => $fileName,
                'mime_type' => $ext,
                'is_primary' => (bool) $isPrimary,
            ]
        );

        if ($isPrimary) {
            Attachment::where('company_id', $companyId)
                ->where('attachable_type', $attachableType)
                ->where('attachable_id', $attachableId)
                ->where('id', '!=', $attachment->id)
                ->update(['is_primary' => false]);
        }
    }

    /**
     * Extract filename WITHOUT querystring.
     *
     * Example:
     * https://.../higo-bb.webp?v=1766470073
     * => higo-bb.webp
     */
    protected function extractOriginalFileName(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $base = basename($path);
        $base = urldecode($base);

        // keep Shopify naming, just sanitize dangerous chars
        $base = preg_replace('/\s+/', '-', $base);
        $base = preg_replace('/[^A-Za-z0-9\-\._]/', '', $base);

        return $base ?: null;
    }
}
