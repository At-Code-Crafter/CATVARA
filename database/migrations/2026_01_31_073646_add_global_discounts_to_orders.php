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
            $table->decimal('global_discount_percent', 5, 2)->default(0)->after('rounding_total');
            $table->decimal('global_discount_amount', 15, 6)->default(0)->after('global_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['global_discount_percent', 'global_discount_amount']);
        });
    }
};
