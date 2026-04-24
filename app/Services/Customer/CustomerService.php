<?php

namespace App\Services\Customer;

use App\Models\Customer\Customer;
use App\Repositories\Customer\CustomerRepository;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Create a new customer with address.
     */
    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = $this->customerRepository->create([
                'company_id' => $data['company_id'],
                'type' => $data['type'] ?? 'INDIVIDUAL',
                'display_name' => $data['display_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'legal_name' => $data['legal_name'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'percentage_discount' => $data['percentage_discount'] ?? 0,
                'timezone' => $data['timezone'] ?? null,
                'tax_group_id' => $data['tax_group_id'] ?? null,
                'is_tax_exempt' => (bool) ($data['is_tax_exempt'] ?? false),
                'tax_exempt_reason' => $data['tax_exempt_reason'] ?? null,
            ]);

            if ($this->hasAddressData($data)) {
                $this->customerRepository->updateOrCreateAddress($customer, $data);
            }

            return $customer;
        });
    }

    /**
     * Update an existing customer.
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            $this->customerRepository->update($customer, [
                'type' => $data['type'] ?? $customer->type,
                'display_name' => $data['display_name'] ?? $customer->display_name,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'legal_name' => $data['legal_name'] ?? null,
                'tax_number' => $data['tax_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_active' => $data['is_active'] ?? $customer->is_active,
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'percentage_discount' => $data['percentage_discount'] ?? 0,
                'timezone' => $data['timezone'] ?? null,
                'tax_group_id' => $data['tax_group_id'] ?? null,
                'is_tax_exempt' => (bool) ($data['is_tax_exempt'] ?? false),
                'tax_exempt_reason' => $data['tax_exempt_reason'] ?? null,
            ]);

            $this->customerRepository->updateOrCreateAddress($customer, $data);

            return $customer;
        });
    }

    /**
     * Get customer statistics for detail view.
     */
    public function getCustomerStats(Customer $customer): array
    {
        return $this->customerRepository->getStats($customer);
    }

    /**
     * Get dashboard stats for a company.
     */
    public function getDashboardStats(int $companyId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->customerRepository->getDashboardStats($companyId, $dateFrom, $dateTo);
    }

    /**
     * Determine if a row is a new customer or an update to an existing one.
     * Returns ['type' => 'new'|'update'|'error', 'customer' => ?Customer, 'errors' => array]
     */
    public function resolveCustomerForImport(int $companyId, array $mappedRow): array
    {
        $email = ! empty($mappedRow['email']) ? trim($mappedRow['email']) : null;
        $customerCode = ! empty($mappedRow['customer_code']) ? trim($mappedRow['customer_code']) : null;

        $existingByCode = $customerCode ? $this->customerRepository->findByCode($companyId, $customerCode) : null;
        $existingByEmail = $email ? $this->customerRepository->findByEmail($companyId, $email) : null;

        $errors = [];

        // Case 1: No identifiers → New
        if (empty($customerCode) && empty($email)) {
            return ['type' => 'new', 'customer' => null, 'errors' => []];
        }

        // Case 2 & 3: Only email provided
        if (empty($customerCode) && ! empty($email)) {
            if ($existingByEmail) {
                return [
                    'type' => 'error',
                    'customer' => null,
                    'errors' => ['email' => 'Email already exists for another customer'],
                ];
            }

            return ['type' => 'new', 'customer' => null, 'errors' => []];
        }

        // Case 4: Only customer_code provided
        if (! empty($customerCode) && empty($email)) {
            if ($existingByCode) {
                return ['type' => 'update', 'customer' => $existingByCode, 'errors' => []];
            }

            return [
                'type' => 'error',
                'customer' => null,
                'errors' => ['customer_code' => 'Customer code not found'],
            ];
        }

        // Case 5: Both provided
        if (! empty($customerCode) && ! empty($email)) {
            if (! $existingByCode) {
                return [
                    'type' => 'error',
                    'customer' => null,
                    'errors' => ['customer_code' => 'Customer code not found'],
                ];
            }

            // Check if email belongs to someone else
            $otherByEmail = $this->customerRepository->findByEmailExcluding($companyId, $email, $existingByCode->id);
            if ($otherByEmail) {
                return [
                    'type' => 'error',
                    'customer' => null,
                    'errors' => ['email' => 'Email is linked with another customer'],
                ];
            }

            return ['type' => 'update', 'customer' => $existingByCode, 'errors' => []];
        }

        return ['type' => 'new', 'customer' => null, 'errors' => []];
    }

    /**
     * Process a single customer import row.
     */
    public function importCustomer(int $companyId, array $mapped): Customer
    {
        return DB::transaction(function () use ($companyId, $mapped) {
            $resolution = $this->resolveCustomerForImport($companyId, $mapped);

            if ($resolution['type'] === 'error') {
                throw new \Exception('Validation failed for row');
            }

            $customer = $resolution['customer'];
            $isNew = $resolution['type'] === 'new';

            // Resolve Type
            $type = strtoupper($mapped['type'] ?? 'INDIVIDUAL');
            if ($type === 'B2B') {
                $type = 'COMPANY';
            } elseif ($type === 'B2C') {
                $type = 'INDIVIDUAL';
            }
            if (! in_array($type, ['INDIVIDUAL', 'COMPANY'])) {
                $type = $isNew ? 'INDIVIDUAL' : ($customer->type ?? 'INDIVIDUAL');
            }

            // Resolve is_active
            $isActive = $isNew ? true : $customer->is_active;
            if (isset($mapped['is_active'])) {
                $activeValue = strtolower((string) $mapped['is_active']);
                $isActive = in_array($activeValue, ['1', 'true', 'yes', 'active']);
            }

            // Resolve discount
            $discount = 0;
            if (! empty($mapped['percentage_discount'])) {
                $discountValue = str_replace(['%', ' '], '', $mapped['percentage_discount']);
                $discount = is_numeric($discountValue) ? (float) $discountValue : 0;
            }

            $customerData = [
                'company_id' => $companyId,
                'type' => $type,
                'display_name' => $mapped['display_name'],
                'email' => ! empty($mapped['email']) ? trim($mapped['email']) : ($isNew ? null : $customer->email),
                'phone' => $mapped['phone'] ?? ($isNew ? null : $customer->phone),
                'legal_name' => $mapped['legal_name'] ?? ($isNew ? null : $customer->legal_name),
                'tax_number' => $mapped['tax_number'] ?? ($isNew ? null : $customer->tax_number),
                'notes' => $mapped['notes'] ?? ($isNew ? null : $customer->notes),
                'is_active' => $isActive,
                'payment_term_id' => $mapped['payment_term_id'] ?? ($isNew ? null : $customer->payment_term_id),
                'percentage_discount' => $discount,
            ];

            if ($isNew) {
                $customer = $this->customerRepository->create($customerData);
            } else {
                $this->customerRepository->update($customer, $customerData);
            }

            // Address logic
            $hasAddressData = ! empty($mapped['address_line_1']) || ! empty($mapped['city']) || ! empty($mapped['zip_code']);
            if ($hasAddressData) {
                $this->customerRepository->updateOrCreateAddress($customer, $mapped);
            }

            return $customer;
        });
    }

    /**
     * Check if address data is present in the request.
     */
    protected function hasAddressData(array $data): bool
    {
        return array_key_exists('address_line_1', $data) || array_key_exists('country_id', $data);
    }

    /**
     * Find customer by ID and company.
     */
    public function findById(int $id, int $companyId): Customer
    {
        return $this->customerRepository->findById($id, $companyId);
    }

    /**
     * Quick lookup for POS.
     */
    public function findByContact(int $companyId, ?string $phone = null, ?string $email = null): ?Customer
    {
        return $this->customerRepository->findByContact($companyId, $phone, $email);
    }
}
