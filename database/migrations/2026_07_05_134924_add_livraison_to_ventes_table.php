<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            // Statut de livraison physique (indépendant du paiement)
            $table->enum('statut_livraison', ['en_attente', 'livre', 'probleme'])
                  ->default('en_attente')
                  ->after('statut_paiement');
            $table->foreignId('livreur_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('statut_livraison');
            $table->timestamp('date_livraison')->nullable()->after('livreur_id');
            $table->text('note_livraison')->nullable()->after('date_livraison');
        });
    }

    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropForeign(['livreur_id']);
            $table->dropColumn(['statut_livraison', 'livreur_id', 'date_livraison', 'note_livraison']);
        });
    }
};
