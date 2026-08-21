<?php

namespace App\Console\Commands;

use App\Models\Produit;
use App\Models\Tenant;
use App\Notifications\StockBasNotification;
use App\Services\StockService;
use Illuminate\Console\Command;

class VerifierStocks extends Command
{
    protected $signature = 'stocks:verifier';
    protected $description = 'Notifie les admins des produits dont le stock est sous le seuil d\'alerte (dédupliqué via produits.alerte_notifiee).';

    public function handle(): int
    {
        $stock  = new StockService();
        $tenants = Tenant::all();
        $total  = 0;

        foreach ($tenants as $tenant) {
            $produits = Produit::where('tenant_id', $tenant->id)->get();

            foreach ($produits as $produit) {
                $s     = $stock->getStockTotal($produit->id);
                $seuil = (int) $produit->seuil_alerte;
                $enAlerte = $s <= 5 || ($seuil > 0 && $s <= $seuil);

                if ($enAlerte) {
                    // Déjà notifié pour cet épisode → on ne spamme pas
                    if ($produit->alerte_notifiee) {
                        continue;
                    }
                    $admins = $tenant->users()->whereIn('role', ['admin', 'super_admin'])->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new StockBasNotification($produit, $s));
                        $total++;
                    }
                    $produit->alerte_notifiee = true;
                    $produit->save();
                } else {
                    // Stock revenu au-dessus du seuil → on réautorise une future alerte
                    if ($produit->alerte_notifiee) {
                        $produit->alerte_notifiee = false;
                        $produit->save();
                    }
                }
            }
        }

        $this->info("Notifications de stock envoyées : {$total}");
        return self::SUCCESS;
    }
}
