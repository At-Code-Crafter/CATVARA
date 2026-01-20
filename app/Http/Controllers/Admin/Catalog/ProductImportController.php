<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Imports\Catalog\ProductImport;
use App\Models\Catalog\Brand;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryLocation;
use App\Models\Pricing\Currency;
use App\Models\Pricing\PriceChannel;
use App\Models\Pricing\VariantPrice;
use Illuminate\Http\Request;
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

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

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

    public function preview(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'sheet_index' => 'required|integer',
        ]);

        $absolutePath = storage_path('app/private/'.$request->temp_path);
        $sheetIndex = $request->sheet_index;

        $data = Excel::toArray(new ProductImport($request->company->id), $absolutePath)[$sheetIndex] ?? [];

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'Sheet is empty']);
        }

        $allHeaders = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($allHeaders);

        $previewData = [];
        $validationErrors = [];
        $skusInFile = [];

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
                // Check internal duplicates
                if (in_array($sku, $skusInFile)) {
                    $errors['variant_sku'] = 'Duplicate SKU in file';
                } else {
                    $skusInFile[] = $sku;
                    // Check DB duplicates
                    if (ProductVariant::where('company_id', $request->company->id)->where('sku', $sku)->exists()) {
                        $errors['variant_sku'] = 'SKU already exists in database';
                    }
                }
            }

            // Numeric checks
            foreach ($mappedRow as $field => $val) {
                if ((str_starts_with($field, 'price_') || str_starts_with($field, 'stock_') || $field === 'cost') && ! empty($val) && ! is_numeric($val)) {
                    $errors[$field] = 'Must be numeric';
                }
            }

            $previewData[] = [
                'row_index' => $i,
                'raw_data' => $row, // Include all columns
                'mapped_data' => $mappedRow,
                'errors' => $errors,
            ];

            if (! empty($errors)) {
                $validationErrors[$i] = $errors;
            }
        }

        return response()->json([
            'success' => true,
            'preview' => $previewData,
            'mapping' => $mapping,
            'all_headers' => $allHeaders,
            'total_rows' => count($data),
            'error_count' => count($validationErrors),
        ]);
    }

    private function autoResolveMapping($headers)
    {
        $mapping = [];
        $priceChannels = PriceChannel::all();
        $locations = InventoryLocation::with('locatable')->get();

        $coreMaps = [
            'brand_id' => ['brand_id'],
            'brand_name' => ['brand_name'],
            'product_id' => ['product_id'],
            'product_name' => ['product_name'],
            'variant_sku' => ['variant_sku'],
            'cost' => ['cost'],
            'category_id' => ['category_id'],
            'category_name' => ['category_name'],
            'description' => ['description'],
            'variant_id' => ['variant_id'],
            'variant_attributes' => ['variant_attributes'],
        ];

        foreach ($headers as $header) {
            $cleanHeader = strtolower(trim($header));

            // Check core maps
            foreach ($coreMaps as $field => $patterns) {
                if (in_array($cleanHeader, $patterns) && ! isset($mapping[$field])) {
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

    public function process(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'sheet_index' => 'required|integer',
        ]);

        $companyId = $request->company->id;
        $absolutePath = storage_path('app/private/'.$request->temp_path);
        $data = Excel::toArray(new ProductImport($companyId), $absolutePath)[$request->sheet_index];

        $headers = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($headers);

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                if ($index === 0) {
                    continue;
                } // Skip headers

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
                if (! empty($mapped['variant_id'])) {
                    $variant = ProductVariant::where('company_id', $companyId)->find($mapped['variant_id']);
                }
                if (! $variant && ! empty($mapped['variant_sku'])) {
                    $variant = ProductVariant::where('company_id', $companyId)
                        ->where('sku', $mapped['variant_sku'])
                        ->first();
                }

                // Resolve Product
                $product = null;
                if (! empty($mapped['product_id'])) {
                    $product = Product::where('company_id', $companyId)->find($mapped['product_id']);
                }
                if (! $product && $variant) {
                    $product = $variant->product;
                }
                if (! $product && ! empty($mapped['product_name'])) {
                    $product = Product::where('company_id', $companyId)
                        ->where('name', $mapped['product_name'])
                        ->first();
                }

                // Resolve Category
                $categoryId = $mapped['category_id'] ?? null;
                if (! $categoryId && ! empty($mapped['category_name'])) {
                    $category = Category::firstOrCreate(
                        ['company_id' => $companyId, 'name' => $mapped['category_name']],
                        ['slug' => Str::slug($mapped['category_name']).'-'.time(), 'is_active' => true]
                    );
                    $categoryId = $category->id;
                }

                // Resolve Brand
                $brandId = $mapped['brand_id'] ?? null;
                if (! $brandId && ! empty($mapped['brand_name'])) {
                    $brand = Brand::firstOrCreate(
                        ['company_id' => $companyId, 'name' => $mapped['brand_name']],
                        ['slug' => Str::slug($mapped['brand_name']).'-'.time(), 'is_active' => true]
                    );
                    $brandId = $brand->id;
                }

                if (! $product) {
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
                        'variant_attributes' => $mapped['variant_attributes'] ?? null,
                        'is_active' => true,
                    ]);
                } else {
                    // Update Variant
                    $variant->update([
                        'sku' => $mapped['variant_sku'] ?? $variant->sku,
                        'cost_price' => $mapped['cost'] ?? $variant->cost_price,
                        'variant_attributes' => $mapped['variant_attributes'] ?? $variant->variant_attributes,
                    ]);
                }

                // Handle Prices
                $currency = Currency::where('code', 'GBP')->first() ?? Currency::first();
                foreach ($mapping as $dbField => $excelCol) {
                    if (str_starts_with($dbField, 'price_')) {
                        $channelId = str_replace('price_', '', $dbField);
                        $priceValue = $row[$excelCol] ?? null;

                        if (is_numeric($priceValue)) {
                            VariantPrice::create([
                                'company_id' => $companyId,
                                'product_variant_id' => $variant->id,
                                'price_channel_id' => $channelId,
                                'currency_id' => $currency->id,
                                'price' => $priceValue,
                                'valid_from' => now(),
                                'is_active' => true,
                            ]);
                        }
                    }

                    if (str_starts_with($dbField, 'stock_')) {
                        $locationId = str_replace('stock_', '', $dbField);
                        $qty = $row[$excelCol] ?? 0;

                        if (is_numeric($qty)) {
                            InventoryBalance::create([
                                'company_id' => $companyId,
                                'product_variant_id' => $variant->id,
                                'inventory_location_id' => $locationId,
                                'quantity' => $qty,
                            ]);
                        }
                    }
                }

                $imported++;
            }

            DB::commit();
            Storage::delete($request->temp_path);

            return response()->json([
                'success' => true,
                'imported' => $imported,
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
