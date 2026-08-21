<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nettoyer ou insérer les nouvelles offres officielles
        DB::table('commission_rules')->updateOrInsert(
            ['code' => 'essentiel'],
            [
                'nom' => 'Offre Essentiel (1 an — 1 poste)',
                'prix' => 30000.00,
                'commission' => 10000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('commission_rules')->updateOrInsert(
            ['code' => 'professionnel'],
            [
                'nom' => 'Offre Professionnel (1 an — Multi-postes, Importation & Multi-magasins)',
                'prix' => 75000.00,
                'commission' => 25000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('commission_rules')->updateOrInsert(
            ['code' => 'entreprise'],
            [
                'nom' => 'Offre Entreprise (À vie — Licence dédiée, sur-mesure)',
                'prix' => 250000.00,
                'commission' => 70000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('commission_rules')->whereIn('code', ['essentiel', 'professionnel', 'entreprise'])->delete();
    }
};
