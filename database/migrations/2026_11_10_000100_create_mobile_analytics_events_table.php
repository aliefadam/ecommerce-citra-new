<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 50);
            $table->string('surface', 20)->default('mobile_spa');
            $table->string('app_version', 50);
            $table->string('route_name', 80)->nullable();
            $table->char('session_hash', 64);
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_name', 'occurred_at']);
            $table->index(['app_version', 'occurred_at']);
            $table->index(['session_hash', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_analytics_events');
    }
};
