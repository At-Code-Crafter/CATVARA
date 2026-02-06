<?php

namespace App\Repositories\Customer;

use App\Models\Common\Address;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CustomerRepository
{
    /**
     * Get base query for customers with necessary joins.
     */
    public function query(int $companyId): Builder
    {
        return Customer::query()
            ->leftJoin('tax_groups as tg', 'tg.id', '=', 'customers.tax_group_id')
            ->select(
                'customers.id',
                'customers.uuid',
                'customers.display_name',
                'customers.type',
                'customers.email',
                'customers.phone',
                'customers.legal_name',
                'customers.is_active',
                'customers.percentage_discount',
                'customers.is_tax_exempt',
                'customers.tax_group_id',
                'tg.name as tax_group_name',
                'customers.created_at'
            )
            ->where('customers.company_id', $companyId);
    }

    /**
     * Find customer by ID and company with relations.
     */
    public function findById(int $id, int $companyId, array $relations = []): Customer
    {
        return Customer::where('company_id', $companyId)
            ->when(! empty($relations), fn ($q) => $q->with($relations))
            ->findOrFail($id);
    }

    /**
     * Create a new customer.
     */
    public function create(array $data): Customer
    {
        if (empty($data['uuid'])) {
            $data['uuid'] = (string) Str::uuid();
        }

        return Customer::create($data);
    }

    /**
     * Update customer.
     */
    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }

    /**
     * Delete customer.
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Update or create associated address.
     */
    public function updateOrCreateAddress(Customer $customer, array $data): void
    {
        Address::updateOrCreate([
            'company_id' => $customer->company_id,
            'addressable_id' => $customer->id,
            'addressable_type' => Customer::class,
        ], [
            'address_line_1' => $data['address_line_1'] ?? '',
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'zip_code' => $data['zip_code'] ?? '',
        ]);
    }

    /**
     * Get statistics for a specific customer.
     */
    public function getStats(Customer $customer): array
    {
        return [
            'orders_count' => $customer->orders()->count(),
            'orders_draft' => $customer->orders()->whereHas('status', fn ($q) => $q->where('code', 'DRAFT'))->count(),
            'orders_completed' => $customer->orders()->whereHas('status', fn ($q) => $q->where('code', 'FULFILLED'))->count(),
            'invoices_paid' => \App\Models\Accounting\Invoice::where('customer_id', $customer->id)
                ->whereHas('status', fn ($q) => $q->where('code', 'PAID'))->count(),
            'invoices_unpaid' => \App\Models\Accounting\Invoice::where('customer_id', $customer->id)
                ->whereHas('status', fn ($q) => $q->whereIn('code', ['ISSUED', 'PARTIALLY_PAID', 'OVERDUE']))->count(),
            'total_spent' => (float) $customer->orders()
                ->whereHas('status', fn ($q) => $q->where('code', 'FULFILLED'))->sum('grand_total'),
            'total_overdue' => (float) \App\Models\Accounting\Invoice::where('customer_id', $customer->id)
                ->whereHas('status', fn ($q) => $q->where('code', 'OVERDUE'))->sum('grand_total'),
        ];
    }

    /**
     * Get dashboard stats for a company.
     */
    public function getDashboardStats(int $companyId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $baseQuery = Customer::where('company_id', $companyId);

        if ($dateFrom && $dateTo) {
            $baseQuery->whereBetween('created_at', [
                $dateFrom.' 00:00:00',
                $dateTo.' 23:59:59',
            ]);
        }

        return [
            'all_customers' => (clone $baseQuery)->count(),
            'active_customers' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive_customers' => (clone $baseQuery)->where('is_active', false)->count(),
            'company_customers' => (clone $baseQuery)->where('type', 'COMPANY')->count(),
            'individual_customers' => (clone $baseQuery)->where('type', 'INDIVIDUAL')->count(),
        ];
    }

    /**
     * Search customers for Select2.
     */
    public function search(int $companyId, string $search = '', int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('display_name', 'LIKE', "%{$search}%")
                        ->orWhere('legal_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('display_name')
            ->paginate($perPage, ['id', 'display_name', 'email', 'phone']);
    }

    /**
     * Get basic list of customers for a company.
     */
    public function getAllForCompany(int $companyId): \Illuminate\Support\Collection
    {
        return Customer::where('company_id', $companyId)
            ->where('is_active', true)
            ->select('id', 'uuid', 'display_name', 'email', 'phone', 'type', 'legal_name')
            ->orderBy('display_name')
            ->get();
    }

    /**
     * Find customer by customer_code.
     */
    public function findByCode(int $companyId, string $code): ?Customer
    {
        return Customer::where('company_id', $companyId)
            ->where('customer_code', $code)
            ->first();
    }

    /**
     * Find customer by email.
     */
    public function findByEmail(int $companyId, string $email): ?Customer
    {
        return Customer::where('company_id', $companyId)
            ->where('email', $email)
            ->first();
    }

    /**
     * Find customer by email excluding specific ID.
     */
    public function findByEmailExcluding(int $companyId, string $email, int $excludeId): ?Customer
    {
        return Customer::where('company_id', $companyId)
            ->where('email', $email)
            ->where('id', '!=', $excludeId)
            ->first();
    }

    /**
     * Find customer by phone or email (for POS lookup).
     */
    public function findByContact(int $companyId, ?string $phone = null, ?string $email = null): ?Customer
    {
        $q = Customer::where('company_id', $companyId)->where('is_active', true);

        if ($phone) {
            $q->where('phone', $phone);
        }

        if ($email) {
            $q->where('email', $email);
        }

        return $q->first();
    }
}
