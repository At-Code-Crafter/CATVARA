<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * QUOTE STATUSES
         */
        if (! Schema::hasTable('quote_statuses')) {
            Schema::create('quote_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // DRAFT, SENT, ACCEPTED, REJECTED, EXPIRED, CONVERTED
                $table->string('name');
                $table->boolean('is_final')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        /**
         * QUOTES (HEADER)
         */
        if (! Schema::hasTable('quotes')) {
            Schema::create('quotes', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();

                $table->unsignedBigInteger('company_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('shipping_customer_id')->nullable();
                $table->unsignedBigInteger('status_id');

                $table->string('quote_number');

                $table->unsignedBigInteger('currency_id');
                $table->unsignedBigInteger('base_currency_id')->nullable();
                $table->decimal('fx_rate', 18, 10)->default(1);
                $table->unsignedBigInteger('tax_group_id')->nullable();

                // Payment term snapshot
                $table->unsignedBigInteger('payment_term_id')->nullable();
                $table->string('payment_term_name')->nullable();
                $table->integer('payment_due_days')->nullable();

                // Validity
                $table->date('valid_until')->nullable();

                // Totals snapshot
                $table->decimal('subtotal', 18, 6)->default(0);
                $table->decimal('tax_total', 18, 6)->default(0);
                $table->decimal('discount_total', 18, 6)->default(0);
                $table->decimal('shipping_total', 18, 6)->default(0);
                $table->decimal('shipping_tax_total', 18, 6)->default(0);
                $table->decimal('global_discount_percent', 5, 2)->default(0);
                $table->decimal('global_discount_amount', 18, 6)->default(0);
                $table->decimal('rounding_total', 18, 6)->default(0);
                $table->decimal('grand_total', 18, 6)->default(0);

                // Timestamps
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();

                // Order reference (when converted)
                $table->unsignedBigInteger('order_id')->nullable();

                $table->text('notes')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'status_id'], 'quote_company_status_idx');
                $table->unique(['company_id', 'quote_number'], 'quote_company_number_unique');

                // Foreign keys
                $table->foreign('company_id', 'quote_company_fk')
                    ->references('id')->on('companies')->cascadeOnDelete();

                $table->foreign('customer_id', 'quote_customer_fk')
                    ->references('id')->on('customers')->nullOnDelete();

                $table->foreign('shipping_customer_id', 'quote_shipping_customer_fk')
                    ->references('id')->on('customers')->nullOnDelete();

                $table->foreign('status_id', 'quote_status_fk')
                    ->references('id')->on('quote_statuses')->restrictOnDelete();

                $table->foreign('currency_id', 'quote_currency_fk')
                    ->references('id')->on('currencies')->restrictOnDelete();

                $table->foreign('base_currency_id', 'quote_base_currency_fk')
                    ->references('id')->on('currencies')->nullOnDelete();

                $table->foreign('tax_group_id', 'quote_tax_group_fk')
                    ->references('id')->on('tax_groups')->nullOnDelete();

                $table->foreign('payment_term_id', 'quote_payment_term_fk')
                    ->references('id')->on('payment_terms')->nullOnDelete();

                $table->foreign('created_by', 'quote_user_fk')
                    ->references('id')->on('users')->nullOnDelete();
            });
        }

        /**
         * QUOTE ITEMS
         */
        if (! Schema::hasTable('quote_items')) {
            Schema::create('quote_items', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('quote_id');
                $table->unsignedBigInteger('product_variant_id');

                // Snapshot
                $table->boolean('is_custom')->default(false);
                $table->string('custom_sku')->nullable();
                $table->string('product_name');
                $table->string('variant_description')->nullable();

                $table->decimal('unit_price', 18, 6);
                $table->integer('quantity');
                $table->decimal('line_subtotal', 18, 6)->default(0);
                $table->decimal('discount_amount', 18, 6)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('line_discount_total', 18, 6)->default(0);

                $table->unsignedBigInteger('tax_group_id')->nullable();
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('tax_amount', 18, 6)->default(0);

                $table->decimal('line_total', 18, 6);

                $table->timestamps();

                $table->unique(['quote_id', 'product_variant_id'], 'quote_item_unique');
                $table->index(['is_custom'], 'qi_is_custom_idx');

                $table->foreign('quote_id', 'qi_quote_fk')
                    ->references('id')->on('quotes')->cascadeOnDelete();

                $table->foreign('product_variant_id', 'qi_variant_fk')
                    ->references('id')->on('product_variants')->restrictOnDelete();

                $table->foreign('tax_group_id', 'qi_tax_group_fk')
                    ->references('id')->on('tax_groups')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('quote_statuses');
    }
};
