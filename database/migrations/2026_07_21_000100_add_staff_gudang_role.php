<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_roles')->insert([
            'name' => 'Staff Gudang',
            'slug' => 'staff-gudang',
            'description' => 'Handles delivery notes and packing lists for B2B sales orders, no access to pricing/commercial documents.',
            'permissions' => json_encode([
                'dashboard.index',
                'sales_orders.index',
                'sales_orders.show',
                'delivery_notes.index',
                'delivery_notes.create',
                'delivery_notes.show',
                'delivery_notes.process',
                'packing_lists.index',
                'packing_lists.show',
                'stock.index',
            ]),
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('admin_roles')->where('slug', 'staff-gudang')->delete();
    }
};
