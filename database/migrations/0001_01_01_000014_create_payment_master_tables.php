<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * PAYMENT TERMS (MASTER)
         */
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->string('code'); // IMMEDIATE, NET_30
            $table->string('name'); // Immediate, Net 30 Days
            $table->integer('due_days')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'pt_company_code_unique');
        });

        /**
         * PAYMENT METHODS (MASTER per company)
         */
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->string('code'); // CASH, CARD...
            $table->string('name'); // Cash, Card, Bank Transfer
            $table->string('type'); // CASH, CARD, GATEWAY, BANK, WALLET, CREDIT

            $table->boolean('is_active')->default(true);
            $table->boolean('allow_refund')->default(true);
            $table->boolean('requires_reference')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'pm_company_code_unique');
        });

        /**
         * PAYMENT STATUSES (GLOBAL)
         */
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // INITIATED, SUCCESS, FAILED, REFUNDED
            $table->string('name');
            $table->boolean('is_final')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // REMOVE: $table->unique(['code'], 'ps_code_unique'); (redundant)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_statuses');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payment_terms');
    }
};
