<?php

namespace Tests\Unit\Services\Sales;

use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductVariant;
use App\Models\Customer\Customer;
use App\Models\Tax\TaxGroup;
use App\Services\Sales\TaxService;
// use Illuminate\Foundation\Testing\RefreshDatabase; // Unit tests extending TestCase might need this if using models, but Mockery is better if possible. 
// However, the service uses models. Let's use validation with real models in an in-memory DB or mocks.
// For simplicity in this setups without factories, I'll mock the models or use simple instances.
use Tests\TestCase; 

class TaxServiceTest extends TestCase
{
    // use RefreshDatabase; // Use this if we need DB, but I'll try to just pass objects.

    public function test_resolve_tax_group_priority()
    {
        $service = new TaxService();

        // Setup IDs
        $itemInputTaxId = 10;
        $variantCatTaxId = 20;
        $customerTaxId = 30;
        $docTaxId = 40;

        // Mock objects (simple instances with properties set)
        
        // 1. Case: Customer is Tax Exempt
        $customerExempt = new Customer();
        $customerExempt->is_tax_exempt = true;
        
        $this->assertNull(
            $service->resolveTaxGroupId(null, null, $customerExempt, $docTaxId),
            'Exempt customer should return null'
        );

        // 2. Case: Item Input present (Highest Priority after exempt)
        $this->assertEquals(
            $itemInputTaxId,
            $service->resolveTaxGroupId($itemInputTaxId, null, null, $docTaxId),
            'Item Input should take precedence'
        );

        // 3. Case: Variant Category present
        // Need to construct the chain variant->product->category->tax_group_id
        $category = new Category();
        $category->tax_group_id = $variantCatTaxId;
        
        $product = new Product();
        $product->setRelation('category', $category);
        
        $variant = new ProductVariant();
        $variant->setRelation('product', $product);

        $customerWithTax = new Customer();
        $customerWithTax->tax_group_id = $customerTaxId;
        $customerWithTax->is_tax_exempt = false;

        $this->assertEquals(
            $variantCatTaxId,
            $service->resolveTaxGroupId(null, $variant, $customerWithTax, $docTaxId),
            'Variant Category should take precedence over Customer and Doc'
        );

        // 4. Case: Document vs Customer (THE ISSUE)
        // Current logic (Fail): Customer > Document
        // Desired logic (Pass): Document > Customer
        
        $result = $service->resolveTaxGroupId(null, null, $customerWithTax, $docTaxId);
        
        // Asserting the CURRENT BROKEN behavior to confirm reproduction (or asserting the DESIRED behavior to fail first)
        // Let's assert the DESIRED behavior, so it fails.
        // We expect $docTaxId (40) but currently implementation gives $customerTaxId (30)
        
        $this->assertEquals(
            $docTaxId, 
            $result,
            'Document Global should take precedence over Customer default'
        );
    }
}
