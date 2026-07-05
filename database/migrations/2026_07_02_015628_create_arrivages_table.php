<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrivages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fournisseur_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // créé par
            $table->string('reference')->unique(); // ex: ARR-2026-001
            $table->date('date_arrivage');
            $table->string('pays_origine')->default('NG');
            // Taux de change
            $table->decimal('taux_change', 10, 4)->default(1); // 1 NGN = X FCFA
            $table->string('devise_origine')->default('NGN');
            $table->string('devise_locale')->default('XOF');
            // Frais
            $table->decimal('frais_transport', 12, 2)->default(0);
            $table->decimal('frais_douane', 12, 2)->default(0);
            $table->decimal('frais_manutention', 12, 2)->default(0);
            $table->decimal('frais_commission', 12, 2)->default(0);
            $table->decimal('frais_divers', 12, 2)->default(0);
            // Totaux calculés
            $table->decimal('total_frais', 12, 2)->default(0);
            $table->decimal('total_valeur_origine', 14, 2)->default(0); // en NGN
            $table->decimal('total_valeur_fcfa', 14, 2)->default(0);    // converti
            $table->decimal('total_cout_reel', 14, 2)->default(0);      // FCFA + frais
            $table->enum('statut', ['en_cours', 'receptionne', 'annule'])->default('en_cours');
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrivages');
    }
};
