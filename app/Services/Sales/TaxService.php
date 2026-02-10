<?php

namespace App\Services\Sales;

use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use App\Models\Tax\TaxGroup;

class TaxService
{
    /**
     * Resolve the appropriate Tax Group for a line item.
     * Priority: Item Input -> Variant Category -> Customer -> Document Global
     */
    public function resolveTaxGroupId(
        ?int $inputTaxGroupId = null,
        ?ProductVariant $variant = null,
        ?Customer $customer = null,
        ?int $documentTaxGroupId = null
    ): ?int {
        // 1. If customer is tax exempt, no tax group
        if ($customer && $customer->is_tax_exempt) {
            return null;
        }

        // 2. Priority: Item Input
        if ($inputTaxGroupId) {
            return $inputTaxGroupId;
        }

        // 3. Priority: Variant's Category
        if ($variant && $variant->product && $variant->product->category && $variant->product->category->tax_group_id) {
            return $variant->product->category->tax_group_id;
        }

        // 4. Priority: Document Global
        if ($documentTaxGroupId) {
            return $documentTaxGroupId;
        }

        // 5. Priority: Customer
        if ($customer && $customer->tax_group_id) {
            return $customer->tax_group_id;
        }

        return null;
    }

    /**
     * Get the active tax rate sum for a tax group ID.
     */
    public function getTaxRate(?int $taxGroupId): float
    {
        if (!$taxGroupId) {
            return 0;
        }

        $taxGroup = TaxGroup::find($taxGroupId);

        return $taxGroup ? $taxGroup->activeRateSum() : 0;
    }

    /**
     * Calculate tax amount for a base amount and tax group ID.
     */
    public function calculateTax(float $baseAmount, ?int $taxGroupId): float
    {
        $rate = $this->getTaxRate($taxGroupId);

        return ($baseAmount * $rate) / 100;
    }

    /**
     * Calculate total tax for a collection of processed items.
     */
    public function calculateTotalTax(array $items, ?int $globalTaxGroupId = null): float
    {
        $totalTax = 0;

        foreach ($items as $item) {
            $lineAmount = $item['line_subtotal'] ?? 0;
            $taxId = $item['tax_group_id'] ?? $globalTaxGroupId;
            $totalTax += $this->calculateTax($lineAmount, $taxId);
        }

        return $totalTax;
    }
}
