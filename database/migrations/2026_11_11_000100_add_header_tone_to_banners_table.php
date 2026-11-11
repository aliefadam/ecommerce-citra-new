<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tone of the banner's top area, so the mobile hero header can pick a readable logo/text colour.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('header_tone', 10)->default('light')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('header_tone');
        });
    }
};
