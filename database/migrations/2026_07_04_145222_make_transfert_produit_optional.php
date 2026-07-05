<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transferts', function (Blueprint $table) {
            $table->foreignId('produit_id')->nullable()->change();
            $table->integer('quantite')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transferts', function (Blueprint $table) {
            $table->foreignId('produit_id')->nullable(false)->change();
            $table->integer('quantite')->nullable(false)->change();
        });
    }
};
