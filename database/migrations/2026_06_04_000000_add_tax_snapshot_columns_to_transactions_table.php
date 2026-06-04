<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('tax_name', 30)->nullable()->after('discount_amount');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('tax_name');
            $table->unsignedBigInteger('taxable_amount')->default(0)->after('tax_rate');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('taxable_amount');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['tax_name', 'tax_rate', 'taxable_amount', 'tax_amount']);
        });
    }
};
