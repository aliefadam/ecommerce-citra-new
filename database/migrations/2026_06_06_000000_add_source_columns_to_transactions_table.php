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
            $table->string('source', 30)->default('checkout')->after('user_id')->index();
            $table->foreignId('created_by_admin_id')->nullable()->after('source')->constrained('users')->nullOnDelete();
        });

        DB::table('transactions')
            ->whereNull('source')
            ->orWhere('source', '')
            ->update(['source' => 'checkout']);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_admin_id');
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};
