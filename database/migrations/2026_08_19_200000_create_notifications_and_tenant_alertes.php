<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('tenants') && !Schema::hasColumn('tenants', 'alertes_envoyees')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->json('alertes_envoyees')->nullable()->after('offre_expires_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'alertes_envoyees')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('alertes_envoyees');
            });
        }

        Schema::dropIfExists('notifications');
    }
};
