<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->nullable()->after('role')->constrained('admin_roles')->nullOnDelete();
        });

        $now = now();

        DB::table('admin_roles')->insert([
            [
                'name' => 'Store Manager',
                'slug' => 'store-manager',
                'description' => 'Full operational access except super admin account management.',
                'permissions' => json_encode([
                    'dashboard.index',
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
                    'transactions.index',
                    'transactions.show',
                    'transactions.edit',
                    'transactions.verify_payment',
                    'return_requests.index',
                    'return_requests.edit',
                    'product_reviews.index',
                    'product_reviews.edit',
                    'product_reviews.delete',
                    'customers.index',
                    'member_tiers.index',
                    'member_tiers.create',
                    'member_tiers.edit',
                    'member_tiers.delete',
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
                    'banners.index',
                    'banners.create',
                    'banners.edit',
                    'banners.delete',
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
                    'admin_users.index',
                    'admin_users.create',
                    'admin_users.edit',
                    'admin_users.delete',
                    'admin_roles.index',
                    'admin_roles.create',
                    'admin_roles.edit',
                    'admin_roles.delete',
                ]),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Order Staff',
                'slug' => 'order-staff',
                'description' => 'Handles transactions, returns, and product reviews.',
                'permissions' => json_encode([
                    'dashboard.index',
                    'transactions.index',
                    'transactions.show',
                    'transactions.edit',
                    'transactions.verify_payment',
                    'return_requests.index',
                    'return_requests.edit',
                    'product_reviews.index',
                    'product_reviews.edit',
                    'customers.index',
                ]),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Catalog Staff',
                'slug' => 'catalog-staff',
                'description' => 'Handles products, categories, stock, coupons, and banners.',
                'permissions' => json_encode([
                    'dashboard.index',
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
                    'banners.index',
                    'banners.create',
                    'banners.edit',
                    'banners.delete',
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
                ]),
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_role_id');
        });

        Schema::dropIfExists('admin_roles');
    }
};
