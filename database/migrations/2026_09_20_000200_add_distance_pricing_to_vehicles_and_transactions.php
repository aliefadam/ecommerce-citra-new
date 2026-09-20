<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('distance_block_km', 8, 2)->default(5)->after('rate_per_kg');
            $table->unsignedBigInteger('rate_per_distance_block')->default(5000)->after('distance_block_km');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('shipping_distance_km', 8, 2)->nullable()->after('shipping_weight_grams');
            $table->decimal('shipping_distance_block_km', 8, 2)->nullable()->after('shipping_rate_per_kg');
            $table->unsignedBigInteger('shipping_distance_rate')->nullable()->after('shipping_distance_block_km');
            $table->unsignedBigInteger('shipping_weight_cost')->nullable()->after('shipping_distance_rate');
            $table->unsignedBigInteger('shipping_distance_cost')->nullable()->after('shipping_weight_cost');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_distance_km',
                'shipping_distance_block_km',
                'shipping_distance_rate',
                'shipping_weight_cost',
                'shipping_distance_cost',
            ]);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['distance_block_km', 'rate_per_distance_block']);
        });
    }
};
