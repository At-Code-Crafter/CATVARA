<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration brings the quotes tables in line with the orders tables,
     * adding support for:
     * - Shipping customer (separate bill-to/ship-to)
     * - Tax groups (order-level and line-level)
     * - Global discounts (percent + amount)
     * - Custom/manual items
     * - Multi-currency with FX rate snapshot
     * - Line subtotals and discount totals
     */
    public function up(): void
    {
        /**
         * UPDATE QUOTES TABLE
         */
        Schema::table('quotes', function (Blueprint $table) {
            // Shipping customer (ship-to separate from bill-to)
            if (!Schema::hasColumn('quotes', 'shipping_customer_id')) {
                $table->unsignedBigInteger('shipping_customer_id')->nullable()->after('customer_id');
                $table->foreign('shipping_customer_id', 'quote_shipping_customer_fk')
                    ->references('id')->on('customers')->nullOnDelete();
            }

            // Multi-currency support (base currency + FX rate snapshot)
            if (!Schema::hasColumn('quotes', 'base_currency_id')) {
                $table->unsignedBigInteger('base_currency_id')->nullable()->after('currency_id');
                $table->foreign('base_currency_id', 'quote_base_currency_fk')
                    ->references('id')->on('currencies')->nullOnDelete();
            }

            if (!Schema::hasColumn('quotes', 'fx_rate')) {
                $table->decimal('fx_rate', 18, 10)->default(1)->after('base_currency_id');
            }

            // Tax group (order-level default)
            if (!Schema::hasColumn('quotes', 'tax_group_id')) {
                $table->unsignedBigInteger('tax_group_id')->nullable()->after('fx_rate');
                $table->foreign('tax_group_id', 'quote_tax_group_fk')
                    ->references('id')->on('tax_groups')->nullOnDelete();
            }

            // Global discounts
            if (!Schema::hasColumn('quotes', 'global_discount_percent')) {
                $table->decimal('global_discount_percent', 5, 2)->default(0)->after('shipping_tax_total');
            }
            if (!Schema::hasColumn('quotes', 'global_discount_amount')) {
                $table->decimal('global_discount_amount', 18, 6)->default(0)->after('global_discount_percent');
            }

            // Rounding (for cash rounding scenarios)
            if (!Schema::hasColumn('quotes', 'rounding_total')) {
                $table->decimal('rounding_total', 18, 6)->default(0)->after('global_discount_amount');
            }
        });

        /**
         * UPDATE QUOTE_ITEMS TABLE
         */
        Schema::table('quote_items', function (Blueprint $table) {
            // Custom/manual item support
            if (!Schema::hasColumn('quote_items', 'is_custom')) {
                $table->boolean('is_custom')->default(false)->after('product_variant_id');
            }
            if (!Schema::hasColumn('quote_items', 'custom_sku')) {
                $table->string('custom_sku')->nullable()->after('is_custom');
            }

            // Line subtotal (qty * unit_price before discounts)
            if (!Schema::hasColumn('quote_items', 'line_subtotal')) {
                $table->decimal('line_subtotal', 18, 6)->default(0)->after('quantity');
            }

            // Line discount total (for clarity)
            if (!Schema::hasColumn('quote_items', 'line_discount_total')) {
                $table->decimal('line_discount_total', 18, 6)->default(0)->after('discount_percent');
            }

            // Tax group (line-level override)
            if (!Schema::hasColumn('quote_items', 'tax_group_id')) {
                $table->unsignedBigInteger('tax_group_id')->nullable()->after('line_discount_total');
                $table->foreign('tax_group_id', 'qi_tax_group_fk')
                    ->references('id')->on('tax_groups')->nullOnDelete();
            }
        });

        // Note: We keep the unique constraint as the tables already support the new fields.
        // The constraint prevents accidental duplicate variant entries.
        // If you need to allow duplicates, manually drop the constraint:
        // Schema::table('quote_items', function ($t) { $t->dropUnique('quote_item_unique'); });

        // Check and add index for custom items if not exists
        if (!Schema::hasIndex('quote_items', 'qi_is_custom_idx')) {
            Schema::table('quote_items', function (Blueprint $table) {
                $table->index(['is_custom'], 'qi_is_custom_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropIndex('qi_is_custom_idx');
            $table->unique(['quote_id', 'product_variant_id'], 'quote_item_unique');

            $table->dropForeign('qi_tax_group_fk');
            $table->dropColumn(['is_custom', 'custom_sku', 'line_subtotal', 'line_discount_total', 'tax_group_id']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign('quote_shipping_customer_fk');
            $table->dropForeign('quote_base_currency_fk');
            $table->dropForeign('quote_tax_group_fk');

            $table->dropColumn([
                'shipping_customer_id',
                'base_currency_id',
                'fx_rate',
                'tax_group_id',
                'global_discount_percent',
                'global_discount_amount',
                'rounding_total',
            ]);
        });
    }
};
