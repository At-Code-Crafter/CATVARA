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
         * Update ORDER_ITEMS
         */
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'tax_group_id')) {
                $table->unsignedBigInteger('tax_group_id')->nullable()->after('line_discount_total');
                $table->foreign('tax_group_id', 'oi_tax_group_fk')
                    ->references('id')->on('tax_groups')->nullOnDelete();
            }
        });

        /**
         * Update QUOTES (Uniqueness)
         */
        Schema::table('quotes', function (Blueprint $table) {
            // Drop global unique if it exists
            // Laravel default name is quotes_quote_number_unique
            try {
                $table->dropUnique(['quote_number']);
            } catch (\Exception $e) {
                // Ignore if already dropped or different name
            }

            // Add per-company unique
            $table->unique(['company_id', 'quote_number'], 'quote_company_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique('quote_company_number_unique');
            $table->unique('quote_number');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('oi_tax_group_fk');
            $table->dropColumn('tax_group_id');
        });
    }
};
