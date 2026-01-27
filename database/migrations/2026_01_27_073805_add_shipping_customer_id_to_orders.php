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
            $table->unsignedBigInteger('shipping_customer_id')->nullable()->after('customer_id');
            $table->foreign('shipping_customer_id', 'order_shipping_customer_fk')
                ->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('order_shipping_customer_fk');
            $table->dropColumn('shipping_customer_id');
        });
    }
};
