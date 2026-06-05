<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('shipping_type', 40)->default('belum_ditentukan')->after('shipping_cost')->index();
            $table->string('shipping_district')->nullable()->after('shipping_city');
            $table->string('shipping_courier_name')->nullable()->after('shipping_postal_code');
            $table->string('shipping_service')->nullable()->after('shipping_courier_name');
        });

        DB::table('transactions')
            ->whereNull('shipping_type')
            ->orWhere('shipping_type', '')
            ->update(['shipping_type' => 'belum_ditentukan']);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['shipping_type']);
            $table->dropColumn([
                'shipping_type',
                'shipping_district',
                'shipping_courier_name',
                'shipping_service',
            ]);
        });
    }
};
