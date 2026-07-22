<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['quotations', 'sales_orders', 'proforma_invoices', 'b2b_invoices'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->decimal('ppn_rate', 5, 2)->default(0)->after('grand_total');
                $blueprint->unsignedBigInteger('ppn_amount')->default(0)->after('ppn_rate');
                $blueprint->unsignedBigInteger('shipping_cost')->default(0)->after('ppn_amount');
                $blueprint->unsignedBigInteger('admin_fee')->default(0)->after('shipping_cost');
                $blueprint->unsignedBigInteger('other_cost')->default(0)->after('admin_fee');
                $blueprint->string('other_cost_note')->nullable()->after('other_cost');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['ppn_rate', 'ppn_amount', 'shipping_cost', 'admin_fee', 'other_cost', 'other_cost_note']);
            });
        }
    }
};
