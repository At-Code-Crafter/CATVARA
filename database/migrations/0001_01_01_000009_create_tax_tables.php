<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * TAX GROUPS
         * Example: "UAE VAT Standard", "UK VAT Standard", "Zero Rated", "Exempt", "Reverse Charge"
         */
        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');

            $table->string('code'); // e.g. UAE_VAT_5, UK_VAT_20, ZERO, EXEMPT
            $table->string('name'); // e.g. "UAE VAT 5% (Standard)"
            $table->text('description')->nullable();

            // if true, tax is included in price (VAT-inclusive pricing)
            $table->boolean('is_tax_inclusive')->default(false);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'tg_company_active_idx');
            $table->unique(['company_id', 'code'], 'tg_company_code_unique');
        });

        /**
         * TAX RATES
         * A group can have one or multiple rates (future-proof; e.g., SGST+CGST, state+county, etc.)
         */
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('tax_group_id');

            $table->string('code')->nullable(); // optional: VAT20, VAT5, etc.
            $table->string('name');             // "VAT 20%", "UAE VAT 5%"
            $table->decimal('rate', 8, 4)->default(0); // percent, e.g. 5.0000, 20.0000

            // Optional targeting (keep nullable now; you can expand later)
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();

            // Advanced tax scenarios (safe to keep now, can be ignored initially)
            $table->boolean('is_compound')->default(false); // tax-on-tax if you ever need it
            $table->integer('priority')->default(1);        // order of application if multiple rates

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'tax_group_id', 'is_active'], 'tr_company_group_active_idx');
            $table->index(['tax_group_id', 'priority'], 'tr_group_priority_idx');

            // avoid duplicate name/code collisions per group (optional but helpful)
            $table->unique(['tax_group_id', 'name'], 'tr_group_name_unique');
        });

        /**
         * FOREIGN KEYS (PREFIX SAFE)
         */
        Schema::table('tax_groups', function (Blueprint $table) {
            $table->foreign('company_id', 'tg_company_fk')
                ->references('id')->on('companies')->cascadeOnDelete();
        });

        Schema::table('tax_rates', function (Blueprint $table) {
            $table->foreign('company_id', 'tr_company_fk')
                ->references('id')->on('companies')->cascadeOnDelete();

            $table->foreign('tax_group_id', 'tr_tax_group_fk')
                ->references('id')->on('tax_groups')->cascadeOnDelete();

            $table->foreign('country_id', 'tr_country_fk')
                ->references('id')->on('countries')->nullOnDelete();

            $table->foreign('state_id', 'tr_state_fk')
                ->references('id')->on('states')->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_groups');
    }
};
