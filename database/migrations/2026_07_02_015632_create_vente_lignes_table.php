<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vente_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('prix_conseille', 12, 2)->default(0); // prix suggéré au moment de la vente
            $table->decimal('prix_vente', 12, 2);                 // prix réellement appliqué (modifiable)
            $table->decimal('cout_unitaire', 12, 2)->default(0);  // pour calcul marge
            $table->decimal('total_ligne', 14, 2)->default(0);    // quantite * prix_vente
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_lignes');
    }
};
