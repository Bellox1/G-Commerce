<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_souscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('nom_entreprise');
            $table->string('nom_contact');
            $table->string('email_contact');
            $table->string('telephone_contact');
            
            // ex: "local", "cloud"
            $table->string('type_offre');
            // ex: "1", "3", "6", "12" mois, ou "vie" pour local
            $table->string('duree')->nullable();
            
            // Options supplémentaires sous format JSON
            $table->json('options')->nullable(); 

            // Montant estimé ou final
            $table->decimal('montant_total', 15, 2)->nullable();
            
            // "en_attente", "paye", "approuve", "rejete"
            $table->string('statut')->default('en_attente');
            
            // Si l'offre est parrainée par un prestataire
            $table->unsignedBigInteger('prestataire_id')->nullable();

            // Si validé, on associe le tenant créé
            $table->unsignedBigInteger('tenant_id')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_souscriptions');
    }
};
