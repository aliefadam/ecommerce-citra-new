<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('manual_customer_name')->nullable()->after('created_by_admin_id');
            $table->string('manual_customer_phone', 50)->nullable()->after('manual_customer_name');
            $table->string('manual_customer_email')->nullable()->after('manual_customer_phone');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'manual_customer_name',
                'manual_customer_phone',
                'manual_customer_email',
            ]);
        });
    }
};
