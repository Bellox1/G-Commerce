<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE clients SET nom = COALESCE(prenom, '') || ' ' || nom WHERE prenom IS NOT NULL AND prenom != ''");

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('prenom');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('prenom')->nullable()->after('nom');
        });
    }
};
