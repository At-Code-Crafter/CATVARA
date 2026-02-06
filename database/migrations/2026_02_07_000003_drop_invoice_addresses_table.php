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
        Schema::dropIfExists('invoice_addresses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('invoice_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('type', 20)->default('BILLING');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->timestamps();
            $table->index(['invoice_id'], 'inv_addr_idx');
            $table->foreign('invoice_id', 'ia_invoice_fk')->references('id')->on('invoices')->cascadeOnDelete();
        });
    }
};
