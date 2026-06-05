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
            $table->string('payment_status', 30)->default('unpaid')->after('payment_method')->index();
            $table->timestamp('payment_paid_at')->nullable()->after('payment_status');
            $table->unsignedBigInteger('payment_amount')->default(0)->after('payment_paid_at');
        });

        DB::table('transactions')
            ->whereNull('payment_status')
            ->orWhere('payment_status', '')
            ->update(['payment_status' => 'unpaid']);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['payment_status', 'payment_paid_at', 'payment_amount']);
        });
    }
};
