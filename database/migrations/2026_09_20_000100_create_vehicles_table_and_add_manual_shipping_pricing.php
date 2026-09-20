<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PERMISSIONS = [
        'vehicles.index',
        'vehicles.create',
        'vehicles.edit',
        'vehicles.delete',
    ];

    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 30);
            $table->string('plate_number', 30)->nullable();
            $table->unsignedInteger('capacity_kg')->nullable();
            $table->unsignedBigInteger('rate_per_kg')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'sort_order']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('shipping_vehicle_id')->nullable()->after('shipping_type')
                ->constrained('vehicles')->nullOnDelete();
            $table->unsignedBigInteger('shipping_weight_grams')->nullable()->after('shipping_vehicle_id');
            $table->unsignedBigInteger('shipping_rate_per_kg')->nullable()->after('shipping_weight_grams');
        });

        $now = now();
        $rows = [];
        foreach (DB::table('companies')->pluck('id') as $companyId) {
            $rows[] = [
                'company_id' => $companyId,
                'name' => 'Motor Kurir',
                'type' => 'motor',
                'plate_number' => null,
                'capacity_kg' => 20,
                'rate_per_kg' => 5000,
                'is_active' => true,
                'notes' => 'Cocok untuk paket kecil dan pengiriman dalam kota.',
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = [
                'company_id' => $companyId,
                'name' => 'Van Pengiriman',
                'type' => 'van',
                'plate_number' => null,
                'capacity_kg' => 500,
                'rate_per_kg' => 2500,
                'is_active' => true,
                'notes' => 'Untuk barang besar atau pengiriman dalam jumlah banyak.',
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('vehicles')->insert($rows);
        }

        DB::table('admin_roles')->orderBy('id')->each(function ($role) {
            $permissions = json_decode($role->permissions ?: '[]', true) ?: [];
            if (! in_array('transactions.edit', $permissions, true) && ! in_array('manage_orders', $permissions, true)) {
                return;
            }

            DB::table('admin_roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values(array_unique([...$permissions, ...self::PERMISSIONS]))),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('admin_roles')->orderBy('id')->each(function ($role) {
            $permissions = json_decode($role->permissions ?: '[]', true) ?: [];
            $permissions = array_values(array_filter(
                $permissions,
                fn ($permission) => ! in_array($permission, self::PERMISSIONS, true)
            ));

            DB::table('admin_roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions),
                'updated_at' => now(),
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_vehicle_id');
            $table->dropColumn(['shipping_weight_grams', 'shipping_rate_per_kg']);
        });

        Schema::dropIfExists('vehicles');
    }
};
