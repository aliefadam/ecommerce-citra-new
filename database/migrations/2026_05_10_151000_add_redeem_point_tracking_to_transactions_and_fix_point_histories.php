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
            $table->unsignedInteger('redeem_points_reserved')->default(0)->after('discount_amount');
            $table->timestamp('redeem_points_reserved_at')->nullable()->after('redeem_points_reserved');
            $table->timestamp('redeem_points_finalized_at')->nullable()->after('redeem_points_reserved_at');
            $table->timestamp('redeem_points_released_at')->nullable()->after('redeem_points_finalized_at');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE point_histories MODIFY points INT NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'redeem_points_reserved',
                'redeem_points_reserved_at',
                'redeem_points_finalized_at',
                'redeem_points_released_at',
            ]);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE point_histories MODIFY points INT UNSIGNED NOT NULL');
        }
    }
};
