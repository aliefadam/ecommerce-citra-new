<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nama varian bisa panjang (gabungan banyak atribut), VARCHAR(255) tidak cukup.
     */
    private array $tables = [
        'transaction_details',
        'return_request_items',
        'quotation_details',
        'sales_order_details',
        'proforma_invoice_details',
        'delivery_note_details',
        'b2b_invoice_details',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'variant_name')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->text('variant_name')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'variant_name')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('variant_name')->nullable()->change();
                });
            }
        }
    }
};
