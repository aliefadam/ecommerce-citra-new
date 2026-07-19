<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_payment_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('midtrans');
            $table->text('server_key')->nullable();
            $table->text('client_key')->nullable();
            $table->boolean('is_production')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_payment_credentials');
    }
};
