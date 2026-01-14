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

            // Note: Stock filtering might require a Join or a specific whereHas on variants.
            // For now, implementing Status as it was specifically mentioned.

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    $img = $row->image;
                    $src = $img ? asset('storage/' . $img) : asset('theme/adminlte/dist/img/default-150x150.png');

                    return '
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 border border-slate-100 shadow-sm">
                                <img src="' . e($src) . '" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-800 truncate">' . e($row->name) . '</div>
                                <div class="text-[10px] font-medium text-slate-400 truncate uppercase tracking-tight">' . e($row->slug) . '</div>
                            </div>
                        </div>';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category
                        ? '<span class="text-sm font-medium text-slate-600">' . e($row->category->name) . '</span>'
                        : '<span class="text-xs text-slate-300 italic">Uncategorized</span>';
                })
                ->addColumn('variants_count', function ($row) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-600 border border-brand-100">' . $row->variants->count() . ' Variants</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = company_route('catalog.products.edit', ['product' => $row->id]);

                    return '
                        <div class="flex items-center justify-end gap-2">
                             <a href="' . $editUrl . '" class="text-slate-400 hover:text-brand-600 transition-colors p-1" title="Edit Product">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['name', 'category_name', 'variants_count', 'action'])
                ->make(true);
        }

        $categories = Category::where('company_id', $request->company->id)->get();

        return view('catvara.catalog.products.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('create', 'products');

        $categories = Category::where('company_id', request()->company->id)->get();
        $attributes = Attribute::where('company_id', request()->company->id)->with('values')->get();

        return view('catvara.catalog.products.create', compact('categories', 'attributes'));
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
        $attributes = Attribute::where('company_id', $company->id)->get();

        $channels = PriceChannel::get(); // Global channels
        $locations = InventoryLocation::where('company_id', $company->id)->with('locatable')->get();
        $currency = Currency::first(); // Default currency

        return view('catvara.catalog.products.edit', compact('product', 'categories', 'attributes', 'channels', 'locations', 'currency'));
    }

    public function update(Request $request, \App\Models\Company\Company $company, $id)
    {
        $this->authorize('edit', 'products');

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
        $this->authorize('create', 'products');

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'description' => 'nullable|string',
            'variants' => 'required|array',
            'image' => 'nullable|image|max:5120', // 5MB
        ]);

        try {
            DB::beginTransaction();

            $product = new Product;
            $product->uuid = (string) Str::uuid();
            $product->company_id = $request->company->id;
            $product->category_id = $request->category_id;
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


}
