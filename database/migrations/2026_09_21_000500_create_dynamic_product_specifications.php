<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('specification_template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specification_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_definition_id')->constrained('attribute_definitions')->restrictOnDelete();
            $table->string('label_override')->nullable();
            $table->string('input_type', 20)->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(true);
            $table->boolean('affects_variant')->default(true);
            $table->boolean('allow_custom_value')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['specification_template_id', 'attribute_definition_id'], 'spec_template_attribute_unique');
        });

        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_definition_id')->constrained('attribute_definitions')->restrictOnDelete();
            $table->foreignId('specification_template_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['specification_template_id', 'attribute_definition_id', 'value'], 'attribute_option_template_unique');
        });

        Schema::table('category_details', function (Blueprint $table) {
            $table->foreignId('specification_template_id')->nullable()->after('main_category_id')
                ->constrained()->nullOnDelete();
        });

        Schema::table('main_categories', function (Blueprint $table) {
            $table->foreignId('default_specification_template_id')->nullable()->after('slug')
                ->constrained('specification_templates')->nullOnDelete();
        });

        $this->seedBaselineTemplates();
    }

    public function down(): void
    {
        Schema::table('category_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('specification_template_id');
        });
        Schema::table('main_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_specification_template_id');
        });
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('specification_template_fields');
        Schema::dropIfExists('specification_templates');
    }

    private function seedBaselineTemplates(): void
    {
        $now = now();
        $templates = [
            'bolt' => [
                'name' => 'Bolt',
                'description' => 'Baut dan fastener berulir.',
                'fields' => [
                    ['diameter', 'Diameter', 'text', null, ['M6', 'M8', 'M10', 'M12', 'M16', 'M20']],
                    ['length_mm', 'Panjang', 'number', 'mm', ['20', '30', '40', '50', '60', '80', '100']],
                    ['thread_type', 'Tipe Drat', 'text', null, ['Metric', 'UNC', 'UNF', 'BSW']],
                    ['grade', 'Grade', 'text', null, ['4.8', '8.8', '10.9', '12.9']],
                    ['material', 'Material', 'text', null, ['Carbon Steel', 'Stainless Steel', 'Alloy Steel']],
                ],
            ],
            'pipe' => [
                'name' => 'Pipe',
                'description' => 'Pipa industri berdasarkan size, schedule, dan standard.',
                'fields' => [
                    ['nominal_size', 'Nominal Size', 'text', 'inch', ['1/2', '3/4', '1', '2', '3', '4', '6', '8']],
                    ['schedule_class', 'Schedule / Class', 'text', null, ['Sch 10', 'Sch 20', 'Sch 40', 'Sch 80', 'Sch 160']],
                    ['material', 'Material', 'text', null, ['Carbon Steel', 'Stainless Steel', 'Galvanized Steel']],
                    ['thickness_mm', 'Thickness', 'number', 'mm', []],
                    ['pipe_length_m', 'Length', 'number', 'm', ['6', '12']],
                    ['standard', 'Standard', 'text', null, ['ASTM A106', 'ASTM A53', 'API 5L']],
                ],
            ],
            'valve' => [
                'name' => 'Valve',
                'description' => 'Valve industri berdasarkan tipe, size, rating, dan koneksi.',
                'fields' => [
                    ['valve_type', 'Valve Type', 'text', null, ['Ball Valve', 'Gate Valve', 'Globe Valve', 'Check Valve']],
                    ['size_inch', 'Size', 'text', 'inch', ['1/2', '3/4', '1', '2', '3', '4', '6']],
                    ['pressure_rating', 'Pressure Rating', 'text', null, ['ANSI 150', 'ANSI 300', 'ANSI 600', 'PN16', 'PN25']],
                    ['material', 'Material', 'text', null, ['Carbon Steel', 'Stainless Steel (SS316)', 'Brass', 'Cast Iron']],
                    ['connection_type', 'Connection Type', 'text', null, ['Flanged', 'Threaded', 'Socket Weld', 'Butt Weld']],
                    ['standard', 'Standard', 'text', null, ['API 6D', 'API 600', 'ASME B16.34']],
                ],
            ],
            'flange' => [
                'name' => 'Flange',
                'description' => 'Flange industri berdasarkan standard, class, facing, dan size.',
                'fields' => [
                    ['standard', 'Standard', 'text', null, ['ASME B16.5', 'ASME B16.47', 'JIS B2220']],
                    ['class_rating', 'Class / Rating', 'text', null, ['Class 150', 'Class 300', 'Class 600', 'PN16']],
                    ['facing', 'Facing', 'text', null, ['RF (Raised Face)', 'FF (Flat Face)', 'RTJ']],
                    ['size_inch', 'Size', 'text', 'inch', ['1/2', '1', '2', '3', '4', '6', '8']],
                    ['material', 'Material', 'text', null, ['Carbon Steel', 'Stainless Steel', 'Alloy Steel']],
                ],
            ],
            'nut' => [
                'name' => 'Nut',
                'description' => 'Mur berdasarkan thread size, pitch, grade, material, dan coating.',
                'fields' => [
                    ['thread_size', 'Thread Size', 'text', null, ['M6', 'M8', 'M10', 'M12', 'M16', 'M20']],
                    ['pitch_mm', 'Pitch', 'number', 'mm', ['1', '1.25', '1.5', '1.75', '2']],
                    ['grade', 'Grade', 'text', null, ['4', '8', '10', '12']],
                    ['material', 'Material', 'text', null, ['Carbon Steel', 'Stainless Steel', 'Brass']],
                    ['finish_coating', 'Finish / Coating', 'text', null, ['Plain', 'Zinc Plated', 'Hot Dip Galvanized', 'Black Oxide']],
                ],
            ],
        ];

        foreach ($templates as $templateCode => $templateData) {
            DB::table('specification_templates')->updateOrInsert(
                ['code' => $templateCode],
                ['name' => $templateData['name'], 'description' => $templateData['description'], 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
            $templateId = DB::table('specification_templates')->where('code', $templateCode)->value('id');

            foreach ($templateData['fields'] as $position => [$code, $name, $type, $unit, $options]) {
                DB::table('attribute_definitions')->updateOrInsert(
                    ['code' => $code],
                    ['name' => $name, 'data_type' => $type === 'number' ? 'number' : 'text', 'unit' => $unit, 'is_filterable' => true, 'sort_order' => ($position + 1) * 10, 'created_at' => $now, 'updated_at' => $now]
                );
                $definitionId = DB::table('attribute_definitions')->where('code', $code)->value('id');
                DB::table('specification_template_fields')->updateOrInsert(
                    ['specification_template_id' => $templateId, 'attribute_definition_id' => $definitionId],
                    ['input_type' => $options !== [] ? 'select' : ($type === 'number' ? 'decimal' : 'text'), 'is_required' => true, 'is_filterable' => true, 'affects_variant' => true, 'allow_custom_value' => $options === [], 'sort_order' => ($position + 1) * 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                );
                foreach ($options as $optionPosition => $value) {
                    DB::table('attribute_options')->updateOrInsert(
                        ['specification_template_id' => $templateId, 'attribute_definition_id' => $definitionId, 'value' => $value],
                        ['label' => null, 'sort_order' => ($optionPosition + 1) * 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }
    }
};
