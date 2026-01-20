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
        /**
         * CURRENCIES (GLOBAL)
         */
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            $table->string('code', 3)->unique(); // USD, GBP
            $table->string('name');
            $table->string('symbol', 5)->nullable();
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /**
         * EXCHANGE RATES (HISTORICAL, APPEND-ONLY)
         */
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreignId('base_currency_id');
            $table->foreignId('target_currency_id');

            $table->decimal('rate', 18, 8);
            $table->date('effective_date');
            $table->string('source')->nullable(); // ECB, API, MANUAL

            $table->timestamps();

            $table->unique(
                ['company_id', 'base_currency_id', 'target_currency_id', 'effective_date'],
                'ex_rate_unique'
            );
        });

        // Countries Table
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('iso_code_2', 2)->unique()->comment('ISO 3166-1 alpha-2');
            $table->string('iso_code_3', 3)->unique()->comment('ISO 3166-1 alpha-3');
            $table->string('numeric_code', 3)->nullable()->comment('ISO 3166-1 numeric');
            $table->string('phone_code', 10)->nullable()->comment('International dialing code');
            $table->string('currency_code', 3)->nullable()->comment('Default currency ISO code');
            $table->string('capital', 100)->nullable();
            $table->string('region', 50)->nullable()->comment('Continent/Region');
            $table->string('subregion', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'name']);
            $table->index('region');
        });

        // States/Provinces Table
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 10)->nullable()->comment('State/Province code');
            $table->string('type', 50)->nullable()->comment('State, Province, Territory, etc.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['country_id', 'code']);
            $table->index(['country_id', 'is_active']);
            $table->index(['is_active', 'name']);
        });

        /**
         * ADD FKs WITH SHORT NAMES (PREFIX SAFE)
         */
        Schema::table('exchange_rates', function (Blueprint $table) {
            $table->foreign('base_currency_id', 'ex_base_cur_fk')
                ->references('id')->on('currencies')
                ->cascadeOnDelete();

            $table->foreign('target_currency_id', 'ex_target_cur_fk')
                ->references('id')->on('currencies')
                ->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
