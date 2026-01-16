<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function loadPaymentTerms()
    {
        $paymentTerms = request()->company->paymentTerms->map(function ($paymentTerm) {
            return [
                'id' => $paymentTerm->id,
                'name' => $paymentTerm->name,
                'due_days' => $paymentTerm->due_days,
            ];
        });

        return response()->json($paymentTerms);
    }

    public function loadPaymentMethods()
    {
        $methods = \App\Models\Accounting\PaymentMethod::where('company_id', request()->company->id)
            ->active()
            ->select(['id', 'name', 'code', 'type'])
            ->get();
        return response()->json($methods);
    }

    public function loadCurrencies()
    {
        $currencies = request()->company->currencies->map(function ($currency) {
            return [
                'id' => $currency->id,
                'name' => $currency->name,
                'code' => $currency->code,
            ];
        });

        return response()->json($currencies);
    }

    public function loadCustomers()
    {
        $customers = Customer::where('company_id', request()->company->id)->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'initial' => $customer->initials,
                'name' => $customer->display_name,
                'legal_name' => $customer->legal_name,
                'address' => optional($customer->address)->render() ?? null,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'isCompany' => $customer->type == 'COMPANY',
                'customerType' => $customer->type,
                'taxNumber' => $customer->tax_number,
                'payment_term_id' => $customer->payment_term_id,
                'uuid' => $customer->uuid,
            ];
        });

        return response()->json($customers);
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
                            },
                        ]);
                },
            ])
            ->get()
            ->map(function ($product) {

                $productId = (string) ($product->uuid ?? $product->id);

                $variants = $product->variants->map(function ($variant) {

                    $attrs = $variant->attributeValues
                        ->filter(fn ($av) => $av->attribute)
                        ->mapWithKeys(fn ($av) => [$av->attribute->name => $av->value])
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
                    'image_url' => $product->image ? asset('storage/'.$product->image) : null,
                    'variants' => $variants,
                ];
            })
            ->values();

        return response()->json($products);
    }
}
