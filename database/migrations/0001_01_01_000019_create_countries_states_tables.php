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
        // Countries Table
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
