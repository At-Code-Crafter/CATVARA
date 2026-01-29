<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * ORDER STATUSES
         */
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // DRAFT, CONFIRMED, PARTIALLY_FULFILLED, FULFILLED, CANCELLED
            $table->string('name');
            $table->boolean('is_final')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /**
         * ORDERS (HEADER)
         */
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('company_id');
            $table->string('order_number');

            /**
             * Customer relations (IDs)
             */
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shipping_customer_id')->nullable();

            /**
             * Customer snapshot (CRITICAL: never changes after document created)
             */

            // Use addresses table
            // $table->string('customer_name')->nullable();
            // $table->string('customer_email')->nullable();
            // $table->string('customer_tax_number')->nullable();

            // $table->string('shipping_customer_name')->nullable();
            // $table->string('shipping_customer_email')->nullable();

            /**
             * Statuses
             */
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('payment_status_id');

            /**
             * Origin tracking
             * - source: QUOTE, POS, WEB, MANUAL
             * - source_reference: POS receipt no, website order number, etc.
             */
            $table->string('source')->default('MANUAL');
            $table->string('source_reference', 100)->nullable();

            /**
             * Currency snapshot
             * currency_id = transaction currency (document currency)
             * base_currency_id + fx_rate = snapshot for reporting
             *
             * Convention:
             * base_amount = doc_amount * fx_rate
             */
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('base_currency_id')->nullable();
            $table->decimal('fx_rate', 18, 10)->default(1);

            /**
             * Payment term snapshot (CRITICAL)
             */
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->string('payment_term_name')->nullable();
            $table->integer('payment_due_days')->nullable();
            $table->date('due_date')->nullable();

            /**
             * Totals snapshot
             */
            $table->decimal('subtotal', 18, 6)->default(0);
            $table->decimal('discount_total', 18, 6)->default(0);

            $table->decimal('shipping_total', 18, 6)->default(0);
            $table->decimal('shipping_tax_total', 18, 6)->default(0);

            $table->decimal('tax_total', 18, 6)->default(0);
            $table->decimal('rounding_total', 18, 6)->default(0);
            $table->decimal('grand_total', 18, 6)->default(0);

            /**
             * Payment snapshot (optional caching, can be recalculated from payments later)
             */
            $table->decimal('paid_total', 18, 6)->default(0);
            $table->decimal('refunded_total', 18, 6)->default(0);

            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status_id'], 'order_company_status_idx');
            $table->unique(['company_id', 'order_number'], 'order_company_order_number_unique');
            $table->index(['company_id', 'customer_id'], 'order_company_customer_idx');

            $table->index(['company_id', 'payment_status_id'], 'order_company_payment_status_idx');
            $table->index(['company_id', 'due_date'], 'order_company_due_date_idx');
            $table->index(['company_id', 'confirmed_at'], 'order_company_confirmed_at_idx');
        });

        /**
         * ORDER ITEMS
         */
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');

            /**
             * Nullable to support custom/manual items
             */
            $table->unsignedBigInteger('product_variant_id')->nullable();

            // Manual/custom item support
            $table->boolean('is_custom')->default(false);
            $table->string('custom_sku')->nullable();

            // Snapshot
            $table->string('product_name');
            $table->string('variant_description')->nullable();

            /**
             * Price breakdown snapshot
             */
            $table->decimal('unit_price', 18, 6);
            $table->integer('quantity');
            $table->integer('fulfilled_quantity')->default(0);

            $table->decimal('line_subtotal', 18, 6)->default(0);
            $table->decimal('discount_amount', 18, 6)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('line_discount_total', 18, 6)->default(0);

            /**
             * Tax snapshot
             * Keep only tax_rate & tax_amount for now.
             * Later (when tax_groups exist), we can add tax_group_id references.
             */
            $table->decimal('tax_rate', 7, 4)->default(0);
            $table->decimal('tax_amount', 18, 6)->default(0);

            /**
             * Final line total snapshot
             */
            $table->decimal('line_total', 18, 6)->default(0);

            $table->timestamps();

            $table->index(['order_id'], 'oi_order_idx');
            $table->index(['product_variant_id'], 'oi_variant_idx');
            $table->index(['is_custom'], 'oi_is_custom_idx');
        });

        /**
         * FOREIGN KEYS (PREFIX SAFE)
         */
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('company_id', 'order_company_fk')
                ->references('id')->on('companies')->cascadeOnDelete();

            $table->foreign('customer_id', 'order_customer_fk')
                ->references('id')->on('customers')->nullOnDelete();

            $table->foreign('shipping_customer_id', 'order_shipping_customer_fk')
                ->references('id')->on('customers')->nullOnDelete();

            $table->foreign('status_id', 'order_status_fk')
                ->references('id')->on('order_statuses')->restrictOnDelete();

            $table->foreign('payment_status_id', 'order_payment_status_fk')
                ->references('id')->on('payment_statuses')->restrictOnDelete();

            $table->foreign('currency_id', 'order_currency_fk')
                ->references('id')->on('currencies')->restrictOnDelete();

            $table->foreign('base_currency_id', 'order_base_currency_fk')
                ->references('id')->on('currencies')->nullOnDelete();

            $table->foreign('payment_term_id', 'order_payment_term_fk')
                ->references('id')->on('payment_terms')->nullOnDelete();

            $table->foreign('created_by', 'order_user_fk')
                ->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('order_id', 'oi_order_fk')
                ->references('id')->on('orders')->cascadeOnDelete();

            $table->foreign('product_variant_id', 'oi_variant_fk')
                ->references('id')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('oi_order_fk');
            $table->dropForeign('oi_variant_fk');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('order_company_fk');
            $table->dropForeign('order_customer_fk');
            $table->dropForeign('order_shipping_customer_fk');
            $table->dropForeign('order_status_fk');
            $table->dropForeign('order_payment_status_fk');
            $table->dropForeign('order_currency_fk');
            $table->dropForeign('order_base_currency_fk');
            $table->dropForeign('order_payment_term_fk');
            $table->dropForeign('order_user_fk');
        });

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_statuses');
    }
};
