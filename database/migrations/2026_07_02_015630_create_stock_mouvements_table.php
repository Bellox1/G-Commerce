<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('magasin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', [
                'entree_arrivage',
                'sortie_vente',
                'transfert_sortie',
                'transfert_entree',
                'ajustement_positif',
                'ajustement_negatif',
            ]);
            $table->integer('quantite'); // toujours positif
            $table->decimal('cout_unitaire', 12, 2)->default(0);
            // Référence à l'opération source
            $table->string('reference_type')->nullable(); // App\Models\Arrivage, App\Models\Vente, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamp('date_mouvement')->useCurrent();
            $table->timestamps();

            $table->index(['magasin_id', 'produit_id']);
            $table->index(['tenant_id', 'produit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mouvements');
    }
};
