<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fields = DB::table('specification_template_fields')
            ->join('attribute_definitions', 'attribute_definitions.id', '=', 'specification_template_fields.attribute_definition_id')
            ->select('specification_template_fields.id', 'specification_template_fields.specification_template_id', 'specification_template_fields.attribute_definition_id', 'attribute_definitions.data_type')
            ->get();

        foreach ($fields as $field) {
            $hasOptions = DB::table('attribute_options')
                ->where('specification_template_id', $field->specification_template_id)
                ->where('attribute_definition_id', $field->attribute_definition_id)
                ->where('is_active', true)
                ->exists();

            DB::table('specification_template_fields')->where('id', $field->id)->update([
                'input_type' => $hasOptions ? 'select' : ($field->data_type === 'number' ? 'decimal' : 'text'),
                'allow_custom_value' => ! $hasOptions,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Metadata normalization is intentionally retained on rollback.
    }
};
