<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('content_pages', 'diagram_image')) {
            Schema::table('content_pages', function (Blueprint $table) {
                $table->string('diagram_image')->nullable()->after('hero_image');
            });
        }

        if (! Schema::hasTable('content_page_category_hotspots')) {
            Schema::create('content_page_category_hotspots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_page_id')->constrained()->cascadeOnDelete();
                $table->foreignId('main_category_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('category_detail_id')->nullable()->constrained()->cascadeOnDelete();
                $table->decimal('x_percent', 5, 2);
                $table->decimal('y_percent', 5, 2);
                $table->timestamps();

                $table->index(['content_page_id', 'main_category_id'], 'cp_hotspots_page_main_idx');
                $table->index(['content_page_id', 'category_detail_id'], 'cp_hotspots_page_detail_idx');
            });

            return;
        }

        if (! Schema::hasIndex('content_page_category_hotspots', 'cp_hotspots_page_main_idx')) {
            Schema::table('content_page_category_hotspots', function (Blueprint $table) {
                $table->index(['content_page_id', 'main_category_id'], 'cp_hotspots_page_main_idx');
            });
        }

        if (! Schema::hasIndex('content_page_category_hotspots', 'cp_hotspots_page_detail_idx')) {
            Schema::table('content_page_category_hotspots', function (Blueprint $table) {
                $table->index(['content_page_id', 'category_detail_id'], 'cp_hotspots_page_detail_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_page_category_hotspots');

        if (Schema::hasColumn('content_pages', 'diagram_image')) {
            Schema::table('content_pages', function (Blueprint $table) {
                $table->dropColumn('diagram_image');
            });
        }
    }
};
