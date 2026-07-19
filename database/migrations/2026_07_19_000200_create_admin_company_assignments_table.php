<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_company_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('admin_role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
        });

        $now = now();
        $staffUsers = DB::table('users')->whereNotNull('admin_role_id')->get(['id', 'admin_role_id']);

        foreach ($staffUsers as $staffUser) {
            DB::table('admin_company_assignments')->insert([
                'user_id' => $staffUser->id,
                'company_id' => null,
                'admin_role_id' => $staffUser->admin_role_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_company_assignments');
    }
};
