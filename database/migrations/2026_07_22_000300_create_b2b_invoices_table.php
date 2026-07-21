<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b2b_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('b2b_invoice_no')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('manual_customer_name')->nullable();
            $table->string('manual_customer_phone')->nullable();
            $table->string('manual_customer_email')->nullable();
            $table->string('status')->default('issued');
            $table->unsignedBigInteger('subtotal_amount')->default(0);
            $table->unsignedBigInteger('grand_total')->default(0);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('outstanding_amount')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('b2b_invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_invoice_id')->constrained('b2b_invoices')->cascadeOnDelete();
            $table->foreignId('delivery_note_detail_id')->nullable()->constrained('delivery_note_details')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('b2b_invoice_delivery_note', function (Blueprint $table) {
            $table->id();
            $table->foreignId('b2b_invoice_id')->constrained('b2b_invoices')->cascadeOnDelete();
            $table->foreignId('delivery_note_id')->constrained('delivery_notes')->cascadeOnDelete();
            $table->unique(['b2b_invoice_id', 'delivery_note_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b2b_invoice_delivery_note');
        Schema::dropIfExists('b2b_invoice_details');
        Schema::dropIfExists('b2b_invoices');
    }
};
