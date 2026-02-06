<?php

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function fixProducts()
    {
        $content = file_get_contents(public_path('new-products.json'));
        $products = json_decode($content, true);

        $processed = [];
        $exchangeRate = 0.80; // 1 USD = 0.80 GBP

        foreach ($products as $product) {
            // Clean and convert cost
            if (isset($product[' Cost '])) {
                $cost = floatval(str_replace(['$', ' '], '', $product[' Cost ']));
                $costGbp = $cost * $exchangeRate;
            } else {
                // Already processed, use as is (prevent double conversion)
                $costGbp = $product['cost'] ?? 0;
                // Heuristic fix for previous double-conversion (2.3 -> 2.88)
                if (round($costGbp, 2) == 2.3) {
                    $costGbp = 2.88;
                }
                if (round($costGbp, 2) == 2.94) {
                    $costGbp = 3.68;
                }
            }

            // Clean selling price
            if (isset($product[' Selling Price '])) {
                $sellingPrice = floatval(str_replace(['£', ' '], '', $product[' Selling Price ']));
            } else {
                $sellingPrice = $product['selling_price'] ?? 0;
            }

            $brand = trim($product['Brand'] ?? ($product['product_name'] ?? ''));
            $category = trim($product['Category'] ?? ($product['category_name'] ?? ''));
            $variantRaw = trim($product['Variant'] ?? ($product['variant_attributes'] ?? ''));
            $sku = trim($product['SKU'] ?? ($product['variant_sku'] ?? ''));

            // Format variant string with labels (skip if already labeled)
            $variantFormatted = $this->resolveVariantAttributes($variantRaw);

            // Create a unique key for grouping (Brand, Category, Variant)
            $key = $brand.'|'.$category.'|'.$variantRaw;

            if (! isset($processed[$key])) {
                $processed[$key] = [
                    'variant_sku' => $sku,
                    'product_name' => $brand,
                    'category_name' => $category,
                    'variant_attributes' => $variantFormatted,
                    'cost' => round($costGbp, 2),
                    'selling_price' => round($sellingPrice, 2),
                    'pkg_pieces' => intval($product['PKG [ IN PIECES ]'] ?? ($product['pkg_pieces'] ?? 0)),
                    'qty_box' => intval($product['QTY [ IN BOX ]'] ?? ($product['qty_box'] ?? 0)),
                ];
            } else {
                // Sum the quantities
                $processed[$key]['pkg_pieces'] += intval($product['PKG [ IN PIECES ]'] ?? ($product['pkg_pieces'] ?? 0));
                $processed[$key]['qty_box'] += intval($product['QTY [ IN BOX ]'] ?? ($product['qty_box'] ?? 0));
            }
        }

        // Save back to JSON
        file_put_contents(public_path('new-products.json'), json_encode(array_values($processed), JSON_PRETTY_PRINT));

        return response()->json(['message' => 'Products processed successfully', 'count' => count($processed)]);
    }

    /**
     * Resolve and format variant attributes with descriptive labels.
     */
    private function resolveVariantAttributes(string $variantRaw): string
    {
        if (str_contains($variantRaw, ':')) {
            return $variantRaw;
        }

        $variantSegments = explode('-', $variantRaw);
        $formattedSegments = [];
        foreach ($variantSegments as $segment) {
            $clean = trim($segment);
            if (empty($clean)) {
                continue;
            }

            $lower = strtolower($clean);
            if (str_contains($lower, 'version') || in_array($lower, ['tpd', 'uk', 'standard'])) {
                $formattedSegments[] = "Version: $clean";
            } elseif (str_contains($lower, 'ohm')) {
                $formattedSegments[] = "Resistance: $clean";
            } elseif (preg_match('/\d+ml/i', $clean)) {
                $formattedSegments[] = "Capacity: $clean";
            } elseif (str_contains($lower, 'pack')) {
                $formattedSegments[] = "Pack Size: $clean";
            } else {
                $formattedSegments[] = "Color: $clean";
            }
        }

        return implode('; ', $formattedSegments);
    }
}
