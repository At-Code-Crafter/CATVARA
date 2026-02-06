<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * CUSTOMERS
         */
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('customer_code')->unique();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('payment_term_id')->nullable();

            $table->enum('type', ['INDIVIDUAL', 'COMPANY'])->default('INDIVIDUAL');

            // Common
            $table->string('display_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Company-specific
            $table->string('legal_name')->nullable();
            $table->string('tax_number')->nullable();

            $table->foreignId('tax_group_id')->nullable();
            $table->boolean('is_tax_exempt')->default(false);
            $table->string('tax_exempt_reason')->nullable();

            $table->decimal('percentage_discount', 5, 2)->default(0);
            $table->string('timezone', 100)->nullable();

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type'], 'cust_company_type_idx');
            $table->unique(['company_id', 'customer_code'], 'cust_company_customer_code_unique');
            $table->unique(['company_id', 'email'], 'cust_company_email_unique');
        });

        /**
         * FOREIGN KEYS (SHORT NAMES)
         */
        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('company_id', 'cust_company_fk')
                ->references('id')->on('companies')
                ->cascadeOnDelete();

            $table->foreign('payment_term_id', 'cust_payment_term_fk')
                ->references('id')->on('payment_terms')
                ->nullOnDelete();

            $table->foreign('tax_group_id', 'cust_tax_group_fk')
                ->references('id')->on('tax_groups')
                ->nullOnDelete();
        });

    }

    public function down(): void
    {

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('cust_company_fk');
            $table->dropForeign('cust_payment_term_fk');
        });

        Schema::dropIfExists('customers');
    }
};
