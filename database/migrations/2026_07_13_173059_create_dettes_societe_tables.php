<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dettes_societe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('fournisseur_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('arrivage_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('montant', 14, 2);
            $table->decimal('montant_paye', 14, 2)->default(0);
            $table->string('description')->nullable();
            $table->date('date_dette');
            $table->string('statut')->default('en_cours'); // en_cours, partiel, solde
            $table->timestamps();
        });

        Schema::create('dette_societe_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dette_societe_id')->constrained('dettes_societe')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 14, 2);
            $table->date('date_paiement');
            $table->string('mode_paiement')->default('especes');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dette_societe_paiements');
        Schema::dropIfExists('dettes_societe');
    }
};
