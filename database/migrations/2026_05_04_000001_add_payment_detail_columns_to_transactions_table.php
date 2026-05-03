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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_va_number')->nullable()->after('payment_method');
            $table->string('payment_va_bank')->nullable()->after('payment_va_number');
            $table->text('payment_qr_url')->nullable()->after('payment_va_bank');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_va_number', 'payment_va_bank', 'payment_qr_url']);
        });
    }
};
