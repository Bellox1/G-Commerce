<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('magasin_source_id')->constrained('magasins')->cascadeOnDelete();
            $table->foreignId('magasin_destination_id')->constrained('magasins')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // initié par
            $table->foreignId('livreur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique(); // TRF-2026-001
            $table->integer('quantite');
            $table->enum('statut', ['en_attente', 'en_transit', 'livre', 'annule'])->default('en_attente');
            $table->timestamp('date_transfert')->useCurrent();
            $table->timestamp('date_livraison')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferts');
    }
};
