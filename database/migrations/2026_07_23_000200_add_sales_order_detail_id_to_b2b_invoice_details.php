<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('b2b_invoice_details', function (Blueprint $table) {
            $table->foreignId('sales_order_detail_id')->nullable()->after('delivery_note_detail_id')->constrained('sales_order_details')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('b2b_invoice_details', function (Blueprint $table) {
            $table->dropForeign(['sales_order_detail_id']);
            $table->dropColumn('sales_order_detail_id');
        });
    }
};
