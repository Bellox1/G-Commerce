<?php

namespace App\Services;

use App\Models\Produit;
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
        $produit = Produit::find($produitId);
        $cpc = $produit ? $produit->cartouchesParCartonEffectif() : 1;

        return (int) StockMouvement::where('magasin_id', $magasinId)
            ->where('produit_id', $produitId)
            ->selectRaw("FLOOR(GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpc} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpc} + quantite_cartouche)
                ELSE 0
            END)) / {$cpc}) as total")
            ->value('total') ?? 0;
    }

    /**
     * Retourne le stock TOTAL centralisé d'un produit (en cartons complets).
     */
    public function getStockTotal(int $produitId): int
    {
        $produit = Produit::find($produitId);
        $cpc = $produit ? $produit->cartouchesParCartonEffectif() : 1;

        return (int) StockMouvement::where('produit_id', $produitId)
            ->selectRaw("FLOOR(GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpc} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpc} + quantite_cartouche)
                ELSE 0
            END)) / {$cpc}) as total")
            ->value('total') ?? 0;
    }

    /**
     * Comme getStock mais renvoie aussi les cartouches restantes.
     */
    public function getStockDetail(int $magasinId, int $produitId): array
    {
        $produit = Produit::find($produitId);
        $cpc = $produit ? $produit->cartouchesParCartonEffectif() : 1;
        $total = (int) StockMouvement::where('magasin_id', $magasinId)
            ->where('produit_id', $produitId)
            ->selectRaw("GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpc} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpc} + quantite_cartouche)
                ELSE 0
            END)) as total")
            ->value('total') ?? 0;
        return ['cartons' => intdiv($total, $cpc), 'cartouches' => $total % $cpc];
    }

    /**
     * Retourne le stock centralisé (tous magasins) de tous les produits.
     * Rejoint la table produits pour appliquer le bon cartouche_par_carton.
     */
    public function getStockTotalParProduit(): array
    {
        $cpcCase = "CASE WHEN produits.a_cartouche = 1 AND produits.cartouche_par_carton > 0 THEN produits.cartouche_par_carton ELSE 1 END";

        return StockMouvement::join('produits', 'produits.id', '=', 'stock_mouvements.produit_id')
            ->select('produit_id')
            ->selectRaw("FLOOR(GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpcCase} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpcCase} + quantite_cartouche)
                ELSE 0
            END)) / MAX({$cpcCase})) as stock")
            ->groupBy('produit_id')
            ->get()
            ->keyBy('produit_id')
            ->map(fn($m) => (int) $m->stock)
            ->toArray();
    }

    /**
     * Retourne le stock de tous les produits d'un magasin.
     */
    public function getStockMagasin(int $magasinId): array
    {
        $cpcCase = "CASE WHEN produits.a_cartouche = 1 AND produits.cartouche_par_carton > 0 THEN produits.cartouche_par_carton ELSE 1 END";

        return StockMouvement::where('magasin_id', $magasinId)
            ->join('produits', 'produits.id', '=', 'stock_mouvements.produit_id')
            ->selectRaw("produit_id, FLOOR(GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpcCase} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpcCase} + quantite_cartouche)
                ELSE 0
            END)) / MAX({$cpcCase})) as stock")
            ->groupBy('produit_id')
            ->get()
            ->keyBy('produit_id')
            ->map(fn($m) => (int) $m->stock)
            ->toArray();
    }

    /**
     * Cartouches restantes (loose) par produit, tous magasins.
     */
    public function getStockTotalCartouchesParProduit(): array
    {
        $cpcCase = "CASE WHEN produits.a_cartouche = 1 AND produits.cartouche_par_carton > 0 THEN produits.cartouche_par_carton ELSE 1 END";

        return StockMouvement::join('produits', 'produits.id', '=', 'stock_mouvements.produit_id')
            ->select('produit_id')
            ->selectRaw("MOD(GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpcCase} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpcCase} + quantite_cartouche)
                ELSE 0
            END)), MAX({$cpcCase})) as reste")
            ->groupBy('produit_id')
            ->get()
            ->keyBy('produit_id')
            ->map(fn($m) => (int) $m->reste)
            ->toArray();
    }

    /**
     * Cartouches restantes (loose) par produit pour un magasin donné.
     */
    public function getStockMagasinCartouches(int $magasinId): array
    {
        $cpcCase = "CASE WHEN produits.a_cartouche = 1 AND produits.cartouche_par_carton > 0 THEN produits.cartouche_par_carton ELSE 1 END";

        return StockMouvement::where('magasin_id', $magasinId)
            ->join('produits', 'produits.id', '=', 'stock_mouvements.produit_id')
            ->select('produit_id')
            ->selectRaw("MOD(GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * {$cpcCase} + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * {$cpcCase} + quantite_cartouche)
                ELSE 0
            END)), MAX({$cpcCase})) as reste")
            ->groupBy('produit_id')
            ->get()
            ->keyBy('produit_id')
            ->map(fn($m) => (int) $m->reste)
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
                'date_transfert'         => $data['date_transfert'] ?? now(),
                'notes'                  => $data['notes'] ?? null,
            ]);

            foreach ($produits as $p) {
                // Ligne de transfert
                $transfert->produits()->create([
                    'produit_id' => $p['produit_id'],
                    'quantite'   => $p['quantite'],
                ]);

                // Sortie du magasin source uniquement.
                // Le stock n'entre dans le magasin destination qu'à la réception (voir receptionner).
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
            }

            return $transfert;
        });
    }

    /**
     * Valide la réception d'un transfert en transit.
     * $quantitesRecues : map [produit_id => quantite_reellement_recue].
     * Permet de corriger les écarts de comptage (ex: déclaré 120, reçu 119/121) :
     *  - le magasin destination reçoit la quantité réelle,
     *  - le magasin source est réconcilié (retour ou sortie complémentaire)
     *    pour que le stock soit exact aux deux endroits.
     */
    public function receptionner(Transfert $transfert, array $quantitesRecues = []): Transfert
    {
        if ($transfert->statut !== 'en_transit') {
            throw new \Exception('Seuls les transferts en transit peuvent être réceptionnés.');
        }

        return DB::transaction(function () use ($transfert, $quantitesRecues) {
            foreach ($transfert->produits as $tp) {
                $recue = isset($quantitesRecues[$tp->produit_id])
                    ? max(0, (int) $quantitesRecues[$tp->produit_id])
                    : $tp->quantite;

                $tp->quantite_recue = $recue;
                $tp->save();

                // Entrée dans le magasin destination (quantité réellement reçue)
                StockMouvement::create([
                    'tenant_id'      => $transfert->tenant_id,
                    'magasin_id'     => $transfert->magasin_destination_id,
                    'produit_id'     => $tp->produit_id,
                    'user_id'        => $transfert->user_id,
                    'type'           => 'transfert_entree',
                    'quantite'       => $recue,
                    'reference_type' => Transfert::class,
                    'reference_id'   => $transfert->id,
                    'note'           => "Transfert {$transfert->reference} entrée (réception)",
                ]);

                // Réconciliation du magasin source
                // déclaré = $tp->quantite (déjà sorti du source), reçu = $recue
                $ecart = $tp->quantite - $recue; // >0 : il manque du stock (retour au source) ; <0 : surplus sorti
                if ($ecart !== 0) {
                    StockMouvement::create([
                        'tenant_id'      => $transfert->tenant_id,
                        'magasin_id'     => $transfert->magasin_source_id,
                        'produit_id'     => $tp->produit_id,
                        'user_id'        => $transfert->user_id,
                        'type'           => $ecart > 0 ? 'transfert_entree' : 'transfert_sortie',
                        'quantite'       => abs($ecart),
                        'reference_type' => Transfert::class,
                        'reference_id'   => $transfert->id,
                        'note'           => "Transfert {$transfert->reference} réconciliation source",
                    ]);
                }
            }

            $transfert->statut         = 'livre';
            $transfert->date_livraison = now();
            $transfert->save();

            return $transfert;
        });
    }

    /**
     * Met à jour un transfert tant qu'il est en transit (avant réception).
     * Annule les anciens mouvements de sortie, recrée les lignes et les sorties source.
     */
    public function mettreAjour(Transfert $transfert, array $produits, array $data = []): Transfert
    {
        if ($transfert->statut !== 'en_transit') {
            throw new \Exception('Seuls les transferts en transit peuvent être modifiés.');
        }

        return DB::transaction(function () use ($transfert, $produits, $data) {
            // Annuler les anciens mouvements liés à ce transfert (sorties source)
            StockMouvement::where('reference_type', Transfert::class)
                ->where('reference_id', $transfert->id)
                ->delete();

            // Supprimer les anciennes lignes produits
            $transfert->produits()->delete();

            if (isset($data['magasin_source_id'])) {
                $transfert->magasin_source_id = $data['magasin_source_id'];
            }
            if (isset($data['magasin_destination_id'])) {
                $transfert->magasin_destination_id = $data['magasin_destination_id'];
            }
            if (array_key_exists('notes', $data)) {
                $transfert->notes = $data['notes'];
            }
            $transfert->save();

            foreach ($produits as $p) {
                $transfert->produits()->create([
                    'produit_id' => $p['produit_id'],
                    'quantite'   => $p['quantite'],
                ]);

                StockMouvement::create([
                    'tenant_id'      => $transfert->tenant_id,
                    'magasin_id'     => $transfert->magasin_source_id,
                    'produit_id'     => $p['produit_id'],
                    'user_id'        => $transfert->user_id,
                    'type'           => 'transfert_sortie',
                    'quantite'       => $p['quantite'],
                    'reference_type' => Transfert::class,
                    'reference_id'   => $transfert->id,
                    'note'           => "Transfert {$transfert->reference} sortie (modifié)",
                ]);
            }

            return $transfert;
        });
    }

    /**
     * Ajustement manuel du stock (inventaire).
     * $quantiteReelleCartons / $quantiteReelleCartouches = comptage physique réel.
     * L'écart est calculé en cartouches pour préserver les cartouches isolées.
     */
    public function ajuster(
        int $tenantId, int $magasinId, int $produitId,
        int $quantiteReelleCartons, int $userId, string $note = '',
        int $quantiteReelleCartouches = 0
    ): void {
        $produit = Produit::find($produitId);
        $cpc = $produit ? $produit->cartouchesParCartonEffectif() : 1;

        $actuel = $this->getStockDetail($magasinId, $produitId);
        $actuelCartouches = $actuel['cartons'] * $cpc + $actuel['cartouches'];
        $reelleCartouches = $quantiteReelleCartons * $cpc + $quantiteReelleCartouches;
        $diff = $reelleCartouches - $actuelCartouches;

        if ($diff === 0) return;

        $mag = abs($diff);
        $cartons = intdiv($mag, $cpc);
        $cartouches = $mag % $cpc;

        StockMouvement::create([
            'tenant_id'          => $tenantId,
            'magasin_id'         => $magasinId,
            'produit_id'         => $produitId,
            'user_id'            => $userId,
            'type'               => $diff > 0 ? 'ajustement_positif' : 'ajustement_negatif',
            'quantite'           => $cartons,
            'quantite_cartouche' => $cartouches,
            'note'               => $note ?: "Ajustement inventaire",
        ]);
    }
}
