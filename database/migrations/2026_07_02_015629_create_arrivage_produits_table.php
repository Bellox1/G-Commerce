<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrivage_produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrivage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->integer('quantite');
            // Prix en devise d'origine (NGN)
            $table->decimal('prix_unitaire_origine', 12, 2);
            $table->decimal('total_origine', 14, 2); // quantite * prix_unitaire_origine
            // Conversion FCFA
            $table->decimal('valeur_fcfa', 14, 2)->default(0); // total_origine * taux_change
            // Frais répartis proportionnellement
            $table->decimal('part_frais', 12, 2)->default(0);
            // Coût réel final par unité (FCFA)
            $table->decimal('cout_unitaire_reel', 12, 2)->default(0);
            $table->decimal('cout_total_reel', 14, 2)->default(0);
            // Prix de vente suggéré (avec arrondi)
            $table->decimal('prix_vente_suggere', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrivage_produits');
    }
};
