<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('unsubscribe_token', 100)->nullable()->unique()->after('email');
            $table->boolean('is_subscribed')->default(true)->after('unsubscribe_token');
            $table->timestamp('unsubscribed_at')->nullable()->after('subscribed_at');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['unsubscribe_token', 'is_subscribed', 'unsubscribed_at']);
        });
    }
};
