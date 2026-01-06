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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('grand_total');
            $table->json('billing_address')->nullable()->after('notes');
            $table->json('shipping_address')->nullable()->after('billing_address');
            $table->decimal('shipping_total', 18, 6)->default(0)->after('grand_total');
            $table->decimal('shipping_tax_total', 18, 6)->default(0)->after('shipping_total');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 18, 6)->default(0)->after('line_total');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('discount_amount'); // e.g. 20.00 for 20%
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'tax_rate']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['notes', 'billing_address', 'shipping_address', 'shipping_total', 'shipping_tax_total']);
        });
    }
};
