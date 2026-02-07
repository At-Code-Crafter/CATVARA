<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * INVOICE STATUSES
         */
        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // DRAFT, ISSUED, PAID, PARTIALLY_PAID, VOIDED
            $table->string('name');
            $table->boolean('is_final')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * INVOICES (HEADER)
         */
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('company_id');
            $table->string('invoice_number');

            /**
             * Customer relations
             */
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shipping_customer_id')->nullable();

            /**
             * Statuses
             */
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('payment_status_id')->nullable();

            /**
             * Origin tracking
             */
            $table->string('source_type')->nullable(); // Order model
            $table->unsignedBigInteger('source_id')->nullable(); // Order ID

            /**
             * Currency snapshot
             */
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('base_currency_id')->nullable();
            $table->decimal('fx_rate', 18, 10)->default(1);

            /**
             * Payment term snapshot
             */
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('payment_term_name', 100)->nullable();
            $table->integer('payment_due_days')->nullable();
            $table->date('due_date')->nullable();

            /**
             * Totals snapshot
             */
            $table->decimal('subtotal', 18, 6)->default(0);
            $table->decimal('discount_total', 18, 6)->default(0);
            $table->decimal('global_discount_percent', 10, 2)->default(0);
            $table->decimal('global_discount_amount', 18, 6)->default(0);
            $table->decimal('shipping_total', 18, 6)->default(0);
            $table->decimal('shipping_tax_total', 18, 6)->default(0);
            $table->decimal('tax_total', 18, 6)->default(0);
            $table->decimal('rounding_total', 18, 6)->default(0);
            $table->decimal('grand_total', 18, 6)->default(0);

            /**
             * Accounting Posting fields
             */
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status_id'], 'inv_company_status_idx');
            $table->unique(['company_id', 'invoice_number'], 'inv_company_number_unique');
            $table->index(['company_id', 'customer_id'], 'inv_company_customer_idx');
            $table->index(['company_id', 'posted_at'], 'inv_company_posted_at_idx');
        });

        /**
         * INVOICE ITEMS
         */
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();

            $table->boolean('is_custom')->default(false);
            $table->string('custom_sku')->nullable();

            $table->string('product_name');
            $table->string('description')->nullable();
            $table->string('variant_description')->nullable();

            $table->decimal('unit_price', 18, 6);
            $table->integer('quantity');

            $table->decimal('line_subtotal', 18, 6)->default(0);
            $table->decimal('discount_amount', 18, 6)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('line_discount_total', 18, 6)->default(0);

            $table->unsignedBigInteger('tax_group_id')->nullable();
            $table->decimal('tax_rate', 7, 4)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);

            $table->decimal('line_total', 18, 6)->default(0);

            $table->timestamps();

            $table->index(['invoice_id'], 'inv_item_idx');
        });

        /**
         * INVOICE ADDRESSES
         */
        Schema::create('invoice_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type', 20)->default('BILLING'); // BILLING, SHIPPING

            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();

            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();

            $table->string('zip_code', 20)->nullable();

            $table->timestamps();

            $table->index(['invoice_id'], 'inv_addr_idx');
        });

        /**
         * FOREIGN KEYS
         */
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('company_id', 'inv_company_fk')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('customer_id', 'inv_customer_fk')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('shipping_customer_id', 'inv_shipping_customer_fk')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('status_id', 'inv_status_fk')->references('id')->on('invoice_statuses')->restrictOnDelete();
            $table->foreign('payment_status_id', 'inv_payment_status_fk')->references('id')->on('payment_statuses')->nullOnDelete();
            $table->foreign('currency_id', 'inv_currency_fk')->references('id')->on('currencies')->restrictOnDelete();
            $table->foreign('payment_term_id', 'inv_term_fk')->references('id')->on('payment_terms')->nullOnDelete();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('invoice_id', 'ii_invoice_fk')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('tax_group_id', 'ii_tax_fk')->references('id')->on('tax_groups')->nullOnDelete();
        });

        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->foreign('invoice_id', 'ia_invoice_fk')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('state_id', 'ia_state_fk')->references('id')->on('states')->nullOnDelete();
            $table->foreign('country_id', 'ia_country_fk')->references('id')->on('countries')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_addresses');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_statuses');
    }
};
