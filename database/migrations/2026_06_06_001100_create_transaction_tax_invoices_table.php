<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_tax_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('requested');
            $table->string('taxpayer_name');
            $table->string('taxpayer_number', 32);
            $table->text('taxpayer_address');
            $table->string('taxpayer_email');
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('tax_invoice_number')->nullable();
            $table->date('tax_invoice_date')->nullable();
            $table->string('tax_invoice_file_path')->nullable();
            $table->foreignId('uploaded_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->timestamps();

            $table->unique('transaction_id');
            $table->index(['status', 'requested_at']);
            $table->index('taxpayer_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_tax_invoices');
    }
};
