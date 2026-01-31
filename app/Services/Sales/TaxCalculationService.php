<?php

namespace App\Services\Sales;

use App\Models\Tax\TaxGroup;

class TaxCalculationService
{
    /**
     * Calculate tax for a single line item
     */
    public function calculateLineTax(float $baseAmount, ?int $taxGroupId): float
    {
        if (! $taxGroupId) {
            return 0;
        }

        $taxGroup = TaxGroup::find($taxGroupId);
        if (! $taxGroup) {
            return 0;
        }

        $rate = $taxGroup->activeRateSum();

        return ($baseAmount * $rate) / 100;
    }

    /**
     * Calculate total tax for a collection of processed items
     */
    public function calculateTotalTax(array $items, ?int $globalTaxGroupId = null): float
    {
        $totalTax = 0;

        foreach ($items as $item) {
            $lineAmount = $item['line_subtotal'] ?? 0;
            // Use line tax group if present, otherwise fallback to global
            $taxId = $item['tax_group_id'] ?? $globalTaxGroupId;

            $totalTax += $this->calculateLineTax($lineAmount, $taxId);
        }

        return $totalTax;
    }
}
