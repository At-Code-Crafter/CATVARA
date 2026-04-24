<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('tracking_url_template')->nullable(); // optional: https://.../track?number={tracking}
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->foreignId('delivery_service_id')->nullable()->after('vehicle_number')->constrained('delivery_services')->nullOnDelete();
            $table->string('tracking_number')->nullable()->after('delivery_service_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['delivery_service_id']);
            $table->dropColumn(['delivery_service_id', 'tracking_number']);
        });

        Schema::dropIfExists('delivery_services');
    }
};
