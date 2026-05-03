<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transaction_reviews')) {
            Schema::drop('transaction_reviews');
        }
    }

    public function down(): void
    {
        // legacy table intentionally not recreated
    }
};

