<?php

namespace App\Console\Commands;

use App\Models\Dette;
use Illuminate\Console\Command;

class SyncDetteVentes extends Command
{
    protected $signature = 'dettes:sync-ventes';
    protected $description = 'Sync existing debt payments back to ventes table (montant_paye, statut_paiement)';

    public function handle()
    {
        $dettes = Dette::whereNotNull('vente_id')
            ->where('montant_paye', '>', 0)
            ->with('vente')
            ->get();

        $count = 0;
        foreach ($dettes as $dette) {
            $vente = $dette->vente;
            if (!$vente) continue;

            // Total paid = total_sale_price - remaining_debt
            // (idempotent: works no matter how many times you run it)
            $totalPaye = $vente->montant_total - $dette->montant_restant;
            if ($totalPaye < 0) $totalPaye = 0;

            $vente->montant_paye = $totalPaye;
            $vente->montant_reste = $vente->montant_total - $totalPaye;
            if ($vente->montant_reste < 0) $vente->montant_reste = 0;

            $vente->statut_paiement = $vente->montant_reste <= 0
                ? 'paye'
                : ($vente->montant_paye > 0 ? 'partiel' : 'impaye');

            $vente->save();
            $count++;
        }

        $this->info("Synced {$count} dette(s) to their ventes.");
    }
}
