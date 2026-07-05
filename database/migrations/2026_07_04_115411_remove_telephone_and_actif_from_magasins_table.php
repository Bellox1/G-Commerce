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
        Schema::table('magasins', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'actif']);
        });
    }

    public function down(): void
    {
        Schema::table('magasins', function (Blueprint $table) {
            $table->string('telephone', 50)->nullable()->after('ville');
            $table->boolean('actif')->default(true)->after('telephone');
        });
    }
};
