<?php

namespace App\Console\Commands;

use App\Models\Magasin;
use App\Models\Produit;
use App\Models\StockMouvement;
use Illuminate\Console\Command;

class ConvertOldStockToMouvements extends Command
{
    protected $signature = 'stock:convert-initial';
    protected $description = 'Convertit le champ produits.stock (global) en mouvements stock_initial par magasin';

    public function handle()
    {
        $produits = Produit::where('stock', '>', 0)->get();

        if ($produits->isEmpty()) {
            $this->info('Aucun produit avec stock > 0 à convertir.');
            return;
        }

        $bar = $this->output->createProgressBar($produits->count());
        $bar->start();

        foreach ($produits as $p) {
            // Prendre le premier magasin du tenant pour y mettre le stock initial
            $magasin = Magasin::where('tenant_id', $p->tenant_id)->first();
            if (!$magasin) {
                $this->warn("Aucun magasin trouvé pour le produit {$p->nom}, ignoré.");
                $bar->advance();
                continue;
            }

            // Vérifier s'il existe déjà un mouvement "Stock initial" pour ce produit
            $existe = StockMouvement::where('tenant_id', $p->tenant_id)
                ->where('magasin_id', $magasin->id)
                ->where('produit_id', $p->id)
                ->where('type', 'ajustement_positif')
                ->where('note', 'Stock initial')
                ->exists();

            if ($existe) {
                $bar->advance();
                continue;
            }

            StockMouvement::create([
                'tenant_id'     => $p->tenant_id,
                'magasin_id'    => $magasin->id,
                'produit_id'    => $p->id,
                'user_id'       => null,
                'type'          => 'ajustement_positif',
                'quantite'      => (int) $p->stock,
                'note'          => 'Stock initial',
                'date_mouvement'=> $p->created_at ?? now(),
            ]);

            // Mettre à zéro le champ obsolète
            $p->stock = 0;
            $p->save();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Conversion terminée avec succès.');
    }
}
