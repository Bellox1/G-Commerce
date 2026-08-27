<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('offre_en_pause')->default(false)->after('offre_expires_at');
            $table->timestamp('offre_pause_depuis')->nullable()->after('offre_en_pause');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['offre_en_pause', 'offre_pause_depuis']);
        });
    }
};
