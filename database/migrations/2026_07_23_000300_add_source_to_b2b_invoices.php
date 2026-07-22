<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_invoices', function (Blueprint $table) {
            $table->string('source')->default('shipment')->after('sales_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('b2b_invoices', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
