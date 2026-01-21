<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        /**
         * PAYMENT TERMS (MASTER)
         */
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code')->unique();     // IMMEDIATE, NET_30
            $table->string('name');               // Immediate, Net 30 Days
            $table->integer('due_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'pt_company_code_unique');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('payment_terms');
    }
};
