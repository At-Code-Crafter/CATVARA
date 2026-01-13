<?php

namespace App\Services\Accounting;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentApplication;
use App\Models\Accounting\PaymentMethod;
use App\Models\Accounting\PaymentStatus;
use App\Models\Company\Company;
use App\Models\Sales\Order;

class PaymentService
{
    /**
     * Generate unique payment number
     */
    public function generatePaymentNumber(int $companyId): string
    {
        $prefix = 'PAY';
        $year = date('Y');

        $lastPayment = Payment::where('company_id', $companyId)
            ->where('payment_number', 'like', "{$prefix}-{$year}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(payment_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $nextNumber);
    }

    /**
     * Create a new payment
     */
    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $this->validateCreatePayload($data);

            $company = Company::findOrFail($data['company_id']);

            // Get payment method
            $method = PaymentMethod::where('company_id', $company->id)
                ->where('id', $data['payment_method_id'])
                ->where('is_active', true)
                ->first();

            if (!$method) {
                throw new \RuntimeException('Payment method not found or inactive.');
            }

            if ($method->requires_reference && empty($data['reference'])) {
                throw new \RuntimeException('Payment reference is required for this method.');
            }

            // Get or set default status
            $status = PaymentStatus::where('code', $data['status'] ?? 'CONFIRMED')->first();
            if (!$status) {
                $status = PaymentStatus::where('code', 'CONFIRMED')->first();
            }

            // Calculate base amount
            $amount = (string) $data['amount'];
            $exchangeRate = (string) ($data['exchange_rate'] ?? '1.00000000');
            $baseAmount = bcmul($amount, $exchangeRate, 6);

            $payment = Payment::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $company->id,
                'customer_id' => $data['customer_id'] ?? null,
                'payment_method_id' => $method->id,
                'status_id' => $status->id,
                'payment_number' => $this->generatePaymentNumber($company->id),
                'source' => $data['source'] ?? 'MANUAL',
                'direction' => $data['direction'] ?? 'IN',
                'currency_id' => $data['currency_id'],
                'amount' => $amount,
                'exchange_rate' => $exchangeRate,
                'base_amount' => $baseAmount,
                'unallocated_amount' => $amount, // Initially all unallocated
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'gateway_reference' => $data['gateway_reference'] ?? null,
                'gateway_payload' => $data['gateway_payload'] ?? null,
                'paid_at' => $data['paid_at'] ?? Carbon::now(),
                'received_by' => $data['received_by'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'confirmed_by' => $status->code === 'CONFIRMED' ? auth()->id() : null,
                'confirmed_at' => $status->code === 'CONFIRMED' ? Carbon::now() : null,
            ]);

