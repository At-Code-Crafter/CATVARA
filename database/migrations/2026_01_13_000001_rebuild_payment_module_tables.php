<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * DROP OLD TABLES (if they exist)
         * Careful: Only run on fresh DB or backup first!
         */
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');

        /**
         * PAYMENT STATUSES
         */
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PENDING, CONFIRMED, FAILED, CANCELLED, REFUNDED
            $table->string('name');
            $table->boolean('is_final')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * PAYMENT METHODS
         * Master list per company
         */
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('company_id');

            $table->string('code');        // CASH, CARD, BANK_TRANSFER, CHEQUE, STRIPE, PAYPAL
            $table->string('name');        // Cash, Card, Bank Transfer
            $table->string('type');        // CASH, CARD, GATEWAY, BANK, WALLET, CREDIT

            $table->boolean('is_active')->default(true);
            $table->boolean('allow_refund')->default(true);
            $table->boolean('requires_reference')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'pm_company_code_unique');
        });

        /**
         * PAYMENTS (FINANCIAL EVENT)
         * One record = one real-world money movement
         */
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('status_id');

            // Auto-generated payment number
            $table->string('payment_number')->unique();

            // Source channel
            $table->enum('source', ['WEB', 'POS', 'MANUAL', 'API'])->default('MANUAL');

            // Direction: IN = received, OUT = refund/return
            $table->enum('direction', ['IN', 'OUT'])->default('IN');

            /**
             * Currency & amounts
             */
            $table->unsignedBigInteger('currency_id');
            $table->decimal('amount', 18, 6);              // Amount in payment currency
            $table->decimal('exchange_rate', 18, 8)->default(1.00000000);
            $table->decimal('base_amount', 18, 6);         // Converted to company base currency

            // Track unallocated amount for advance payments
            $table->decimal('unallocated_amount', 18, 6)->default(0);

            /**
             * Reference & metadata
             */
            $table->string('reference')->nullable();       // Bank ref, cheque no, transaction ID
            $table->text('description')->nullable();       // Notes/remarks

            /**
             * Gateway data (for online payments)
             */
            $table->string('gateway_reference')->nullable();
            $table->json('gateway_payload')->nullable();

            /**
             * Dates & audit
             */
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();  // Who received (cashier)
            $table->unsignedBigInteger('created_by');               // Who recorded
            $table->unsignedBigInteger('confirmed_by')->nullable(); // Who approved
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status_id'], 'pay_company_status_idx');
            $table->index(['company_id', 'customer_id'], 'pay_company_customer_idx');
            $table->index(['company_id', 'source'], 'pay_company_source_idx');
        });

        /**
         * PAYMENT APPLICATIONS (Allocation to documents)
         * One payment → many applications
         */
        Schema::create('payment_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('payment_id');

            /**
             * Document being paid (Order, Invoice, CreditNote)
             */
            $table->string('paymentable_type');
            $table->unsignedBigInteger('paymentable_id');

            /**
             * Currency & amounts
             */
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('document_currency_id')->nullable();
            $table->decimal('amount', 18, 6);              // Applied in payment currency
            $table->decimal('converted_amount', 18, 6)->nullable();
            $table->decimal('exchange_rate', 18, 8)->default(1.00000000);
            $table->decimal('base_amount', 18, 6);         // In base currency

            /**
             * Audit
             */
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('applied_by');
            $table->timestamp('applied_at')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['company_id', 'paymentable_type', 'paymentable_id'],
                'pa_company_paymentable_idx'
            );
            $table->index(['payment_id'], 'pa_payment_idx');
        });

        /**
         * FOREIGN KEYS
         */
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreign('company_id', 'pm_company_fk')
                ->references('id')->on('companies')
                ->cascadeOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('company_id', 'pay_company_fk')
                ->references('id')->on('companies')
                ->cascadeOnDelete();

            $table->foreign('customer_id', 'pay_customer_fk')
                ->references('id')->on('customers')
                ->nullOnDelete();

            $table->foreign('payment_method_id', 'pay_method_fk')
                ->references('id')->on('payment_methods')
                ->restrictOnDelete();

            $table->foreign('status_id', 'pay_status_fk')
                ->references('id')->on('payment_statuses')
                ->restrictOnDelete();

            $table->foreign('currency_id', 'pay_currency_fk')
                ->references('id')->on('currencies')
                ->restrictOnDelete();

            $table->foreign('received_by', 'pay_receiver_fk')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('created_by', 'pay_creator_fk')
                ->references('id')->on('users')
                ->restrictOnDelete();

            $table->foreign('confirmed_by', 'pay_confirmer_fk')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        Schema::table('payment_applications', function (Blueprint $table) {
            $table->foreign('company_id', 'pa_company_fk')
                ->references('id')->on('companies')
                ->cascadeOnDelete();

            $table->foreign('payment_id', 'pa_payment_fk')
                ->references('id')->on('payments')
                ->cascadeOnDelete();

            $table->foreign('currency_id', 'pa_currency_fk')
                ->references('id')->on('currencies')
                ->restrictOnDelete();

            $table->foreign('applied_by', 'pa_applier_fk')
                ->references('id')->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_applications', function (Blueprint $table) {
            $table->dropForeign('pa_company_fk');
            $table->dropForeign('pa_payment_fk');
            $table->dropForeign('pa_currency_fk');
            $table->dropForeign('pa_applier_fk');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('pay_company_fk');
            $table->dropForeign('pay_customer_fk');
            $table->dropForeign('pay_method_fk');
            $table->dropForeign('pay_status_fk');
            $table->dropForeign('pay_currency_fk');
            $table->dropForeign('pay_receiver_fk');
            $table->dropForeign('pay_creator_fk');
            $table->dropForeign('pay_confirmer_fk');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign('pm_company_fk');
        });

        Schema::dropIfExists('payment_applications');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payment_statuses');
    }
};
