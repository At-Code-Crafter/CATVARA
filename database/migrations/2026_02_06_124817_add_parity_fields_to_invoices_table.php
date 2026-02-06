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
        Schema::table('invoices', function (Blueprint $table) {
            // Missing Header Fields
            $table->unsignedBigInteger('shipping_customer_id')->nullable()->after('customer_id');
            $table->unsignedBigInteger('payment_status_id')->nullable()->after('status_id');
            
            $table->decimal('global_discount_percent', 10, 2)->default(0)->after('discount_total');
            $table->decimal('global_discount_amount', 18, 6)->default(0)->after('global_discount_percent');
            $table->decimal('shipping_tax_total', 18, 6)->default(0)->after('shipping_total');

            // Adjust Precision to match Orders (18, 6)
            $table->decimal('subtotal', 18, 6)->default(0)->change();
            $table->decimal('discount_total', 18, 6)->default(0)->change();
            $table->decimal('shipping_total', 18, 6)->default(0)->change();
            $table->decimal('tax_total', 18, 6)->default(0)->change();
            $table->decimal('rounding_total', 18, 6)->default(0)->change();
            $table->decimal('grand_total', 18, 6)->default(0)->change();

            // Foreign Keys
            $table->foreign('shipping_customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('payment_status_id')->references('id')->on('payment_statuses')->nullOnDelete();

            // Drop old column if it exists from previous iteration
            if (Schema::hasColumn('invoices', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            // Adjust Precision to match Order Items
            $table->decimal('unit_price', 18, 6)->change();
            $table->decimal('line_subtotal', 18, 6)->change();
            $table->decimal('discount_amount', 18, 6)->change();
            $table->decimal('line_discount_total', 18, 6)->change();
            $table->decimal('tax_amount', 18, 6)->change();
            $table->decimal('line_total', 18, 6)->change();
            
            // Add missing line fields if any (orders have fulfilled_quantity, we skip for invoice for now)
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['shipping_customer_id']);
            $table->dropForeign(['payment_status_id']);
            $table->dropColumn([
                'shipping_customer_id',
                'payment_status_id',
                'global_discount_percent',
                'global_discount_amount',
                'shipping_tax_total'
            ]);
            $table->string('payment_status')->default('UNPAID');
        });
    }
};