            return $payment;
        });
    }

    /**
     * Update an existing payment
     */
    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            if (!$payment->canBeEdited()) {
                throw new \RuntimeException('This payment cannot be edited.');
            }

            // Recalculate base amount if amount or rate changed
            if (isset($data['amount']) || isset($data['exchange_rate'])) {
                $amount = (string) ($data['amount'] ?? $payment->amount);
                $exchangeRate = (string) ($data['exchange_rate'] ?? $payment->exchange_rate);
                $data['base_amount'] = bcmul($amount, $exchangeRate, 6);
                $data['unallocated_amount'] = $amount; // Reset unallocated
            }

            $payment->update($data);

            return $payment->fresh();
        });
    }

    /**
     * Confirm a pending payment
     */
    public function confirm(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            if ($payment->isConfirmed()) {
                throw new \RuntimeException('Payment is already confirmed.');
            }

            $confirmedStatus = PaymentStatus::where('code', 'CONFIRMED')->first();
            if (!$confirmedStatus) {
                throw new \RuntimeException('Confirmed status not found.');
            }

            $payment->update([
                'status_id' => $confirmedStatus->id,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => Carbon::now(),
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Cancel a payment
     */
    public function cancel(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            if (!$payment->canBeCancelled()) {
                throw new \RuntimeException('This payment cannot be cancelled.');
            }

            // Check if any applications exist
            if ($payment->applications()->count() > 0) {
                throw new \RuntimeException('Cannot cancel payment with existing applications. Remove applications first.');
            }

            $cancelledStatus = PaymentStatus::where('code', 'CANCELLED')->first();
            if (!$cancelledStatus) {
                throw new \RuntimeException('Cancelled status not found.');
            }

            $payment->update([
                'status_id' => $cancelledStatus->id,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Apply payment to a document (Order, Invoice, etc.)
     */
    public function apply(Payment $payment, array $data): PaymentApplication
    {
        return DB::transaction(function () use ($payment, $data) {
            $this->validateApplicationPayload($data);

            if (!$payment->isConfirmed()) {
                throw new \RuntimeException('Cannot apply an unconfirmed payment.');
            }

            $applyAmount = (string) $data['amount'];

            if (bccomp($applyAmount, '0', 6) <= 0) {
                throw new \RuntimeException('Application amount must be greater than zero.');
            }

            // Check unallocated amount
            $unallocated = $payment->calculateUnallocatedAmount();
            if (bccomp($applyAmount, $unallocated, 6) > 0) {
                throw new \RuntimeException("Cannot apply more than unallocated amount ({$unallocated}).");
            }

            // Calculate base amount
            $exchangeRate = (string) ($data['exchange_rate'] ?? $payment->exchange_rate);
            $baseAmount = bcmul($applyAmount, $exchangeRate, 6);

            $application = PaymentApplication::create([
                'uuid' => (string) Str::uuid(),
                'company_id' => $payment->company_id,
                'payment_id' => $payment->id,
                'paymentable_type' => $data['paymentable_type'],
                'paymentable_id' => $data['paymentable_id'],
                'currency_id' => $payment->currency_id,
                'amount' => $applyAmount,
                'exchange_rate' => $exchangeRate,
                'base_amount' => $baseAmount,
                'notes' => $data['notes'] ?? null,
                'applied_by' => auth()->id(),
                'applied_at' => $data['applied_at'] ?? Carbon::now(),
            ]);

            // Update unallocated amount on payment
            $payment->update([
                'unallocated_amount' => $payment->calculateUnallocatedAmount(),
            ]);

            // Update order payment status if applicable
            if ($data['paymentable_type'] === Order::class) {
                $this->updateOrderPaymentStatus($data['paymentable_id']);
            }

            return $application;
        });
    }

    /**
     * Remove a payment application
     */
    public function removeApplication(PaymentApplication $application): void
    {
        DB::transaction(function () use ($application) {
            $payment = $application->payment;
            $paymentableType = $application->paymentable_type;
            $paymentableId = $application->paymentable_id;

            $application->delete();

            // Update unallocated amount on payment
            $payment->update([
                'unallocated_amount' => $payment->calculateUnallocatedAmount(),
            ]);

            // Update order payment status if applicable
            if ($paymentableType === Order::class) {
                $this->updateOrderPaymentStatus($paymentableId);
            }
        });
    }

    /**
     * Apply payment to multiple documents
     */
    public function applyMany(Payment $payment, array $applications): array
    {
        $results = [];
        foreach ($applications as $appData) {
            $results[] = $this->apply($payment, $appData);
        }
        return $results;
    }

    /**
     * Create payment and immediately apply to document
     */
    public function createAndApply(array $paymentData, array $applicationData): array
    {
        return DB::transaction(function () use ($paymentData, $applicationData) {
            $payment = $this->create($paymentData);
            $application = $this->apply($payment, $applicationData);

            return [
                'payment' => $payment,
                'application' => $application,
            ];
        });
    }

    /**
     * Get total payments received for a document
     */
    public function getTotalPaidForDocument(string $type, int $id): string
    {
        return (string) PaymentApplication::where('paymentable_type', $type)
            ->where('paymentable_id', $id)
            ->whereHas('payment', fn($q) => $q->confirmed())
            ->sum('amount');
    }

    /**
     * Get outstanding balance for a document
     */
    public function getOutstandingBalance(string $type, int $id): string
    {
        $document = $type::findOrFail($id);
        $totalPaid = $this->getTotalPaidForDocument($type, $id);
        $grandTotal = (string) $document->grand_total;

        return bcsub($grandTotal, $totalPaid, 6);
    }

    /**
     * Update order payment status based on applications
     */
    protected function updateOrderPaymentStatus(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        $totalPaid = $this->getTotalPaidForDocument(Order::class, $orderId);
        $grandTotal = (string) $order->grand_total;

        if (bccomp($totalPaid, '0', 6) === 0) {
            $status = 'UNPAID';
        } elseif (bccomp($totalPaid, $grandTotal, 6) >= 0) {
            $status = 'PAID';
        } else {
            $status = 'PARTIAL';
        }

        $order->update(['payment_status' => $status]);
    }

    /**
     * Validate create payload
     */
    protected function validateCreatePayload(array $data): void
    {
        $required = ['company_id', 'payment_method_id', 'currency_id', 'amount'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' is required.");
            }
        }

        if ((float) $data['amount'] <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero.');
        }
    }

    /**
     * Validate application payload
     */
    protected function validateApplicationPayload(array $data): void
    {
        $required = ['paymentable_type', 'paymentable_id', 'amount'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' is required.");
            }
        }
    }

    /**
     * Get payments summary for a company
     */
    public function getSummary(int $companyId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Payment::forCompany($companyId)->confirmed();

        if ($dateFrom) {
            $query->whereDate('paid_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('paid_at', '<=', $dateTo);
        }

        return [
            'total_received' => (string) (clone $query)->incoming()->sum('base_amount'),
            'total_refunded' => (string) (clone $query)->outgoing()->sum('base_amount'),
            'total_unallocated' => (string) (clone $query)->sum('unallocated_amount'),
            'payment_count' => (clone $query)->count(),
        ];
    }
}
