<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Catalog\ProductImportPreviewRequest;
use App\Http\Requests\Admin\Catalog\ProductImportProcessRequest;
use App\Http\Requests\Admin\Catalog\ProductImportUploadRequest;
use App\Imports\Catalog\ProductImport;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\AttributeValue;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLocation;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReason;
use App\Models\Pricing\Currency;
use App\Models\Pricing\PriceChannel;
use App\Models\Pricing\VariantPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportController extends Controller
{
    public function index()
    {
        $this->authorize('create', 'products');

        $priceChannels = PriceChannel::all(['id', 'name', 'code']);
        $locations = InventoryLocation::with('locatable')->get()->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->locatable->name ?? 'Unknown Location',
            ];
        });

        return view('catvara.catalog.products.import', compact('priceChannels', 'locations'));
    }

    public function upload(ProductImportUploadRequest $request)
    {
        $path = $request->file('file')->store('temp_imports');
        $absolutePath = storage_path('app/private/'.$path);

        // Get sheets
        $sheets = Excel::toArray(new ProductImport($request->company->id), $absolutePath);
        $sheetNames = array_keys($sheets);

        // Get headers for the first sheet (default)
        $headers = [];
        if (! empty($sheets[0])) {
            $headers = array_keys($sheets[0][0] ?? []);
        }

        $priceChannels = PriceChannel::all(['id', 'name', 'code']);
        $locations = InventoryLocation::with('locatable')->get()->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->locatable->name ?? 'Unknown Location',
            ];
        });

        return response()->json([
            'success' => true,
            'temp_path' => $path,
            'sheets' => $sheetNames,
            'headers' => $headers,
            'price_channels' => $priceChannels,
            'locations' => $locations,
        ]);
    }

    public function preview(ProductImportPreviewRequest $request)
    {
        $absolutePath = storage_path('app/private/'.$request->temp_path);
        $sheetIndex = $request->sheet_index;

        $data = Excel::toArray(new ProductImport($request->company->id), $absolutePath)[$sheetIndex] ?? [];

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'Sheet is empty']);
        }

        $allHeaders = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($allHeaders, $request->company->id);

        $previewData = [];
        $validationErrors = [];
        $skusInFile = [];
        $newCount = 0;
        $updateCount = 0;

        foreach ($data as $i => $row) {
            $mappedRow = [];
            $errors = [];

            // 1. Resolve Mapped Data
            foreach ($mapping as $dbField => $excelCol) {
                $val = $row[$excelCol] ?? null;
                $mappedRow[$dbField] = $val;
            }

            // 2. Perform Validation
            $sku = $mappedRow['variant_sku'] ?? null;
            $name = $mappedRow['product_name'] ?? null;

            if (empty($name)) {
                $errors['product_name'] = 'Product Name is required';
            }
            if (empty($sku)) {
                $errors['variant_sku'] = 'SKU is required';
            } else {
                // Check internal duplicates within the file
                if (in_array($sku, $skusInFile)) {
                    $errors['variant_sku'] = 'Duplicate SKU in file';
                } else {
                    $skusInFile[] = $sku;
                }
            }

            // 3. Determine Row Type (New vs Update)
            $rowType = 'new';
            $variantId = $mappedRow['variant_id'] ?? null;
            $companyId = $request->company->id;

            if (! empty($variantId)) {
                $exists = ProductVariant::where('company_id', '=', $companyId, 'and')->where('id', '=', $variantId, 'and')->exists();
                if ($exists) {
                    $rowType = 'update';
                }
            } elseif (! empty($sku)) {
                $exists = ProductVariant::where('company_id', '=', $companyId, 'and')->where('sku', '=', $sku, 'and')->exists();
                if ($exists) {
                    $rowType = 'update';
                }
            }

            $previewData[] = [
                'row_index' => $i,
                'raw_data' => $row, // Include all columns
                'mapped_data' => $mappedRow,
                'errors' => $errors,
                'row_type' => $rowType,
            ];

            if (! empty($errors)) {
                $validationErrors[$i] = $errors;
            } else {
                if ($rowType === 'update') {
                    $updateCount++;
                } else {
                    $newCount++;
                }
            }
        }

        // Build detailed error list for all rows with errors
        $errorDetails = [];
        foreach ($validationErrors as $rowIndex => $errors) {
            foreach ($errors as $field => $message) {
                $errorDetails[] = [
                    'row' => $rowIndex + 1, // 1-based for display
                    'field' => $field,
                    'column' => $mapping[$field] ?? $field,
                    'message' => $message,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'preview' => array_slice($previewData, 0, 10), // Only first 10 for preview table
            'all_errors' => $errorDetails, // All errors for the error summary
            'mapping' => $mapping,
            'all_headers' => $allHeaders,
            'total_rows' => count($data),
            'error_count' => count($validationErrors),
            'new_count' => $newCount,
            'update_count' => $updateCount,
        ]);
    }

    private function autoResolveMapping($headers, $companyId)
    {
        $mapping = [];
        $priceChannels = PriceChannel::where('is_active', 1)->whereHas('companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->get();
        $locations = InventoryLocation::where('company_id', $companyId)->with('locatable')->get();

        $coreMaps = [
            'brand_id' => ['brand_id'],
            'brand_name' => ['brand_name'],
            'product_id' => ['product_id'],
            'product_name' => ['product_name'],
            'variant_sku' => ['variant_sku'],
            'cost' => ['cost', 'cost_price'],
            'category_id' => ['category_id'],
            'category_name' => ['category_name'],
            'description' => ['description'],
            'variant_id' => ['variant_id'],
            'variant_attributes' => ['variant_attributes'],
        ];

        foreach ($headers as $header) {
            $cleanHeader = str_replace('_', ' ', strtolower(trim($header)));

            // Check core maps
            foreach ($coreMaps as $field => $patterns) {
                if (in_array(str_replace(' ', '_', $cleanHeader), $patterns) && ! isset($mapping[$field])) {
                    $mapping[$field] = $header;

                    continue 2;
                }
            }

            // Check Price Channels
            foreach ($priceChannels as $channel) {
                $channelName = strtolower($channel->name);
                $channelCode = strtolower($channel->code);
                if (str_contains($cleanHeader, 'price') && (str_contains($cleanHeader, $channelName) || str_contains($cleanHeader, $channelCode))) {
                    $mapping['price_'.$channel->id] = $header;

                    continue 2;
                }
            }

            // Check Locations
            foreach ($locations as $loc) {
                $locName = strtolower($loc->locatable->name ?? '');
                if (str_contains($cleanHeader, 'stock') && ! empty($locName) && str_contains($cleanHeader, $locName)) {
                    $mapping['stock_'.$loc->id] = $header;

                    continue 2;
                }
                // Also match by just location name if it's specific enough
                if (! empty($locName) && $cleanHeader === $locName) {
                    $mapping['stock_'.$loc->id] = $header;

                    continue 2;
                }
            }
        }

        return $mapping;
    }

    public function process(ProductImportProcessRequest $request)
    {
        $companyId = $request->company->id;
        $absolutePath = storage_path('app/private/'.$request->temp_path);
        $data = Excel::toArray(new ProductImport($companyId), $absolutePath)[$request->sheet_index];

        $headers = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($headers, $companyId);

        $imported = 0;
        $newImported = 0;
        $updatedImported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                $mapped = [];
                foreach ($mapping as $dbField => $excelCol) {
                    $mapped[$dbField] = $row[$excelCol] ?? null;
                }

                if (empty($mapped['product_name']) || empty($mapped['variant_sku'])) {
                    $failed++;

                    continue;
                }

                // Check for existing variant
                $variant = null;
                $isNew = false;
                if (! empty($mapped['variant_id'])) {
                    $variant = ProductVariant::where('company_id', '=', $companyId, 'and')->find($mapped['variant_id']);
                }
                if (! $variant && ! empty($mapped['variant_sku'])) {
                    $variant = ProductVariant::where('company_id', '=', $companyId, 'and')
                        ->where('sku', '=', $mapped['variant_sku'], 'and')
                        ->first(['*']);
                }

                // Resolve Product
                $product = null;
                if (! empty($mapped['product_id'])) {
                    $product = Product::where('company_id', '=', $companyId, 'and')->find($mapped['product_id']);
                }
                if (! $product && $variant) {
                    $product = $variant->product;
                }
                if (! $product && ! empty($mapped['product_name'])) {
                    $product = Product::where('company_id', '=', $companyId, 'and')
                        ->where('name', '=', $mapped['product_name'], 'and')
                        ->first(['*']);
                }

                // Resolve Category
                $categoryId = $mapped['category_id'] ?? null;
                if ($categoryId) {
                    $category = Category::where('company_id', '=', $companyId, 'and')->find($categoryId);
                    if ($category) {
                        $categoryId = $category->id;
                    } else {
                        $categoryId = null; // ID provided but not found, fall back to name logic
                    }
                }

                if (! $categoryId && ! empty($mapped['category_name'])) {
                    $category = Category::firstOrCreate(
                        ['company_id' => $companyId, 'name' => $mapped['category_name']],
                        ['slug' => Str::slug($mapped['category_name']).'-'.time(), 'is_active' => true]
                    );
                    $categoryId = $category->id;
                }

                // Resolve Brand
                $brandId = $mapped['brand_id'] ?? null;
                if ($brandId) {
                    $brand = Brand::where('company_id', '=', $companyId, 'and')->find($brandId);
                    if ($brand) {
                        $brandId = $brand->id;
                    } else {
                        $brandId = null;
                    }
                }

                if (! $brandId && ! empty($mapped['brand_name'])) {
                    $brand = Brand::firstOrCreate(
                        ['company_id' => $companyId, 'name' => $mapped['brand_name']],
                        ['slug' => Str::slug($mapped['brand_name']).'-'.time(), 'is_active' => true]
                    );
                    $brandId = $brand->id;
                }

                if (! $product) {
                    $isNew = true;
                    if (empty($mapped['product_name'])) {
                        $failed++;
                        $errors[$index] = 'Product name missing for new product';

                        continue;
                    }
                    // Create Product
                    $product = Product::create([
                        'company_id' => $companyId,
                        'category_id' => $categoryId,
                        'brand_id' => $brandId,
                        'name' => $mapped['product_name'],
                        'slug' => Str::slug($mapped['product_name']).'-'.time(),
                        'description' => $mapped['description'] ?? null,
                        'is_active' => true,
                    ]);
                } else {
                    // Update Product
                    $product->update([
                        'category_id' => $categoryId ?? $product->category_id,
                        'brand_id' => $brandId ?? $product->brand_id,
                        'name' => $mapped['product_name'] ?? $product->name,
                        'description' => $mapped['description'] ?? $product->description,
                    ]);
                }

                if (! $variant) {
                    $isNew = true;
                    if (empty($mapped['variant_sku'])) {
                        $failed++;
                        $errors[$index] = 'SKU missing for new variant';

                        continue;
                    }
                    // Create Variant
                    $variant = ProductVariant::create([
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'sku' => $mapped['variant_sku'],
                        'cost_price' => $mapped['cost'] ?? 0,
                        'is_active' => true,
                    ]);
                } else {
                    // Update Variant
                    $variant->update([
                        'sku' => $mapped['variant_sku'] ?? $variant->sku,
                        'cost_price' => $mapped['cost'] ?? $variant->cost_price,
                    ]);
                }

                // Handle Variant Attributes (e.g., "Flavor: Berry Fizzy; Color: Red")
                $attributeValueIds = [];
                $attrString = $mapped['variant_attributes'] ?? null;
                if ($attrString) {
                    $pairs = explode(';', $attrString);
                    foreach ($pairs as $pair) {
                        $parts = explode(':', $pair);
                        if (count($parts) === 2) {
                            $attrName = trim($parts[0]);
                            $valString = trim($parts[1]);

                            if (! empty($attrName) && ! empty($valString)) {
                                // 1. Resolve Attribute
                                $attribute = Attribute::firstOrCreate(
                                    ['company_id' => $companyId, 'name' => $attrName],
                                    ['code' => Str::slug($attrName), 'is_active' => true]
                                );

                                // 2. Resolve Attribute Value
                                $attrValue = AttributeValue::firstOrCreate(
                                    ['attribute_id' => $attribute->id, 'value' => $valString],
                                    ['is_active' => true]
                                );

                                $attributeValueIds[] = $attrValue->id;

                                // 3. Ensure Category-Attribute link
                                if ($product->category_id) {
                                    $exists = DB::table('category_attributes')
                                        ->where('category_id', $product->category_id)
                                        ->where('attribute_id', $attribute->id)
                                        ->exists();
                                    if (! $exists) {
                                        DB::table('category_attributes')->insert([
                                            'category_id' => $product->category_id,
                                            'attribute_id' => $attribute->id,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                // Sync Attribute Values to Variant
                if (! empty($attributeValueIds)) {
                    $variant->attributeValues()->sync($attributeValueIds);
                }

                // Handle Prices
                $currency = Currency::where('code', '=', 'GBP', 'and')->first(['*']) ?? Currency::first(['*']);
                foreach ($mapping as $dbField => $excelCol) {
                    if (str_starts_with($dbField, 'price_')) {
                        $channelId = str_replace('price_', '', $dbField);
                        $priceValue = $row[$excelCol] ?? null;

                        if (is_numeric($priceValue)) {
                            VariantPrice::updateOrCreate(
                                [
                                    'company_id' => $companyId,
                                    'product_variant_id' => $variant->id,
                                    'price_channel_id' => $channelId,
                                    'currency_id' => $currency->id,
                                ],
                                [
                                    'price' => $priceValue,
                                    'valid_from' => now(),
                                    'is_active' => true,
                                ]
                            );
                        }
                    }

                    if (str_starts_with($dbField, 'stock_')) {
                        $locationId = str_replace('stock_', '', $dbField);
                        $targetQty = (float) ($row[$excelCol] ?? 0);

                        // 1. Resolve or Create Balance
                        $balance = InventoryBalance::where('company_id', $companyId)
                            ->where('product_variant_id', $variant->id)
                            ->where('inventory_location_id', $locationId)
                            ->first();

                        if (! $balance) {
                            $balance = InventoryBalance::create([
                                'uuid' => (string) Str::uuid(),
                                'company_id' => $companyId,
                                'inventory_location_id' => $locationId,
                                'product_variant_id' => $variant->id,
                                'quantity' => 0,
                            ]);
                        }

                        // 2. Calculate Delta
                        $currentQty = (float) $balance->quantity;
                        $delta = $targetQty - $currentQty;

                        if ($delta != 0) {
                            // 3. Post Movement
                            $reasonCode = $delta > 0 ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT';
                            $reason = InventoryReason::where('company_id', $companyId)
                                ->where('code', $reasonCode)
                                ->first();

                            InventoryMovement::create([
                                'uuid' => (string) Str::uuid(),
                                'company_id' => $companyId,
                                'inventory_location_id' => $locationId,
                                'product_variant_id' => $variant->id,
                                'inventory_reason_id' => $reason->id ?? null,
                                'quantity' => $delta,
                                'reference_type' => 'import',
                                'reference_id' => null,
                                'occurred_at' => now(),
                                'posted_at' => now(),
                            ]);

                            // 4. Update Balance
                            $balance->update([
                                'quantity' => $targetQty,
                                'last_movement_at' => now(),
                            ]);
                        }
                    }
                }

                $imported++;
                if ($isNew) {
                    $newImported++;
                } else {
                    $updatedImported++;
                }
            }

            DB::commit();
            Storage::delete($request->temp_path);

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'new' => $newImported,
                'updated' => $updatedImported,
                'failed' => $failed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
