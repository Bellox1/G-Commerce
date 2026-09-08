<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Dette;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Vente;
use App\Models\VenteLigne;
use Illuminate\Support\Facades\DB;

class VenteService
{
    /**
     * Créer une vente complète
     *
     * @param array $data    Données vente (tenant_id, magasin_id, user_id, ...)
     * @param array $lignes  [ ['produit_id', 'quantite', 'prix_vente'], ... ]
     */
    public function creer(array $data, array $lignes): Vente
    {
        return DB::transaction(function () use ($data, $lignes) {
            // Calculer le total
            $total = 0;
            $lignesCalculees = [];

            foreach ($lignes as $l) {
                $produit = Produit::findOrFail($l['produit_id']);
                $unite = $l['unite'] ?? 'carton';
                $cartoucheParCarton = max(1, (int) ($produit->cartouche_par_carton ?? 1));
                $prixVente = (float) $l['prix_vente'];
                $totalLigne = $prixVente * $l['quantite'];
                $total += $totalLigne;

                $qteCarton    = ($unite === 'cartouche') ? 0 : (int) $l['quantite'];
                $qteCartouche = ($unite === 'cartouche') ? (int) $l['quantite'] : 0;

                $prixConseille = ($unite === 'cartouche')
                    ? ($produit->prix_cartouche ?: (int) ceil(($produit->prix_vente_conseille / $cartoucheParCarton) / 100) * 100)
                    : $produit->prix_vente_conseille;

                $lignesCalculees[] = [
                    'produit_id'     => $l['produit_id'],
                    'quantite'       => $l['quantite'],
                    'prix_conseille' => $prixConseille,
                    'prix_vente'     => $prixVente,
                    'cout_unitaire'  => $prixConseille,
                    'total_ligne'    => $totalLigne,
                    'unite'          => $unite,
                    '_qte_carton'    => $qteCarton,
                    '_qte_cartouche' => $qteCartouche,
                ];
            }

            $montantPaye  = (float) ($data['montant_paye'] ?? $total);
            $montantReste = max(0, $total - $montantPaye);

            $dateEcheance = $data['date_echeance'] ?? null;
            unset($data['date_echeance']);

            // Générer la référence DANS la transaction avec INSERT ... ON DUPLICATE KEY UPDATE (atomique)
            $annee = now()->year;
            $prefix = "VNT-{$annee}-";

            // Atomic increment: insère si n'existe pas, sinon incrémente
            DB::statement(
                'INSERT INTO vente_counters (annee, counter, created_at, updated_at) VALUES (?, 1, NOW(), NOW()) 
                 ON DUPLICATE KEY UPDATE counter = counter + 1, updated_at = NOW()',
                [$annee]
            );

            $counter = DB::table('vente_counters')
                ->where('annee', $annee)
                ->value('counter');

            $data['reference'] = sprintf('VNT-%d-%05d', $annee, $counter);

            $data['montant_total']   = $total;
            $data['montant_paye']    = $montantPaye;
            $data['montant_reste']   = $montantReste;
            $data['statut_paiement'] = $montantReste <= 0 ? 'paye' : ($montantPaye > 0 ? 'partiel' : 'impaye');

            $vente = Vente::create($data);

            foreach ($lignesCalculees as $ligne) {
                $qteCarton = $ligne['_qte_carton'];
                $qteCartouche = $ligne['_qte_cartouche'];
                unset($ligne['_qte_carton'], $ligne['_qte_cartouche']);

                VenteLigne::create(array_merge($ligne, ['vente_id' => $vente->id]));

                $noteExtra = $ligne['unite'] === 'cartouche' ? " ({$ligne['quantite']} cartouche(s))" : " ({$ligne['quantite']} carton(s))";

                // Mouvement de stock : sortie (le signe est appliqué via le type).
                // Les cartouches isolées sont enregistrées dans quantite_cartouche
                // pour ne pas perdre le reste du carton ouvert.
                StockMouvement::create([
                    'tenant_id'         => $data['tenant_id'],
                    'magasin_id'        => $data['magasin_id'],
                    'produit_id'        => $ligne['produit_id'],
                    'user_id'           => $data['user_id'],
                    'type'              => 'sortie_vente',
                    'quantite'          => $qteCarton,
                    'quantite_cartouche'=> $qteCartouche,
                    'cout_unitaire'     => $ligne['cout_unitaire'],
                    'reference_type'    => Vente::class,
                    'reference_id'      => $vente->id,
                    'note'              => "Vente {$vente->reference}{$noteExtra}",
                ]);
            }

            // Créer dette si paiement partiel ou impayé
            if ($montantReste > 0) {
                Dette::create([
                    'tenant_id'       => $data['tenant_id'],
                    'client_id'       => $data['client_id'] ?? null,
                    'vente_id'        => $vente->id,
                    'montant_initial' => $montantReste,
                    'montant_paye'    => 0,
                    'montant_restant' => $montantReste,
                    'date_echeance'   => $dateEcheance,
                    'statut'          => $montantPaye > 0 ? 'partiel' : 'en_cours',
                    'notes'           => $data['client_id'] ? null : 'Client anonyme',
                ]);
            }

            return $vente->load('lignes', 'client');
        });
    }
}
