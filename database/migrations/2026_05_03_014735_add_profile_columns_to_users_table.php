<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('username')->nullable()->unique()->after('email');
            $table->enum('gender', ['male', 'female'])->nullable()->after('username');
            $table->string('phone_country_code', 8)->nullable()->after('gender');
            $table->string('phone_number', 30)->nullable()->after('phone_country_code');
            $table->date('birth_date')->nullable()->after('phone_number');
            $table->string('social_url')->nullable()->after('birth_date');
            $table->text('bio')->nullable()->after('social_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'username',
                'gender',
                'phone_country_code',
                'phone_number',
                'birth_date',
                'social_url',
                'bio',
            ]);
        });
    }
};
