<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('delivery_note_id')->nullable();
            $table->unsignedBigInteger('order_item_id');
            $table->integer('box_number');
            $table->decimal('quantity', 18, 6);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes')->nullOnDelete();
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_boxes');
    }
};
