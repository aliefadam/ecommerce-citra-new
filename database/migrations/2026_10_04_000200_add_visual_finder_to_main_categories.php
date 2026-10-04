<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('main_categories', 'diagram_image')) {
            Schema::table('main_categories', function (Blueprint $table) {
                $table->string('diagram_image')->nullable()->after('image');
            });
        }

        if (! Schema::hasTable('main_category_diagram_areas')) {
            Schema::create('main_category_diagram_areas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('main_category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('category_detail_id')->constrained()->cascadeOnDelete();
                $table->decimal('x_percent', 5, 2);
                $table->decimal('y_percent', 5, 2);
                $table->decimal('width_percent', 5, 2);
                $table->decimal('height_percent', 5, 2);
                $table->timestamps();

                $table->index(['main_category_id', 'category_detail_id'], 'mc_diagram_area_category_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('main_category_diagram_areas');

        if (Schema::hasColumn('main_categories', 'diagram_image')) {
            Schema::table('main_categories', function (Blueprint $table) {
                $table->dropColumn('diagram_image');
            });
        }
    }
};
