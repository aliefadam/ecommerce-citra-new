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
        Schema::table('addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable()->after('phone_number');
            $table->unsignedBigInteger('city_id')->nullable()->after('province_id');
            $table->unsignedBigInteger('district_id')->nullable()->after('city_id');
            $table->unsignedBigInteger('subdistrict_id')->nullable()->after('district_id');
            $table->string('district')->nullable()->after('city');
            $table->string('subdistrict')->nullable()->after('district');
            $table->unsignedBigInteger('destination_id')->nullable()->after('postal_code');

            $table->index(['province_id', 'city_id', 'district_id', 'subdistrict_id'], 'addresses_region_ids_idx');
            $table->index(['destination_id'], 'addresses_destination_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex('addresses_region_ids_idx');
            $table->dropIndex('addresses_destination_id_idx');
            $table->dropColumn([
                'province_id',
                'city_id',
                'district_id',
                'subdistrict_id',
                'district',
                'subdistrict',
                'destination_id',
            ]);
        });
    }
};

