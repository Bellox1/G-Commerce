<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('reference')->nullable();
            $table->string('categorie')->nullable();
            $table->string('unite')->default('pcs'); // pcs, carton, kg, litre
            $table->decimal('prix_vente_conseille', 12, 2)->default(0); // prix suggéré système
            $table->decimal('prix_marche', 12, 2)->default(0);           // prix marché manuel
            $table->integer('seuil_alerte')->default(5);                 // stock minimum alerte
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
