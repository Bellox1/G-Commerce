<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestion fine du stock des produits en cartouches :
     *  - un "carton" n'est compté que s'il est complet,
     *  - les cartouches restantes (ne formant pas un carton) sont suivies à part.
     */
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->unsignedInteger('stock_cartouches')->default(0)->after('stock');
        });

        Schema::table('stock_mouvements', function (Blueprint $table) {
            $table->integer('quantite_cartouche')->default(0)->after('quantite');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn('stock_cartouches');
        });

        Schema::table('stock_mouvements', function (Blueprint $table) {
            $table->dropColumn('quantite_cartouche');
        });
    }
};
