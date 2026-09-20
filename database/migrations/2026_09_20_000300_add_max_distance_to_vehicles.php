<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('max_distance_km', 8, 2)->default(50)->after('rate_per_distance_block');
        });

        DB::table('vehicles')->where('type', 'motor')->update(['max_distance_km' => 30]);
        DB::table('vehicles')->where('type', 'van')->update(['max_distance_km' => 100]);
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('max_distance_km');
        });
    }
};
