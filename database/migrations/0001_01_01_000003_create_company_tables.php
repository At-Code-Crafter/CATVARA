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
        Schema::create('company_statuses', function (Blueprint $table) {

            $table->id();
            $table->string('name');              // Active, Suspended, Closed
            $table->string('code')->unique();    // ACTIVE, SUSPENDED, CLOSED
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('companies', function (Blueprint $table) {

            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');              // Display name
            $table->string('legal_name');        // Registered name
            $table->string('code')->unique();    // Internal short code

            $table->string('logo')->nullable();
            $table->string('website_url')->nullable();

            $table->unsignedBigInteger('base_currency_id')->nullable();

            $table->unsignedBigInteger('company_status_id');
            $table->integer('password_expiry_days')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_status_id')
                ->references('id')
                ->on('company_statuses');
        });

        Schema::create('company_details', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('company_id');

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->text('address')->nullable();
            $table->string('tax_number')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('company_id');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();
        });

        Schema::create('company_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('branch')->nullable();
            $table->unsignedBigInteger('currency_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id');

            // POS_ORDER, WEB_ORDER, INVOICE, CREDIT_NOTE, QUOTE
            $table->string('document_type');

            // Optional: POS / WEB / B2B
            $table->string('channel')->nullable();

            // Prefix you define (POS-, INV-, CN-, etc)
            $table->string('prefix');

            // Optional postfix (e.g. /2025)
            $table->string('postfix')->nullable();

            // Last used number
            $table->unsignedBigInteger('current_number')->default(0);

            // Optional year-based reset
            $table->unsignedSmallInteger('year')->nullable();

            $table->timestamps();

            $table->unique(
                ['company_id', 'document_type', 'channel', 'year'],
                'doc_seq_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('company_details');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('company_statuses');
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('company_banks');
        Schema::enableForeignKeyConstraints();
    }
};
