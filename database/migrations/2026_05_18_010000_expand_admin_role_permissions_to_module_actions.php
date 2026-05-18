<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyMap = [
            'view_dashboard' => ['dashboard.index'],
            'view_reports' => [
                'reports.index',
                'reports.owner',
                'reports.sales',
                'reports.sales.export',
                'reports.stock',
                'reports.products',
                'reports.payments',
                'reports.customers',
                'reports.promos',
                'reports.returns',
            ],
            'manage_orders' => [
                'transactions.index',
                'transactions.show',
                'transactions.edit',
                'transactions.verify_payment',
                'return_requests.index',
                'return_requests.edit',
            ],
            'manage_product_reviews' => [
                'product_reviews.index',
                'product_reviews.edit',
                'product_reviews.delete',
            ],
            'manage_catalog' => [
                'products.index',
                'products.create',
                'products.edit',
                'products.delete',
                'products.import',
                'categories.index',
                'categories.create',
                'categories.edit',
                'categories.delete',
                'variants.index',
                'variants.create',
                'variants.edit',
                'variants.delete',
                'stock.index',
                'stock.edit',
                'flash_sales.index',
                'flash_sales.create',
                'flash_sales.edit',
                'flash_sales.delete',
                'coupons.index',
                'coupons.create',
                'coupons.edit',
                'coupons.delete',
            ],
            'manage_banners' => [
                'banners.index',
                'banners.create',
                'banners.edit',
                'banners.delete',
            ],
            'manage_store_settings' => [
                'store_settings.index',
                'store_settings.edit',
                'newsletter.index',
                'newsletter.send',
                'newsletter.delete',
                'promo_pages.index',
                'promo_pages.create',
                'promo_pages.edit',
                'promo_pages.delete',
                'content_pages.index',
                'content_pages.create',
                'content_pages.edit',
                'content_pages.delete',
            ],
            'view_customers' => ['customers.index'],
            'manage_membership_tiers' => [
                'member_tiers.index',
                'member_tiers.create',
                'member_tiers.edit',
                'member_tiers.delete',
            ],
            'manage_admin_users' => [
                'admin_users.index',
                'admin_users.create',
                'admin_users.edit',
                'admin_users.delete',
            ],
            'manage_roles_permissions' => [
                'admin_roles.index',
                'admin_roles.create',
                'admin_roles.edit',
                'admin_roles.delete',
            ],
        ];

        DB::table('admin_roles')
            ->orderBy('id')
            ->get()
            ->each(function ($role) use ($legacyMap) {
                $permissions = json_decode($role->permissions ?: '[]', true) ?: [];
                $expanded = [];

                foreach ($permissions as $permission) {
                    foreach ($legacyMap[$permission] ?? [$permission] as $mappedPermission) {
                        $expanded[] = $mappedPermission;
                    }
                }

                DB::table('admin_roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode(array_values(array_unique($expanded))),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // Permission expansion is intentionally not collapsed because custom roles
        // may have been edited after this migration.
    }
};
