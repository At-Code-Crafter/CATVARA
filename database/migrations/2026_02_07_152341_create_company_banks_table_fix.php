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
        // Only create if it doesn't exist (might have been created in earlier migration)
        if (!Schema::hasTable('company_banks')) {
            Schema::create('company_banks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('bank_name');
                $table->string('account_name');
                $table->string('account_number');
                $table->string('iban')->nullable();
                $table->string('swift_code')->nullable();
                $table->string('branch')->nullable();
                $table->foreignId('currency_id')->nullable()->constrained();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_banks');
    }
};
