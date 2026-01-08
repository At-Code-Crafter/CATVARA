<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'additional_charges')) {
                $table->json('additional_charges')->nullable()->after('shipping_address');
            }

            if (!Schema::hasColumn('orders', 'additional_total')) {
                $table->decimal('additional_total', 18, 6)->default(0)->after('shipping_tax_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'additional_charges')) {
                $table->dropColumn('additional_charges');
            }
            if (Schema::hasColumn('orders', 'additional_total')) {
                $table->dropColumn('additional_total');
            }
        });
    }
};
