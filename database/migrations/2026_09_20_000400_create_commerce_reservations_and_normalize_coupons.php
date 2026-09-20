<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('normalized_code', 50)->nullable()->after('code');
        });

        DB::table('coupons')->orderBy('id')->each(function ($coupon): void {
            DB::table('coupons')->where('id', $coupon->id)->update([
                'normalized_code' => mb_strtoupper(trim((string) $coupon->code)),
            ]);
        });

        $collision = DB::table('coupons')
            ->select('company_id', 'normalized_code', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('company_id', 'normalized_code')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($collision) {
            throw new \RuntimeException('Migrasi kupon dibatalkan: ditemukan kode kupon duplikat dalam perusahaan yang sama.');
        }

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropUnique('coupons_code_unique');
            $table->unique(['company_id', 'normalized_code'], 'coupons_company_normalized_code_unique');
        });

        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('reserved');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['transaction_id', 'product_variant_id'], 'inventory_reservation_transaction_variant_unique');
            $table->index(['product_variant_id', 'status'], 'inventory_reservation_variant_status_index');
            $table->index(['status', 'expires_at'], 'inventory_reservation_expiry_index');
        });

        Schema::create('flash_sale_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flash_sale_item_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('reserved');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('committed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['transaction_id', 'flash_sale_item_id'], 'flash_sale_reservation_transaction_item_unique');
            $table->index(['flash_sale_item_id', 'status'], 'flash_sale_reservation_item_status_index');
            $table->index(['status', 'expires_at'], 'flash_sale_reservation_expiry_index');
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->string('status', 20)->default('reserved');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique('transaction_id');
            $table->index(['coupon_id', 'status'], 'coupon_redemption_coupon_status_index');
            $table->index(['status', 'expires_at'], 'coupon_redemption_expiry_index');
        });
    }

    public function down(): void
    {
        $globalCollision = DB::table('coupons')
            ->select('normalized_code', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('normalized_code')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($globalCollision) {
            throw new \RuntimeException('Rollback kupon dibatalkan: kode lintas perusahaan harus dibuat unik kembali terlebih dahulu.');
        }

        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('flash_sale_reservations');
        Schema::dropIfExists('inventory_reservations');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropUnique('coupons_company_normalized_code_unique');
            $table->dropColumn('normalized_code');
            $table->unique('code');
        });
    }
};
