<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('payment_qr_url');
            $table->timestamp('payment_proof_uploaded_at')->nullable()->after('payment_proof_path');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_proof_uploaded_at');
            $table->timestamp('payment_rejected_at')->nullable()->after('payment_verified_at');
            $table->text('payment_admin_note')->nullable()->after('payment_rejected_at');
            $table->text('shipping_note')->nullable()->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof_path',
                'payment_proof_uploaded_at',
                'payment_verified_at',
                'payment_rejected_at',
                'payment_admin_note',
                'shipping_note',
            ]);
        });
    }
};
