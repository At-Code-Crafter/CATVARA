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
        if ($request->ajax()) {
            $query = Product::where('company_id', $request->company->id)
                ->with(['category', 'variants', 'attachments']); // Eager load attachments for thumbnail

            // Optional: Filter by Category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    $img = $row->attachments->where('is_primary', true)->first();
                    $src = $img ? asset('storage/' . $img->path) : asset('theme/adminlte/dist/img/default-150x150.png');

                    return '<div class="d-flex align-items-center">
                                <img src="' . e($src) . '" class="img-thumbnail mr-2" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <div class="font-weight-bold">' . e($row->name) . '</div>
                                    <small class="text-muted">' . e($row->slug) . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category ? e($row->category->name) : '<span class="text-muted">Uncategorized</span>';
                })
                ->addColumn('variants_count', function ($row) {
                    return '<span class="badge badge-info">' . $row->variants->count() . ' Variants</span>';
                })
                ->addColumn('action', function ($row) {
                    $compact['editUrl'] = company_route('catalog.products.edit', ['product' => $row->id]);
                    $compact['deleteUrl'] = company_route('catalog.products.destroy', ['product' => $row->id]);

                    return view('theme.adminlte.components._table-actions', $compact)->render();
                })
                ->rawColumns(['name', 'category_name', 'variants_count', 'action'])
                ->make(true);
        }

        $categories = Category::where('company_id', $request->company->id)->get();

        return view('theme.adminlte.catalog.products.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::where('company_id', request()->company->id)->get();
        $attributes = Attribute::where('company_id', request()->company->id)->with('values')->get();

        return view('theme.adminlte.catalog.products.create', compact('categories', 'attributes'));
    }

    protected $productService;

    public function __construct(\App\Services\Catalog\ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function edit(\App\Models\Company\Company $company, $id)
    {
        // $company is passed via route model binding
        $product = Product::where('company_id', $company->id)->with(['variants.attributeValues', 'variants.prices', 'variants.inventory'])->findOrFail($id);
        $categories = Category::where('company_id', $company->id)->get();
        $attributes = Attribute::where('company_id', $company->id)->get();

        $channels = PriceChannel::get(); // Global channels
        $locations = InventoryLocation::where('company_id', $company->id)->with('locatable')->get();
        $currency = Currency::first(); // Default currency

        return view('theme.adminlte.catalog.products.edit', compact('product', 'categories', 'attributes', 'channels', 'locations', 'currency'));
    }

    public function update(Request $request, \App\Models\Company\Company $company, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'variants' => 'nullable|array',
            'prices' => 'nullable|array',
            'primary_image' => 'nullable|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::where('company_id', $company->id)->findOrFail($id);

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
        // Complex form handling
        // 1. Create Product
        // 2. Iterate variants JSON/array
        // 3. Create Variants + Prices + Inventory

        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required',
            'variants' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $product = new Product;
            $product->uuid = Str::uuid();
            $product->company_id = $request->company->id;
            $product->category_id = $request->category_id;
            $product->name = $request->name;
            $product->slug = Str::slug($request->name) . '-' . time();
            $product->description = $request->description;
            $product->save();

            // Get default defaults
            $currency = Currency::first(); // Assuming seeded
            $channel = PriceChannel::where('code', 'WEBSITE')->first(); // Assuming seeded
            // Fallback
            if (!$channel) {
                $channel = PriceChannel::first();
            }

            // Default location - NOT USED anymore in this flow
            // $location = InventoryLocation::where('company_id', $request->company->id)->first();

            foreach ($request->variants as $v) {
                // $v should contain: attributes (combo), price, cost, sku

                $variant = new ProductVariant;
                $variant->uuid = Str::uuid();
                $variant->company_id = $request->company->id;
                $variant->product_id = $product->id;
                $variant->sku = $v['sku'] ?? ($product->slug . '-' . Str::random(4));
                $variant->barcode = $v['barcode'] ?? null;
                $variant->cost_price = $v['cost'] ?? null; // Added Cost
                $variant->save();

                // Attach attributes
                // $v['attributes'] could be [attr_id => val_id, ...]
                if (isset($v['attributes']) && is_array($v['attributes'])) {
                    foreach ($v['attributes'] as $valId) {
                        $variant->attributeValues()->attach($valId);
                    }
                }

                // Create Price
                if (isset($v['price'])) {
                    $vp = new VariantPrice;
                    $vp->company_id = $request->company->id;
                    $vp->product_variant_id = $variant->id;
                    $vp->price_channel_id = $channel->id;
                    $vp->currency_id = $currency->id;
                    $vp->price = $v['price'];
                    $vp->valid_from = now();
                    $vp->save();
                }

                // MOVED: Opening Stock logic is now part of the Inventory Module
            }

            DB::commit();

            return response()->json(['success' => true, 'redirect' => company_route('catalog.products.index')]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function loadProducts(Request $request)
    {
        $companyId = $request->company->id;

        $products = Product::query()
            ->where('company_id', $companyId)
            ->with([
                'category:id,name',
                'variants' => function ($q) {
                    $q->select(['id', 'uuid', 'product_id']) // keep product_id for relation
                        ->with([
                            'attributeValues:id,attribute_id,value',
                            'attributeValues.attribute:id,name',
                        ])
                        // Inventory sum (change `quantity` if your column differs)
                        ->withSum('inventory as stock', 'quantity')
                        // Latest price (change `price` column if needed)
                        ->with([
                            'prices' => function ($pq) {
                                $pq->select(['id', 'product_variant_id', 'price'])
                                    ->latest('id');
                            }
                        ]);
                }
            ])
            ->get()
            ->map(function ($product) {

                $productId = (string) ($product->uuid ?? $product->id);

                $variants = $product->variants->map(function ($variant) {

                    $attrs = $variant->attributeValues
                        ->filter(fn($av) => $av->attribute)
                        ->mapWithKeys(fn($av) => [$av->attribute->name => $av->value])
                        ->all();

                    $priceRow = $variant->prices->first();
                    $price = $priceRow ? (float) $priceRow->price : (float) ($variant->price ?? 0);

                    $stock = (int) ($variant->stock ?? 0); // from withSum alias
    
                    return [
                        'id' => (string) ($variant->uuid ?? $variant->id),
                        'attrs' => $attrs,
                        'price' => $price,
                        'stock' => $stock,
                    ];
                })->values()->all();

                return [
                    'id' => $productId,
                    'name' => (string) $product->name,
                    'category' => (string) optional($product->category)->name,
                    'brand' => (string) ($product->brand ?? ''),
                    'variants' => $variants,
                ];
            })
            ->values();

        return response()->json($products);
    }

}
