<?php

namespace App\Services\Sales;

use App\Models\Pricing\Currency;
use App\Models\Accounting\PaymentTerm;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class SalesDocumentService
{
    /**
     * Resolve Currency ID from a given code.
     */
    public function resolveCurrencyId(string $code): int
    {
        $code = strtoupper(trim($code));
        $currency = Currency::where('code', $code)->first();

        if (!$currency) {
            throw new \Exception("Currency not found for code: {$code}");
        }

        return (int) $currency->id;
    }

    /**
     * Resolve a snapshot of Payment Term details.
     */
    public function resolvePaymentTermSnapshot(?int $paymentTermId): array
    {
        if (!$paymentTermId) {
            return [
                'payment_term_id' => null,
                'payment_term_name' => null,
                'payment_due_days' => 0,
            ];
        }

        $term = PaymentTerm::find($paymentTermId);

        if (!$term) {
            return [
                'payment_term_id' => null,
                'payment_term_name' => null,
                'payment_due_days' => 0,
            ];
        }

        return [
            'payment_term_id' => (int) $term->id,
            'payment_term_name' => (string) $term->name,
            'payment_due_days' => (int) ($term->due_days ?? 0),
        ];
    }

    /**
     * Update address snapshots for a sales document (Order, Quote, etc.)
     */
    public function syncAddressSnapshots(Model $document, Customer $billTo, ?Customer $shipTo = null): void
    {
        $shipTo = $shipTo ?? $billTo;

        $document->addresses()->updateOrCreate(
            ['type' => 'BILLING'],
            $this->mapCustomerToAddressArray($billTo)
        );

        $document->addresses()->updateOrCreate(
            ['type' => 'SHIPPING'],
            $this->mapCustomerToAddressArray($shipTo)
        );
    }

    /**
     * Map customer data to address fields for snapshotting.
     */
    protected function mapCustomerToAddressArray(Customer $customer): array
    {
        $address = $customer->address;

        return [
            'company_id' => $customer->company_id,
            'address_line_1' => $address?->address_line_1 ?? '',
            'address_line_2' => $address?->address_line_2 ?? null,
            'city' => $address?->city ?? null,
            'state_id' => $address?->state_id ?? null,
            'zip_code' => $address?->zip_code ?? '',
            'country_id' => $address?->country_id ?? null,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'name' => $customer->legal_name ?? $customer->display_name,
            'tax_number' => $customer->tax_number,
        ];
    }
}
