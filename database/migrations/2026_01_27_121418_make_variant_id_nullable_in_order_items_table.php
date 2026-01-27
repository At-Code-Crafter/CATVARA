<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // 1. Drop FK that depends on the index (order_id part of composite unique)
            $table->dropForeign('oi_order_fk');
            
            // 2. Drop the unique constraint
            $table->dropUnique('order_item_unique');
            
            // 3. Make product_variant_id nullable
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();

            // 4. Restore the FK
            $table->foreign('order_id', 'oi_order_fk')
                ->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Restore non-nullable
            $table->unsignedBigInteger('product_variant_id')->nullable(false)->change();
            
            // Restore unique constraint (only if all rows have variant_id)
            $table->unique(['order_id', 'product_variant_id'], 'order_item_unique');
        });
    }
};
