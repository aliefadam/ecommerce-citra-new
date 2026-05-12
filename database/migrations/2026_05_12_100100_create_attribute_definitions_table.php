<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('data_type', 20)->default('text');
            $table->string('unit', 50)->nullable();
            $table->boolean('is_filterable')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('attribute_definitions')->insert([
            [
                'code' => 'diameter',
                'name' => 'Diameter',
                'data_type' => 'text',
                'unit' => null,
                'is_filterable' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'length_mm',
                'name' => 'Panjang',
                'data_type' => 'number',
                'unit' => 'mm',
                'is_filterable' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'thread_type',
                'name' => 'Tipe Drat',
                'data_type' => 'text',
                'unit' => null,
                'is_filterable' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'grade',
                'name' => 'Grade',
                'data_type' => 'text',
                'unit' => null,
                'is_filterable' => true,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'material',
                'name' => 'Material',
                'data_type' => 'text',
                'unit' => null,
                'is_filterable' => true,
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_definitions');
    }
};
