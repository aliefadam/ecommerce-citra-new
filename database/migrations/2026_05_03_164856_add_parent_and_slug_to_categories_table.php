<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('slug')->nullable()->after('name');
        });

        $categories = DB::table('categories')->select('id', 'name')->orderBy('id')->get();
        $used = [];
        foreach ($categories as $category) {
            $base = Str::slug((string) $category->name);
            $base = $base !== '' ? $base : 'kategori';
            $slug = $base;
            $i = 2;
            while (in_array($slug, $used, true)) {
                $slug = $base . '-' . $i;
                $i++;
            }
            $used[] = $slug;
            DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
