<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Tax\TaxGroup;
use Illuminate\Http\Request;

class SalesOrderComponentController extends Controller
{
    /**
     * Render the variant selector modal content
     */
    public function variantModal(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::with(['variants', 'category'])->where('id', $productId)->orWhere('uuid', $productId)->firstOrFail();
        
        return view('catvara.sales-orders.partials._variant_modal_content', compact('product'))->render();
    }

    /**
     * Render the custom item form modal content
     */
    public function customItemModal(Request $request)
    {
        return view('catvara.sales-orders.partials._custom_item_modal_content')->render();
    }
}
