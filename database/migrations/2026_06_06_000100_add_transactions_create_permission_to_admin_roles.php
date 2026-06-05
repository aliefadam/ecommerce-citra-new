<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_roles')
            ->orderBy('id')
            ->get()
            ->each(function ($role) {
                $permissions = json_decode($role->permissions ?: '[]', true) ?: [];

                if (in_array('transactions.index', $permissions, true) && !in_array('transactions.create', $permissions, true)) {
                    $permissions[] = 'transactions.create';

                    DB::table('admin_roles')
                        ->where('id', $role->id)
                        ->update([
                            'permissions' => json_encode(array_values(array_unique($permissions))),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('admin_roles')
            ->orderBy('id')
            ->get()
            ->each(function ($role) {
                $permissions = json_decode($role->permissions ?: '[]', true) ?: [];
                $permissions = array_values(array_filter($permissions, fn ($permission) => $permission !== 'transactions.create'));

                DB::table('admin_roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                        'updated_at' => now(),
                    ]);
            });
    }
};
