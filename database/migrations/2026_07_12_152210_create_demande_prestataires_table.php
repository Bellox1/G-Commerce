<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_prestataires', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('email');
            $table->string('telephone');
            $table->string('entreprise')->nullable();
            $table->text('motivation')->nullable();
            
            // "en_attente", "approuve", "rejete"
            $table->string('statut')->default('en_attente');
            
            // Si validé, on associe l'utilisateur créé
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_prestataires');
    }
};
