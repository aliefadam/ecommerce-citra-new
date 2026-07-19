<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('npwp')->nullable();
            $table->string('invoice_prefix')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $storeSettings = DB::table('store_settings')->pluck('value', 'key');
        $storeName = (string) ($storeSettings['store_name'] ?? 'BOQ');
        $now = now();

        DB::table('companies')->insert([
            'name' => $storeName,
            'slug' => 'boq',
            'legal_name' => $storeName,
            'logo_path' => (string) ($storeSettings['store_logo_path'] ?? '') ?: null,
            'address' => null,
            'phone' => null,
            'email' => null,
            'npwp' => null,
            'invoice_prefix' => 'BOQ',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
