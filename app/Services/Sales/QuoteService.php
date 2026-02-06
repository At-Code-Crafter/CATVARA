<?php

namespace App\Services\Sales;

use App\Models\Sales\{Quote, QuoteItem, QuoteStatus};
use App\Services\Common\DocumentNumberService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        protected DocumentNumberService $docService,
        protected SalesCalculationService $calcService,
        protected SalesDocumentService $salesDocService
    ) {}

    public function createDraft(array $data): Quote
    {
        $statusId = QuoteStatus::where('code', 'DRAFT')->value('id');

        $termSnapshot = $this->salesDocService->resolvePaymentTermSnapshot($data['payment_term_id'] ?? null);

        return Quote::create([
            'uuid' => Str::uuid(),
            'company_id' => $data['company_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'status_id' => $statusId,
            'quote_number' => $this->docService->generate(
                companyId: $data['company_id'],
                documentType: 'QUOTE',
                channel: 'SALES',
                year: now()->year
            ),
            
            'currency_id' => $data['currency_id'],
            
            'payment_term_id' => $termSnapshot['payment_term_id'],
            'payment_term_name' => $termSnapshot['payment_term_name'],
            'payment_due_days' => $termSnapshot['payment_due_days'],
            
            'created_by' => $data['user_id'] ?? Auth::id(),
            'valid_until' => Carbon::now()->addDays(15),
        ]);
    }

    /**
     * Update quote totals from items
     */
    public function refreshTotals(Quote $quote): void
    {
        $quote->load(['items', 'customer']);

        $itemsPayload = $quote->items->map(function ($item) {
            return [
                'type' => $item->product_variant_id ? 'variant' : 'custom',
                'variant_id' => $item->productVariant?->uuid,
                'custom_name' => $item->product_name,
                'unit_price' => $item->unit_price,
                'qty' => $item->quantity,
                'tax_group_id' => $item->tax_group_id,
                'discount_percent' => $item->discount_percent ?? 0,
            ];
        })->toArray();

        $calc = $this->calcService->calculate($quote->company_id, [
            'items' => $itemsPayload,
            'customer_id' => $quote->customer_id,
            'tax_group_id' => $quote->tax_group_id,
        ]);

        $quote->update([
            'subtotal' => $calc['subtotal'],
            'tax_total' => $calc['tax_total'],
            'grand_total' => $calc['grand_total'],
        ]);
    }
}
