<?php

use App\Models\StoreSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Key yang dipindah per perusahaan (lihat docs/prd-multi-company-foundation.md §2).
     * store_settings tetap menjadi sumber baca aplikasi sampai Fase 2 merewire consumer-nya
     * (CheckoutTaxCalculator, MidtransController, ManualPaymentController) -- ini hanya menyalin
     * nilai BOQ saat ini supaya skema per-company sudah tersedia lebih dulu.
     */
    private const COMPANY_SCOPED_KEYS = [
        'manual_payment_bank_name',
        'manual_payment_account_number',
        'manual_payment_account_name',
        'manual_payment_instruction',
        'tax_enabled',
        'tax_name',
        'tax_rate',
    ];

    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
        });

        $boqId = DB::table('companies')->where('slug', 'boq')->value('id');
        $storeSettings = (array) DB::table('store_settings')->pluck('value', 'key');
        // store_settings sering cuma berisi key yang pernah benar-benar disimpan admin (mis.
        // wa_gateway_store_id) -- key manual_payment_*/tax_* yang belum pernah disentuh cuma ada
        // sebagai default PHP di StoreSetting::defaults(), jadi fallback ke situ supaya BOQ tidak
        // kehilangan nilai placeholder yang selama ini tampil ke customer (no behavior change).
        $storeSettings = array_merge(StoreSetting::defaults(), $storeSettings);
        $now = now();

        foreach (self::COMPANY_SCOPED_KEYS as $key) {
            DB::table('company_settings')->insert([
                'company_id' => $boqId,
                'key' => $key,
                'value' => json_encode((string) $storeSettings[$key]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
