<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_tax_invoices', function (Blueprint $table) {
            $table->timestamp('last_downloaded_at')->nullable()->after('sent_at');
            $table->timestamp('email_failed_at')->nullable()->after('last_downloaded_at');
            $table->text('email_failure_reason')->nullable()->after('email_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_tax_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'last_downloaded_at',
                'email_failed_at',
                'email_failure_reason',
            ]);
        });
    }
};
