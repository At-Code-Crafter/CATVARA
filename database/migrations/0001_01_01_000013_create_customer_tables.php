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

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type'], 'cust_company_type_idx');
            $table->unique(['company_id', 'customer_code'], 'cust_company_customer_code_unique');
            $table->unique(['company_id', 'email'], 'cust_company_email_unique');
            $table->unique(['company_id', 'phone'], 'cust_company_phone_unique');
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
        });

        /**
         * POS ORDERS → CUSTOMER (OPTIONAL)
         */
        Schema::table('pos_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_orders', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('user_id');
            }

            $table->foreign('customer_id', 'pos_customer_fk')
                ->references('id')->on('customers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropForeign('pos_customer_fk');

            if (Schema::hasColumn('pos_orders', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('cust_company_fk');
            $table->dropForeign('cust_payment_term_fk');
        });

        Schema::dropIfExists('customers');
    }
};
