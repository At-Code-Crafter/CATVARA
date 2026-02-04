<?php

namespace App\Services\Sales;

use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use App\Models\Tax\TaxGroup;

class QuoteCalculationService
{
    public function __construct(
        protected TaxCalculationService $taxService
    ) {}

    /**
     * Calculate all totals for a quote payload
     * Mirrors OrderCalculationService for consistency
     */
    public function calculate(int $companyId, array $payload): array
    {
        $items = $payload['items'] ?? [];
        $shipping = (float) ($payload['shipping'] ?? 0);
        $additional = (float) ($payload['additional'] ?? 0);
        $globalDiscPercent = (float) ($payload['global_discount_percent'] ?? 0);
        $globalDiscAmt = (float) ($payload['global_discount_amount'] ?? 0);
        $quoteTaxGroupId = $payload['tax_group_id'] ?? null;
        $customerId = $payload['customer_id'] ?? null;

        $customer = $customerId ? Customer::find($customerId) : null;
        $isExempt = $customer ? (bool) $customer->is_tax_exempt : false;

        $subtotal = 0;
        $itemsLineDiscountTotal = 0;
        $taxTotal = 0;
        $rows = [];

        foreach ($items as $item) {
            $type = $item['type'] ?? 'variant';
            $isCustom = ($type === 'custom');
            $qty = max(1, (float) ($item['qty'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discPercent = min(100, max(0, (float) ($item['discount_percent'] ?? 0)));

            $variantId = null;
            $productName = 'Custom Item';
            $variantDescription = null;
            $customSku = null;
            $variant = null;

            if ($isCustom) {
                $productName = $item['custom_name'] ?? 'Custom Item';
                $customSku = $item['custom_sku'] ?? null;
                $variantDescription = $customSku ? "SKU: {$customSku}" : null;
            } else {
                $variantUuid = $item['variant_id'] ?? null;
                if (! $variantUuid) {
                    continue;
                }

                $variant = ProductVariant::with(['product.category'])
                    ->where('company_id', $companyId)
                    ->where('uuid', $variantUuid)
                    ->first();

                if (! $variant) {
                    continue;
                }

                $variantId = $variant->id;
                $productName = $variant->product->name;
                $variantDescription = method_exists($variant, 'getVariantDescription')
                    ? $variant->getVariantDescription()
                    : null;
            }

            // Tax resolution (same as orders)
            $taxGroupId = null;
            if (! $isExempt) {
                $taxGroupId = $item['tax_group_id'] ?? null;

                if (! $taxGroupId && $variant && $variant->product->category) {
                    $taxGroupId = $variant->product->category->tax_group_id;
                }

                if (! $taxGroupId && $customer) {
                    $taxGroupId = $customer->tax_group_id;
                }

                if (! $taxGroupId) {
                    $taxGroupId = $quoteTaxGroupId;
                }
            }

            $taxRate = 0;
            if ($taxGroupId) {
                $taxGroup = TaxGroup::find($taxGroupId);
                $taxRate = $taxGroup ? $taxGroup->activeRateSum() : 0;
            }

            $lineRaw = $unitPrice * $qty;
            $lineDisc = $lineRaw * ($discPercent / 100);
            $taxable = max(0, $lineRaw - $lineDisc);
            $lineTax = $taxable * ($taxRate / 100);
            $lineTotal = $taxable + $lineTax;

            $subtotal += $lineRaw;
            $itemsLineDiscountTotal += $lineDisc;
            $taxTotal += $lineTax;

            $rows[] = [
                'product_variant_id' => $variantId,
                'is_custom' => $isCustom,
                'custom_sku' => $customSku,
                'product_name' => $productName,
                'variant_description' => $variantDescription,
                'unit_price' => $unitPrice,
                'quantity' => $qty,
                'line_subtotal' => $lineRaw,
                'discount_percent' => $discPercent,
                'discount_amount' => $lineDisc,
                'line_discount_total' => $lineDisc,
                'tax_group_id' => $taxGroupId,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ];
        }

        // Global discount
        $postLineSubtotal = $subtotal - $itemsLineDiscountTotal;
        $globalDiscountTotal = ($postLineSubtotal * ($globalDiscPercent / 100)) + $globalDiscAmt;

        $shippingTotal = max(0, $shipping + $additional);

        // Shipping tax (using quote level tax group if present)
        $quoteTaxRate = 0;
        if ($quoteTaxGroupId) {
            $otg = TaxGroup::find($quoteTaxGroupId);
            $quoteTaxRate = $otg ? $otg->activeRateSum() : 0;
        }
        $shippingTax = $shippingTotal * ($quoteTaxRate / 100);

        $grandTotal = max(0, $postLineSubtotal - $globalDiscountTotal) + $taxTotal + $shippingTotal + $shippingTax;

        return [
            'subtotal' => $subtotal,
            'items_line_discount_total' => $itemsLineDiscountTotal,
            'global_discount_total' => $globalDiscountTotal,
            'discount_total' => $itemsLineDiscountTotal + $globalDiscountTotal,
            'tax_total' => $taxTotal,
            'shipping_total' => $shippingTotal,
            'shipping_tax_total' => $shippingTax,
            'grand_total' => (float) $grandTotal,
            'items_for_db' => $rows,
        ];
    }
}
