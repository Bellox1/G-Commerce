<?php

namespace App\Services;

use App\Models\StockMouvement;
use App\Models\Transfert;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Retourne le stock d'un produit dans un magasin
     * = somme des mouvements (arrivages, ventes, transferts, ajustements)
     */
    public function getStock(int $magasinId, int $produitId): int
    {
        return (int) StockMouvement::where('magasin_id', $magasinId)
            ->where('produit_id', $produitId)
            ->selectRaw("SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite
                ELSE 0
            END) as total")
            ->value('total') ?? 0;
    }

    /**
     * Retourne le stock de tous les produits d'un magasin
     * = somme des mouvements (arrivages, ventes, transferts, ajustements)
     */
    public function getStockMagasin(int $magasinId): array
    {
        return StockMouvement::where('magasin_id', $magasinId)
            ->selectRaw("produit_id, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite
                ELSE 0
            END) as stock")
            ->groupBy('produit_id')
            ->get()
            ->keyBy('produit_id')
            ->map(fn($m) => (int) $m->stock)
            ->toArray();
    }

    /**
     * Effectue un transfert entre magasins (un ou plusieurs produits)
     * 
     * $data = ['tenant_id', 'magasin_source_id', 'magasin_destination_id', 'user_id', 'notes']
     * $produits = [['produit_id' => 1, 'quantite' => 5], ...]
     */
    public function transferer(array $data, array $produits): Transfert
    {
        return DB::transaction(function () use ($data, $produits) {
            $annee    = now()->year;
            $count    = Transfert::whereYear('created_at', $annee)->count() + 1;
            $reference = sprintf('TRF-%d-%03d', $annee, $count);

            $transfert = Transfert::create([
                'tenant_id'              => $data['tenant_id'],
                'magasin_source_id'      => $data['magasin_source_id'],
                'magasin_destination_id' => $data['magasin_destination_id'],
                'user_id'                => $data['user_id'] ?? null,
                'reference'              => $reference,
                'statut'                 => 'en_transit',
                'notes'                  => $data['notes'] ?? null,
            ]);

            foreach ($produits as $p) {
                // Ligne de transfert
                $transfert->produits()->create([
                    'produit_id' => $p['produit_id'],
                    'quantite'   => $p['quantite'],
                ]);

                // Sortie du magasin source
                StockMouvement::create([
                    'tenant_id'      => $data['tenant_id'],
                    'magasin_id'     => $data['magasin_source_id'],
                    'produit_id'     => $p['produit_id'],
                    'user_id'        => $data['user_id'] ?? null,
                    'type'           => 'transfert_sortie',
                    'quantite'       => $p['quantite'],
                    'reference_type' => Transfert::class,
                    'reference_id'   => $transfert->id,
                    'note'           => "Transfert {$reference} sortie",
                ]);

                // Entrée dans le magasin destination
                StockMouvement::create([
                    'tenant_id'      => $data['tenant_id'],
                    'magasin_id'     => $data['magasin_destination_id'],
                    'produit_id'     => $p['produit_id'],
                    'user_id'        => $data['user_id'] ?? null,
                    'type'           => 'transfert_entree',
                    'quantite'       => $p['quantite'],
                    'reference_type' => Transfert::class,
                    'reference_id'   => $transfert->id,
                    'note'           => "Transfert {$reference} entrée",
                ]);
            }

            $transfert->statut         = 'livre';
            $transfert->date_livraison = now();
            $transfert->save();

            return $transfert;
        });
    }

    /**
     * Ajustement manuel du stock (inventaire)
     */
    public function ajuster(int $tenantId, int $magasinId, int $produitId, int $quantiteReelle, int $userId, string $note = ''): void
    {
        $stockActuel = $this->getStock($magasinId, $produitId);
        $diff = $quantiteReelle - $stockActuel;

        if ($diff === 0) return;

        StockMouvement::create([
            'tenant_id'  => $tenantId,
            'magasin_id' => $magasinId,
            'produit_id' => $produitId,
            'user_id'    => $userId,
            'type'       => $diff > 0 ? 'ajustement_positif' : 'ajustement_negatif',
            'quantite'   => abs($diff),
            'note'       => $note ?: "Ajustement inventaire",
        ]);
    }
}
