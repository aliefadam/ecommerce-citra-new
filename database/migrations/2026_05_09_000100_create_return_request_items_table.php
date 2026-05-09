<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained('return_requests')->cascadeOnDelete();
            $table->foreignId('transaction_detail_id')->constrained('transaction_details')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->timestamps();

            $table->index(['transaction_detail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_request_items');
    }
};
