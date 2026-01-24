<?php

namespace App\Exports\Catalog;

use App\Models\Catalog\ProductVariant;
use App\Models\Inventory\InventoryLocation;
use App\Models\Pricing\PriceChannel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    protected $companyId;

    protected $priceChannels;

    protected $locations;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;

        // Load dynamic columns once
        $this->priceChannels = PriceChannel::where('is_active', 1)
            ->whereHas('companies', function ($query) {
                $query->where('company_id', $this->companyId);
            })->get();

        $this->locations = InventoryLocation::where('company_id', $this->companyId)
            ->with('locatable')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return ProductVariant::where('company_id', $this->companyId)
            ->with([
                'product.category',
                'product.brand',
                'prices',
                'inventory',
                'attributeValues.attribute',
            ])
            ->get();
    }

    /**
     * @var ProductVariant
     */
    public function map($variant): array
    {
        $product = $variant->product;

        $row = [
            $product->category_id ?? '',
            $product->category->name ?? '',
            $product->brand_id ?? '',
            $product->brand->name ?? '',
            $product->id ?? '',
            $product->name ?? '',
            $product->is_active ? 'Active' : 'Inactive',
            $variant->id,
            $variant->sku,
            $variant->attributeValues->groupBy(fn ($av) => $av->attribute->name ?? 'Unknown')
                ->map(fn ($vals, $name) => $name.': '.$vals->pluck('value')->join(', '))
                ->join('; '),
            $variant->cost_price ?? 0,
        ];

        // Map Prices
        foreach ($this->priceChannels as $channel) {
            $priceObj = $variant->prices->firstWhere('price_channel_id', $channel->id);
            $row[] = $priceObj ? $priceObj->price : '';
        }

        // Map Stock
        $totalStock = 0;
        foreach ($this->locations as $location) {
            $balance = $variant->inventory->firstWhere('inventory_location_id', $location->id);
            $qty = $balance ? (float) $balance->quantity : 0;
            $row[] = $qty;
            $totalStock += $qty;
        }

        $row[] = $totalStock;

        return $row;
    }

    public function headings(): array
    {
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
        foreach ($this->priceChannels as $channel) {
            $headers[] = 'Price - '.($channel->name ?: $channel->code);
        }

        // Add Stock Location headers
        foreach ($this->locations as $location) {
            $locationName = $location->locatable->name ?? 'Unknown ('.$location->type.')';
            $headers[] = 'Stock - '.$locationName;
        }

        $headers[] = 'Total Stock';

        return $headers;
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_TEXT, // Variant SKU
        ];
    }
}
