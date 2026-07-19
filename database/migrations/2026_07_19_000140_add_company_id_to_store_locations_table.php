<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_locations', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->restrictOnDelete();
        });

        $boqId = DB::table('companies')->where('slug', 'boq')->value('id');
        DB::table('store_locations')->whereNull('company_id')->update(['company_id' => $boqId]);

        Schema::table('store_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('store_locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
