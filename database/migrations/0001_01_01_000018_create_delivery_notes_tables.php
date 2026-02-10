<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('inventory_location_id')->nullable();
            
            $table->string('delivery_note_number')->unique();
            $table->string('reference_number')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('status')->default('SHIPPED');
            $table->text('notes')->nullable();
            
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('inventory_location_id')->references('id')->on('inventory_locations')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_note_id');
            $table->unsignedBigInteger('order_item_id');
            
            $table->decimal('quantity', 18, 6);
            
            $table->timestamps();

            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->cascadeOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
