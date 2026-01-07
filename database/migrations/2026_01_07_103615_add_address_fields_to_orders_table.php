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
            $table->json('billing_address')->nullable()->after('notes');
            $table->json('shipping_address')->nullable()->after('billing_address');
            $table->decimal('shipping_total', 18, 6)->default(0)->after('shipping_address');
            $table->decimal('shipping_tax_total', 18, 6)->default(0)->after('shipping_total');
            $table->text('notes')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['billing_address', 'shipping_address', 'shipping_total', 'shipping_tax_total', 'notes']);
        });
    }
};
