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
            $table->foreignId('specification_template_id')->nullable()->after('parent_id')
                ->constrained()->nullOnDelete();
        });

        $templateIds = DB::table('specification_templates')->pluck('id', 'code');

        DB::table('main_categories')->orderBy('id')->get()->each(function ($category) use ($templateIds) {
            if ($category->default_specification_template_id) {
                return;
            }
            $code = $this->detectTemplateCode((string) $category->name);
            if ($code && isset($templateIds[$code])) {
                DB::table('main_categories')->where('id', $category->id)->update([
                    'default_specification_template_id' => $templateIds[$code],
                    'updated_at' => now(),
                ]);
            }
        });

        DB::table('category_details')
            ->leftJoin('main_categories', 'main_categories.id', '=', 'category_details.main_category_id')
            ->select('category_details.id', 'category_details.name', 'category_details.specification_template_id', 'main_categories.name as main_name')
            ->orderBy('category_details.id')
            ->get()
            ->each(function ($category) use ($templateIds) {
                if ($category->specification_template_id) {
                    return;
                }
                $code = $this->detectTemplateCode(trim((string) $category->main_name).' '.trim((string) $category->name));
                if ($code && isset($templateIds[$code])) {
                    DB::table('category_details')->where('id', $category->id)->update([
                        'specification_template_id' => $templateIds[$code],
                        'updated_at' => now(),
                    ]);
                }
            });

        DB::table('categories')->orderBy('id')->get()->each(function ($category) use ($templateIds) {
            $code = $this->detectTemplateCode((string) $category->name);
            if ($code && isset($templateIds[$code])) {
                DB::table('categories')->where('id', $category->id)->update([
                    'specification_template_id' => $templateIds[$code],
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('specification_template_id');
        });
    }

    private function detectTemplateCode(string $name): ?string
    {
        $normalized = ' '.Str::of($name)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->trim().' ';
        foreach ([' klem ', ' clamp ', ' bracket '] as $excludedContext) {
            if (str_contains($normalized, $excludedContext)) {
                return null;
            }
        }
        $rules = [
            'bolt' => [' bolt ', ' baut '],
            'nut' => [' nut ', ' mur '],
            'pipe' => [' pipe ', ' pipa '],
            'valve' => [' valve ', ' katup '],
            'flange' => [' flange ', ' flens '],
        ];

        foreach ($rules as $code => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($normalized, $needle)) {
                    return $code;
                }
            }
        }

        return null;
    }
};
