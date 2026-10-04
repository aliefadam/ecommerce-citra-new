<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_pages', function (Blueprint $table) {
            $table->string('diagram_image')->nullable()->after('hero_image');
        });

        Schema::create('content_page_category_hotspots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('main_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('category_detail_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('x_percent', 5, 2);
            $table->decimal('y_percent', 5, 2);
            $table->timestamps();

            $table->index(['content_page_id', 'main_category_id']);
            $table->index(['content_page_id', 'category_detail_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_page_category_hotspots');

        Schema::table('content_pages', function (Blueprint $table) {
            $table->dropColumn('diagram_image');
        });
    }
};
