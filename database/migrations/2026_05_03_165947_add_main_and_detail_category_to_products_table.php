<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('main_category_id')->nullable()->after('category_id')->constrained('main_categories')->nullOnDelete();
            $table->foreignId('category_detail_id')->nullable()->after('main_category_id')->constrained('category_details')->nullOnDelete();
        });

        if (Schema::hasTable('categories')) {
            $legacyCategories = DB::table('categories')->select('id', 'parent_id', 'name')->orderBy('id')->get();
            $mainMap = [];
            $detailMap = [];

            foreach ($legacyCategories->whereNull('parent_id') as $row) {
                $slugBase = Str::slug((string) $row->name) ?: 'kategori-utama';
                $slug = $slugBase;
                $i = 2;
                while (DB::table('main_categories')->where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . $i++;
                }
                $id = DB::table('main_categories')->insertGetId([
                    'name' => $row->name,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $mainMap[$row->id] = $id;
            }

            foreach ($legacyCategories->whereNotNull('parent_id') as $row) {
                $mainId = $mainMap[$row->parent_id] ?? null;
                if (!$mainId) {
                    continue;
                }
                $slugBase = Str::slug((string) $row->name) ?: 'kategori-detail';
                $slug = $slugBase;
                $i = 2;
                while (DB::table('category_details')->where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . $i++;
                }
                $id = DB::table('category_details')->insertGetId([
                    'main_category_id' => $mainId,
                    'name' => $row->name,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $detailMap[$row->id] = $id;
            }

            $products = DB::table('products')->select('id', 'category_id')->whereNotNull('category_id')->get();
            foreach ($products as $product) {
                $detailId = $detailMap[$product->category_id] ?? null;
                if (!$detailId) {
                    continue;
                }
                $mainId = DB::table('category_details')->where('id', $detailId)->value('main_category_id');
                DB::table('products')->where('id', $product->id)->update([
                    'main_category_id' => $mainId,
                    'category_detail_id' => $detailId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('main_category_id');
            $table->dropConstrainedForeignId('category_detail_id');
        });
    }
};
