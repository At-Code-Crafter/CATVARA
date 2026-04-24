<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('delivery_notes', 'po_number')) {
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->string('po_number')->nullable()->after('reference_number');
            });
        }

        if (! Schema::hasColumn('delivery_notes', 'weight')) {
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->decimal('weight', 18, 3)->nullable()->after('po_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('delivery_notes', 'weight')) {
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->dropColumn('weight');
            });
        }

        if (Schema::hasColumn('delivery_notes', 'po_number')) {
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->dropColumn('po_number');
            });
        }
    }
};
