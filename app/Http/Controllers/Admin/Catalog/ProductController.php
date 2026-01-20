<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Attribute;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryLocation;
use App\Models\Pricing\Currency;
use App\Models\Pricing\PriceChannel;
use App\Models\Pricing\VariantPrice;
// use App\Models\Inventory\InventoryBalance; // Removed for separation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', 'products');

        if ($request->ajax()) {
            $query = Product::where('company_id', $request->company->id)
                ->with(['category', 'variants', 'attachments']); // Eager load attachments for thumbnail

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            if ($request->filled('stock_level')) {
                $status = $request->stock_level;
                if ($status === 'in_stock') {
                    $query->whereHas('variants.inventory', function ($q) {
                        $q->where('quantity', '>', 0);
                    });
                } elseif ($status === 'low_stock') {
                    $query->whereHas('variants.inventory', function ($q) {
                        $q->where('quantity', '>', 0)->where('quantity', '<=', 5);
                    });
                } elseif ($status === 'out_of_stock') {
                    // Products that do NOT have any inventory > 0
                    $query->whereDoesntHave('variants.inventory', function ($q) {
                        $q->where('quantity', '>', 0);
                    });
                }
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('products.created_at', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59'
                ]);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image_url', function ($row) {
                    return $row->image ? asset('storage/' . $row->image) : null;
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category ? $row->category->name : null;
                })
                ->addColumn('variants_count', function ($row) {
                    return $row->variants->count();
                })
                ->addColumn('edit_url', function ($row) {
                    return company_route('catalog.products.edit', ['product' => $row->id]);
                })
                ->addColumn('action', function ($row) {
                    return ''; // Actions are rendered client-side using edit_url
                })
                ->make(true);
        }

        $categories = Category::where('company_id', $request->company->id)->get();
        $brands = \App\Models\Catalog\Brand::where('company_id', $request->company->id)->get();

        return view('catvara.catalog.products.index', compact('categories', 'brands'));
    }

    public function create()
    {
        $this->authorize('create', 'products');

        $categories = Category::where('company_id', request()->company->id)->get();
        $brands = \App\Models\Catalog\Brand::where('company_id', request()->company->id)->get();
        $attributes = Attribute::where('company_id', request()->company->id)->with('values')->get();

        return view('catvara.catalog.products.create', compact('categories', 'brands', 'attributes'));
    }

    protected $productService;

    public function __construct(\App\Services\Catalog\ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function edit(\App\Models\Company\Company $company, $id)
    {
        $this->authorize('edit', 'products');

        // $company is passed via route model binding
        $product = Product::where('company_id', $company->id)->with(['variants.attributeValues', 'variants.prices', 'variants.inventory'])->findOrFail($id);
        $categories = Category::where('company_id', $company->id)->get();
        $brands = \App\Models\Catalog\Brand::where('company_id', $company->id)->get();
        $attributes = Attribute::where('company_id', $company->id)->get();

        $channels = PriceChannel::get(); // Global channels
        $locations = InventoryLocation::where('company_id', $company->id)->with('locatable')->get();
        $currency = Currency::first(); // Default currency

        return view('catvara.catalog.products.edit', compact('product', 'categories', 'brands', 'attributes', 'channels', 'locations', 'currency'));
    }

    public function update(Request $request, \App\Models\Company\Company $company, $id)
    {
        $this->authorize('edit', 'products');

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|string', // Can be ID or "new:Name"
            'brand_id' => 'nullable|string', // Can be ID or "new:Name"
            'variants' => 'nullable|array',
            'prices' => 'nullable|array',
            'primary_image' => 'nullable|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::where('company_id', $company->id)->findOrFail($id);

            // Handle Category - create if new
            $categoryId = $this->resolveOrCreateCategory($request->category_id, $company->id);
            $request->merge(['category_id' => $categoryId]);

            // Handle Brand - create if new
            $brandId = $this->resolveOrCreateBrand($request->brand_id, $company->id);
            $request->merge(['brand_id' => $brandId]);

            // 1. Update Core & Variants & Prices via Service
            $this->productService->updateProduct($product, $request->all());

            // 2. Handle Primary Image Upload
            if ($request->hasFile('primary_image')) {
                // Reset old primary
                \App\Models\Common\Attachment::where('company_id', $company->id)
                    ->where('attachable_id', $product->id)
                    ->where('attachable_type', Product::class)
                    ->update(['is_primary' => false]);

                $path = $request->file('primary_image')->store('products', 'public');

                $product->attachments()->create([
                    'company_id' => $company->id,
                    'disk' => 'public',
                    'path' => $path,
                    'file_name' => $request->file('primary_image')->getClientOriginalName(),
                    'mime_type' => $request->file('primary_image')->getMimeType(),
                    'size' => $request->file('primary_image')->getSize(),
                    'is_primary' => true,
                ]);
            }

            // 3. Handle Additional Images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    $product->attachments()->create([
                        'company_id' => $company->id,
                        'disk' => 'public',
                        'path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'is_primary' => false,
                    ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->authorize('create', 'products');

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|string', // Can be ID or "new:Name"
            'brand_id' => 'nullable|string', // Can be ID or "new:Name"
            'description' => 'nullable|string',
            'variants' => 'required|array',
            'image' => 'nullable|image|max:5120', // 5MB
        ]);

        try {
            DB::beginTransaction();

            // Handle Category - create if new
            $categoryId = $this->resolveOrCreateCategory($request->category_id, $request->company->id);

            // Handle Brand - create if new
            $brandId = $this->resolveOrCreateBrand($request->brand_id, $request->company->id);

            $product = new Product;
            $product->uuid = (string) Str::uuid();
            $product->company_id = $request->company->id;
            $product->category_id = $categoryId;
            $product->brand_id = $brandId;
            $product->name = $request->name;
            $product->slug = Str::slug($request->name) . '-' . time();
            $product->description = $request->description;

            // Save first to get ID
            $product->save();

            /**
             * IMAGE UPLOAD (stores path into $product->image)
             * storage/app/public/products/...
             */
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $safeName = Str::slug($product->name) . '-' . $product->id . '-' . Str::random(6) . '.' . $ext;

                $path = $file->storeAs('products', $safeName, 'public'); // returns products/xxx.jpg

                $product->image = $path;
                $product->save();
            }

            // Defaults
            $currency = Currency::first(); // Assuming seeded
            $channel = PriceChannel::where('code', 'WEBSITE')->first() ?? PriceChannel::first();

            foreach ($request->variants as $v) {
                $variant = new ProductVariant;
                $variant->uuid = (string) Str::uuid();
                $variant->company_id = $request->company->id;
                $variant->product_id = $product->id;
                $variant->sku = $v['sku'] ?? ($product->slug . '-' . Str::random(4));
                $variant->barcode = $v['barcode'] ?? null;
                $variant->cost_price = $v['cost'] ?? null;
                $variant->save();

                // Attach attribute values (value IDs)
                if (isset($v['attributes']) && is_array($v['attributes'])) {
                    foreach ($v['attributes'] as $valId) {
                        if ($valId) {
                            $variant->attributeValues()->attach($valId);
                        }
                    }
                }

                // Price (selling)
                if (isset($v['price']) && $v['price'] !== null && $v['price'] !== '') {
                    $vp = new VariantPrice;
                    $vp->company_id = $request->company->id;
                    $vp->product_variant_id = $variant->id;
                    $vp->price_channel_id = $channel->id;
                    $vp->currency_id = $currency->id;
                    $vp->price = $v['price'];
                    $vp->valid_from = now();
                    $vp->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => company_route('catalog.products.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export products with variants to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('view', 'products');

        $companyId = $request->company->id;

        // 1. Get Dynamic Columns: Price Channels
        $priceChannels = PriceChannel::all();

        // 2. Get Dynamic Columns: Inventory Locations (Stores & Warehouses)
        $locations = InventoryLocation::where('company_id', $companyId)
            ->with('locatable')
            ->get();

        // Prepare Headers
        $headers = [
            'Category ID',
            'Category Name',
            'Brand ID',
            'Brand Name',
            'Product ID',
            'Product Name',
            'Status',
            'Variant ID',
            'Variant SKU',
            'Variant Attributes',
            'Cost',
        ];

        // Add Price Channel headers
        foreach ($priceChannels as $channel) {
            $headers[] = 'Price - ' . ($channel->name ?: $channel->code);
        }

        // Add Stock Location headers
        foreach ($locations as $location) {
            $locationName = $location->locatable->name ?? 'Unknown (' . $location->type . ')';
            $headers[] = 'Stock - ' . $locationName;
        }

        $headers[] = 'Total Stock';

        // Get all products with variants, category, brand, prices, and inventory
        $products = Product::where('company_id', $companyId)
            ->with([
                'category',
                'brand',
                'variants.prices',
                'variants.inventory',
                'variants.attributeValues.attribute',
            ])
            ->get();

        $csvData = [];
        $csvData[] = $headers;

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                $row = [
                    $product->category_id,
                    $product->category->name ?? '',
                    $product->brand_id,
                    $product->brand->name ?? '',
                    $product->id,
                    $product->name,
                    $product->is_active ? 'Active' : 'Inactive',
                    $variant->id,
                    '="' . $variant->sku . '"',
                    $variant->attributeValues->groupBy(fn($av) => $av->attribute->name ?? 'Unknown')
                        ->map(fn($vals, $name) => $name . ': ' . $vals->pluck('value')->join(', '))
                        ->join('; '),
                    $variant->cost_price ?? 0,
                ];

                // Map Prices
                foreach ($priceChannels as $channel) {
                    $priceObj = $variant->prices->firstWhere('price_channel_id', $channel->id);
                    $row[] = $priceObj ? $priceObj->price : '';
                }

                // Map Stock
                $totalStock = 0;
                foreach ($locations as $location) {
                    $balance = $variant->inventory->firstWhere('inventory_location_id', $location->id);
                    $qty = $balance ? (float) $balance->quantity : 0;
                    $row[] = $qty;
                    $totalStock += $qty;
                }

                $row[] = $totalStock;

                $csvData[] = $row;
            }
        }

        // Generate CSV
        $filename = 'products_export_' . date('Y-m-d_His') . '.csv';

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Resolve or create a category from the input value.
     * If value starts with "new:", create a new category with that name.
     * Otherwise, return the existing ID.
     */
    protected function resolveOrCreateCategory($value, $companyId)
    {
        if (empty($value)) {
            return null;
        }

        // Check if it's a new category (starts with "new:")
        if (str_starts_with($value, 'new:')) {
            $name = trim(substr($value, 4));

            if (empty($name)) {
                return null;
            }

            // Check if category with same name already exists
            $existing = Category::where('company_id', $companyId)
                ->where('name', $name)
                ->first();

            if ($existing) {
                return $existing->id;
            }

            // Create new category
            $category = Category::create([
                'company_id' => $companyId,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . time(),
                'is_active' => true,
            ]);

            return $category->id;
        }

        // It's an existing ID
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Resolve or create a brand from the input value.
     * If value starts with "new:", create a new brand with that name.
     * Otherwise, return the existing ID.
     */
    protected function resolveOrCreateBrand($value, $companyId)
    {
        if (empty($value)) {
            return null;
        }

        // Check if it's a new brand (starts with "new:")
        if (str_starts_with($value, 'new:')) {
            $name = trim(substr($value, 4));

            if (empty($name)) {
                return null;
            }

            // Check if brand with same name already exists
            $existing = \App\Models\Catalog\Brand::where('company_id', $companyId)
                ->where('name', $name)
                ->first();

            if ($existing) {
                return $existing->id;
            }

            // Create new brand
            $brand = \App\Models\Catalog\Brand::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $companyId,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . time(),
                'is_active' => true,
            ]);

            return $brand->id;
        }

        // It's an existing ID
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Show the import form
     */
    public function showImportForm(Request $request)
    {
        $this->authorize('create', 'products');

        return view('catvara.catalog.products.import');
    }

    /**
     * Import products from uploaded file - auto-detects columns
     */
    public function import(Request $request)
    {
        $this->authorize('create', 'products');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        // Increase time limit for import
        set_time_limit(300);

        try {
            DB::beginTransaction();

            $file = $request->file('file');

            // Use chunk reading for large files
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
            $reader->setReadDataOnly(true);

            // For CSV files, use simpler parsing
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'csv') {
                $data = array_map('str_getcsv', file($file->getPathname()));
            } else {
                // Read only first 200 rows for xlsx/xls
                $chunkFilter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
                    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool {
                        return $row <= 200;
                    }
                };
                $reader->setReadFilter($chunkFilter);
                $spreadsheet = $reader->load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }

            $companyId = $request->company->id;

            // Find header row and create column mapping
            $headerRow = 0;
            $columnMap = [];

            foreach ($data as $index => $row) {
                $headerFound = false;
                foreach ($row as $colIndex => $cell) {
                    $cellLower = strtolower(trim($cell ?? ''));
                    // Check for standard export format OR delivery note format
                    if (in_array($cellLower, ['brand name', 'category name', 'product name', 'variant sku', 'sku', 'name', 'item no', 'item no:', 'description'])) {
                        $headerFound = true;
                        break;
                    }
                }
                if ($headerFound) {
                    $headerRow = $index;
                    // Map columns
                    foreach ($row as $colIndex => $cell) {
                        $cellLower = strtolower(trim($cell ?? ''));
                        if ($cellLower === 'category id') $columnMap['category_id'] = $colIndex;
                        if ($cellLower === 'category name' || $cellLower === 'category') $columnMap['category_name'] = $colIndex;
                        if ($cellLower === 'brand id') $columnMap['brand_id'] = $colIndex;
                        if ($cellLower === 'brand name' || $cellLower === 'brand' || $cellLower === 'item no' || $cellLower === 'item no:') $columnMap['brand_name'] = $colIndex;
                        if ($cellLower === 'product id') $columnMap['product_id'] = $colIndex;
                        if ($cellLower === 'product name' || $cellLower === 'description' || $cellLower === 'item' || $cellLower === 'name') $columnMap['product_name'] = $colIndex;
                        if ($cellLower === 'status') $columnMap['status'] = $colIndex;
                        if ($cellLower === 'variant id') $columnMap['variant_id'] = $colIndex;
                        if ($cellLower === 'variant sku' || $cellLower === 'sku' || $cellLower === 'code') $columnMap['sku'] = $colIndex;
                        if ($cellLower === 'variant attributes') $columnMap['attributes'] = $colIndex;
                        if ($cellLower === 'cost' || $cellLower === 'cost price') $columnMap['cost'] = $colIndex;
                        if ($cellLower === 'quantity' || $cellLower === 'qty') $columnMap['quantity'] = $colIndex;
                        if (str_starts_with($cellLower, 'price -') || $cellLower === 'price' || $cellLower === 'unit price') {
                            $channelName = str_starts_with($cellLower, 'price -') ? trim(substr($cell, 7)) : 'Default';
                            $columnMap['prices'][$colIndex] = $channelName;
                        }
                        if (str_starts_with($cellLower, 'stock -')) {
                            $locationName = trim(substr($cell, 7));
                            $columnMap['stocks'][$colIndex] = $locationName;
                        }
                    }
                    break;
                }
            }

            // Get price channels and inventory locations
            $priceChannels = PriceChannel::all()->keyBy(function ($c) {
                return strtolower($c->name ?: $c->code);
            });
            $locations = InventoryLocation::where('company_id', $companyId)
                ->with('locatable')
                ->get()
                ->keyBy(function ($l) {
                    return strtolower($l->locatable->name ?? 'Unknown');
                });

            $currency = Currency::first();

            $importedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Debug: Log what was detected
            $debugInfo = [
                'headerRow' => $headerRow,
                'columnMap' => $columnMap,
                'totalRows' => count($data),
                'firstDataRow' => isset($data[$headerRow + 1]) ? array_slice($data[$headerRow + 1], 0, 5) : null,
            ];

            // If no columns mapped, return error with debug info
            if (empty($columnMap) || !isset($columnMap['product_name'])) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Could not detect columns. Debug: ' . json_encode($debugInfo),
                ], 400);
            }

            // SKU sequence cache (prefix => last number)
            $skuSequenceCache = [];

            // Limit records for testing (remove this limit later)
            $maxRecords = 200;
            $processedCount = 0;

            // Process data rows
            for ($i = $headerRow + 1; $i < count($data); $i++) {
                // Stop after max records
                if ($processedCount >= $maxRecords) break;

                $row = $data[$i];

                // Skip empty rows
                $hasData = false;
                foreach ($row as $cell) {
                    if (!empty(trim($cell ?? ''))) {
                        $hasData = true;
                        break;
                    }
                }
                if (!$hasData) continue;

                try {
                    // Extract data
                    $categoryName = isset($columnMap['category_name']) ? trim($row[$columnMap['category_name']] ?? '') : null;
                    $brandName = isset($columnMap['brand_name']) ? trim($row[$columnMap['brand_name']] ?? '') : null;
                    $productName = isset($columnMap['product_name']) ? trim($row[$columnMap['product_name']] ?? '') : null;
                    $sku = isset($columnMap['sku']) ? trim(str_replace(['="', '"'], '', $row[$columnMap['sku']] ?? '')) : null;
                    $cost = isset($columnMap['cost']) ? floatval(str_replace(['$', '£', '€', ','], '', $row[$columnMap['cost']] ?? 0)) : null;
                    $isActive = isset($columnMap['status']) ? (strtolower(trim($row[$columnMap['status']] ?? '')) === 'active') : true;

                    // Skip if no product name
                    if (empty($productName)) {
                        $errors[] = "Row " . ($i + 1) . ": No product name found";
                        $skippedCount++;
                        continue;
                    }

                    // Get or create category
                    $categoryId = null;
                    if (!empty($categoryName)) {
                        $category = Category::where('company_id', $companyId)
                            ->where('name', $categoryName)
                            ->first();
                        if (!$category) {
                            $category = Category::create([
                                'uuid' => (string) Str::uuid(),
                                'company_id' => $companyId,
                                'name' => $categoryName,
                                'slug' => Str::slug($categoryName) . '-' . time(),
                                'is_active' => true,
                            ]);
                        }
                        $categoryId = $category->id;
                    }

                    // Get or create brand
                    $brandId = null;
                    if (!empty($brandName)) {
                        $brand = \App\Models\Catalog\Brand::where('company_id', $companyId)
                            ->where('name', $brandName)
                            ->first();
                        if (!$brand) {
                            $brand = \App\Models\Catalog\Brand::create([
                                'uuid' => (string) Str::uuid(),
                                'company_id' => $companyId,
                                'name' => $brandName,
                                'slug' => Str::slug($brandName) . '-' . time(),
                                'is_active' => true,
                            ]);
                        }
                        $brandId = $brand->id;
                    }

                    // Generate SKU if not provided: [Brand Initial][Category Initial][4-digit sequence]
                    if (empty($sku)) {
                        $brandInitial = !empty($brandName) ? strtoupper(substr($brandName, 0, 1)) : 'X';
                        $categoryInitial = !empty($categoryName) ? strtoupper(substr($categoryName, 0, 1)) : 'X';
                        $skuPrefix = $brandInitial . $categoryInitial;

                        // Get next sequence number for this prefix
                        if (!isset($skuSequenceCache[$skuPrefix])) {
                            // Find the highest existing SKU number with this prefix
                            $lastSku = ProductVariant::where('company_id', $companyId)
                                ->where('sku', 'LIKE', $skuPrefix . '%')
                                ->orderByRaw('CAST(SUBSTRING(sku, 3) AS UNSIGNED) DESC')
                                ->first();

                            if ($lastSku && preg_match('/^' . $skuPrefix . '(\d+)$/', $lastSku->sku, $matches)) {
                                $skuSequenceCache[$skuPrefix] = (int) $matches[1];
                            } else {
                                $skuSequenceCache[$skuPrefix] = 0;
                            }
                        }

                        $skuSequenceCache[$skuPrefix]++;
                        $sku = $skuPrefix . str_pad($skuSequenceCache[$skuPrefix], 4, '0', STR_PAD_LEFT);
                    }

                    // Check if variant with SKU exists
                    $existingVariant = ProductVariant::where('company_id', $companyId)
                        ->where('sku', $sku)
                        ->first();

                    if ($existingVariant) {
                        // Update existing variant
                        $existingVariant->cost_price = $cost;
                        $existingVariant->is_active = $isActive;
                        $existingVariant->save();

                        // Update product
                        $product = $existingVariant->product;
                        if ($categoryId) $product->category_id = $categoryId;
                        if ($brandId) $product->brand_id = $brandId;
                        $product->is_active = $isActive;
                        $product->save();

                        $variant = $existingVariant;
                        $updatedCount++;
                    } else {
                        // Find or create product
                        $product = Product::where('company_id', $companyId)
                            ->where('name', $productName)
                            ->first();

                        if (!$product) {
                            $product = new Product;
                            $product->uuid = (string) Str::uuid();
                            $product->company_id = $companyId;
                            $product->category_id = $categoryId;
                            $product->brand_id = $brandId;
                            $product->name = $productName;
                            $product->slug = Str::slug($productName) . '-' . time() . '-' . Str::random(4);
                            $product->is_active = $isActive;
                            $product->save();
                        }

                        // Create variant
                        $variant = new ProductVariant;
                        $variant->uuid = (string) Str::uuid();
                        $variant->company_id = $companyId;
                        $variant->product_id = $product->id;
                        $variant->sku = $sku;
                        $variant->cost_price = $cost;
                        $variant->is_active = $isActive;
                        $variant->save();

                        $importedCount++;
                    }

                    // Update prices
                    if (!empty($columnMap['prices']) && $currency) {
                        foreach ($columnMap['prices'] as $colIndex => $channelName) {
                            $priceValue = floatval(str_replace(['$', '£', '€', ','], '', $row[$colIndex] ?? 0));
                            if ($priceValue <= 0) continue;

                            $channel = $priceChannels->get(strtolower($channelName));
                            if (!$channel) continue;

                            $vp = VariantPrice::where('product_variant_id', $variant->id)
                                ->where('price_channel_id', $channel->id)
                                ->first();

                            if ($vp) {
                                $vp->price = $priceValue;
                                $vp->save();
                            } else {
                                VariantPrice::create([
                                    'company_id' => $companyId,
                                    'product_variant_id' => $variant->id,
                                    'price_channel_id' => $channel->id,
                                    'currency_id' => $currency->id,
                                    'price' => $priceValue,
                                    'valid_from' => now(),
                                ]);
                            }
                        }
                    }

                    // Update inventory (if inventory model exists)
                    if (!empty($columnMap['stocks'])) {
                        foreach ($columnMap['stocks'] as $colIndex => $locationName) {
                            $qty = floatval($row[$colIndex] ?? 0);
                            if ($qty <= 0) continue;

                            $location = $locations->get(strtolower($locationName));
                            if (!$location) continue;

                            // Check if InventoryBalance model exists
                            if (class_exists(\App\Models\Inventory\InventoryBalance::class)) {
                                $balance = \App\Models\Inventory\InventoryBalance::where('product_variant_id', $variant->id)
                                    ->where('inventory_location_id', $location->id)
                                    ->first();

                                if ($balance) {
                                    $balance->quantity = $qty;
                                    $balance->save();
                                } else {
                                    \App\Models\Inventory\InventoryBalance::create([
                                        'company_id' => $companyId,
                                        'product_variant_id' => $variant->id,
                                        'inventory_location_id' => $location->id,
                                        'quantity' => $qty,
                                    ]);
                                }
                            }
                        }
                    }

                    $processedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($i + 1) . ": " . $e->getMessage();
                    $skippedCount++;
                    $processedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import completed! {$importedCount} new, {$updatedCount} updated, {$skippedCount} skipped.",
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'errors' => $errors,
                'redirect' => company_route('catalog.products.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
