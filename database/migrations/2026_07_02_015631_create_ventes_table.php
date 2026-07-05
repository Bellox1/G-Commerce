<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // vendeur
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference')->unique(); // VNT-2026-001
            $table->timestamp('date_vente')->useCurrent();
            $table->decimal('montant_total', 14, 2)->default(0);
            $table->decimal('montant_paye', 14, 2)->default(0);
            $table->decimal('montant_reste', 14, 2)->default(0);
            $table->enum('statut_paiement', ['paye', 'partiel', 'impaye'])->default('paye');
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'virement', 'credit'])->default('especes');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'date_vente']);
            $table->index(['magasin_id', 'date_vente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
