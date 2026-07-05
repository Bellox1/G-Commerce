<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dette_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dette_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // encaissé par
            $table->decimal('montant', 12, 2);
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'virement'])->default('especes');
            $table->timestamp('date_paiement')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dette_paiements');
    }
};
