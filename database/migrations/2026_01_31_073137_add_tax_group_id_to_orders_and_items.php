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
            $table->unsignedBigInteger('tax_group_id')->nullable()->after('currency_id');
            $table->foreign('tax_group_id')->references('id')->on('tax_groups')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_group_id')->nullable()->after('quantity');
            $table->foreign('tax_group_id')->references('id')->on('tax_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['tax_group_id']);
            $table->dropColumn('tax_group_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['tax_group_id']);
            $table->dropColumn('tax_group_id');
        });
    }
};
