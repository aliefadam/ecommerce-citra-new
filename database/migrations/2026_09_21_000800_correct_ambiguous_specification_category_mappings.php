<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ambiguousMainIds = DB::table('main_categories')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%klem%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%clamp%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%bracket%']);
            })
            ->whereNull('default_specification_template_id')
            ->pluck('id');

        if ($ambiguousMainIds->isNotEmpty()) {
            DB::table('category_details')
                ->whereIn('main_category_id', $ambiguousMainIds)
                ->where(function ($query) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%pipa%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%pipe%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%bolt%']);
                })
                ->update(['specification_template_id' => null, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Ambiguous mappings stay unassigned and can be configured explicitly by an admin.
    }
};
