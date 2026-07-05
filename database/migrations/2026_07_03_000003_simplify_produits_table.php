<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'unite']);
            $table->integer('stock')->default(0);
            $table->boolean('a_cartouche')->default(false);
            $table->integer('cartouche_par_carton')->nullable();
            $table->decimal('prix_cartouche', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['stock', 'a_cartouche', 'cartouche_par_carton', 'prix_cartouche']);
            $table->string('categorie')->nullable();
            $table->string('unite')->default('pcs');
        });
    }
};
