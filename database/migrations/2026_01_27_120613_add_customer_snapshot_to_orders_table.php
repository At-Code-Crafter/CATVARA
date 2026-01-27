<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Billing customer snapshot
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_tax_number')->nullable()->after('customer_email');
            
            // Shipping customer snapshot
            $table->string('shipping_customer_name')->nullable()->after('shipping_customer_id');
            $table->string('shipping_customer_email')->nullable()->after('shipping_customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_tax_number',
                'shipping_customer_name',
                'shipping_customer_email',
            ]);
        });
    }
};
