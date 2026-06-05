<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OPERATIONAL_PERMISSIONS = [
        'tax_invoices.index',
        'tax_invoices.show',
        'tax_invoices.process',
        'tax_invoices.reject',
        'tax_invoices.upload',
        'tax_invoices.send',
    ];

    public function up(): void
    {
        DB::table('admin_roles')
            ->orderBy('id')
            ->get()
            ->each(function ($role) {
                $permissions = json_decode($role->permissions ?: '[]', true) ?: [];

                if (! in_array('transactions.index', $permissions, true)) {
                    return;
                }

                $permissions = array_merge($permissions, self::OPERATIONAL_PERMISSIONS);

                if (($role->slug ?? null) === 'store-manager') {
                    $permissions[] = 'tax_invoices.view_sensitive';
                }

                DB::table('admin_roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode(array_values(array_unique($permissions))),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        $taxInvoicePermissions = array_merge(self::OPERATIONAL_PERMISSIONS, ['tax_invoices.view_sensitive']);

        DB::table('admin_roles')
            ->orderBy('id')
            ->get()
            ->each(function ($role) use ($taxInvoicePermissions) {
                $permissions = json_decode($role->permissions ?: '[]', true) ?: [];
                $permissions = array_values(array_filter(
                    $permissions,
                    fn ($permission) => ! in_array($permission, $taxInvoicePermissions, true)
                ));

                DB::table('admin_roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                        'updated_at' => now(),
                    ]);
            });
    }
};
