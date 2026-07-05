<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dettes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vente_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('montant_initial', 14, 2);
            $table->decimal('montant_paye', 14, 2)->default(0);
            $table->decimal('montant_restant', 14, 2);
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['en_cours', 'partiel', 'solde', 'en_retard'])->default('en_cours');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'statut']);
            $table->index(['client_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dettes');
    }
};
