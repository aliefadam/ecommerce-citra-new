<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->restrictOnDelete();
        });

        $boqId = DB::table('companies')->where('slug', 'boq')->value('id');
        DB::table('products')->whereNull('company_id')->update(['company_id' => $boqId]);

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->dropUnique('products_slug_unique');
            $table->unique(['company_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'slug']);
            $table->unique('slug');
            $table->dropConstrainedForeignId('company_id');
        });
    }
};
