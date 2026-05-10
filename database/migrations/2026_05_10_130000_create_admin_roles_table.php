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
                    'view_dashboard',
                    'view_reports',
                    'manage_orders',
                    'manage_product_reviews',
                    'manage_catalog',
                    'manage_banners',
                    'manage_store_settings',
                    'view_customers',
                    'manage_admin_users',
                    'manage_roles_permissions',
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
                    'view_dashboard',
                    'manage_orders',
                    'manage_product_reviews',
                    'view_customers',
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
                    'view_dashboard',
                    'manage_catalog',
                    'manage_banners',
                    'manage_store_settings',
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
