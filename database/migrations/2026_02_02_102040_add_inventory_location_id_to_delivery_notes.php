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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_location_id')->nullable()->after('order_id');
            $table->foreign('inventory_location_id')->references('id')->on('inventory_locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['inventory_location_id']);
            $table->dropColumn('inventory_location_id');
        });
    }
};
