<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add converted_amount to support multi-currency payment applications.
     * When payment currency differs from document currency:
     * - amount = Amount deducted from payment (in payment currency)
     * - converted_amount = Amount credited to document (in document currency)
     * - exchange_rate = Rate used (how many document currency units per 1 payment currency unit)
     */
    public function up(): void
    {
        Schema::table('payment_applications', function (Blueprint $table) {
            // Amount in document currency (after conversion)
            $table->decimal('converted_amount', 18, 6)->nullable()->after('amount');

            // Store document currency for audit trail
            $table->unsignedBigInteger('document_currency_id')->nullable()->after('currency_id');
        });
    }

    public function down(): void
    {
        Schema::table('payment_applications', function (Blueprint $table) {
            $table->dropColumn(['converted_amount', 'document_currency_id']);
        });
    }
};
