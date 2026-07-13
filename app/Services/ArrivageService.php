<?php

namespace App\Services;

use App\Models\Arrivage;
use App\Models\ArrivageProduit;
use App\Models\DetteSociete;
use App\Models\Produit;
use App\Models\StockMouvement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArrivageService
{
    /**
     * Crée un arrivage complet avec ses produits
     *
     * @param array $data    Données de l'arrivage
     * @param array $produits [ ['produit_id', 'quantite', 'prix_unitaire_origine'], ... ]
     */
    public function creer(array $data, array $produits): Arrivage
    {
        return DB::transaction(function () use ($data, $produits) {
            // Générer référence
            $data['reference'] = $this->genererReference();

            $arrivage = Arrivage::create($data);

            foreach ($produits as $p) {
                $totalOrigine = $p['quantite'] * $p['prix_unitaire_origine'];
                ArrivageProduit::create([
                    'arrivage_id'          => $arrivage->id,
                    'produit_id'           => $p['produit_id'],
                    'fournisseur_id'       => $p['fournisseur_id'] ?? null,
                    'quantite'             => $p['quantite'],
                    'prix_unitaire_origine'=> $p['prix_unitaire_origine'],
                    'total_origine'        => $totalOrigine,
                ]);
            }

            // Calculer les totaux et répartir les frais
            $arrivage->load('produits');
            $arrivage->recalculer();

            // Créer automatiquement la dette société (ce que la société doit pour cet arrivage)
            DetteSociete::create([
                'tenant_id'     => $arrivage->tenant_id,
                'fournisseur_id'=> $arrivage->fournisseur_id,
                'arrivage_id'   => $arrivage->id,
                'montant'       => $arrivage->total_cout_reel,
                'montant_paye'  => 0,
                'description'   => "Arrivage {$arrivage->reference}",
                'date_dette'    => now(),
                'statut'        => 'en_cours',
            ]);

            return $arrivage;
        });
    }

    /**
     * Valide un arrivage (stock entré = réceptionné)
     */
    public function valider(Arrivage $arrivage): void
    {
        DB::transaction(function () use ($arrivage) {
            foreach ($arrivage->produits as $ligne) {
                // Créer le mouvement de stock
                StockMouvement::create([
                    'tenant_id'      => $arrivage->tenant_id,
                    'magasin_id'     => $arrivage->magasin_id,
                    'produit_id'     => $ligne->produit_id,
                    'user_id'        => $arrivage->user_id,
                    'type'           => 'entree_arrivage',
                    'quantite'       => $ligne->quantite,
                    'cout_unitaire'  => $ligne->cout_unitaire_reel,
                    'reference_type' => Arrivage::class,
                    'reference_id'   => $arrivage->id,
                    'note'           => "Arrivage {$arrivage->reference}",
                ]);

                // Mettre à jour le prix conseillé du produit
                $produit = Produit::find($ligne->produit_id);
                if ($produit) {
                    $produit->prix_vente_conseille = $ligne->prix_vente_suggere;
                    $produit->save();
                }
            }

            $arrivage->statut = 'receptionne';
            $arrivage->save();
        });
    }

    private function genererReference(): string
    {
        $annee = now()->year;
        $count = Arrivage::whereYear('created_at', $annee)->count() + 1;
        return sprintf('ARR-%d-%03d', $annee, $count);
    }
}
