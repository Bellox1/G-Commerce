<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dettes_societe', function (Blueprint $table) {
            $table->string('devise')->nullable()->after('description');
            $table->decimal('taux_de_change', 16, 4)->nullable()->after('devise');
            $table->decimal('montant_origine', 16, 2)->nullable()->after('taux_de_change');
        });
    }

    public function down(): void
    {
        Schema::table('dettes_societe', function (Blueprint $table) {
            $table->dropColumn(['devise', 'taux_de_change', 'montant_origine']);
        });
    }
};
