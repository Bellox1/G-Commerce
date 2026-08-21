<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\DetteSociete;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ArrivageService;
use App\Services\VenteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'mantinoubello123@gmail.com')->first();
        if (!$admin || !$admin->tenant) {
            $this->command->warn('Aucun tenant trouvé (mantinoubello123@gmail.com). Le seeder principal doit tourner avant.');
            return;
        }
        $tenant = $admin->tenant;
        $userId = $admin->id;

        // Évite les doublons si déjà exécuté
        if (Produit::where('tenant_id', $tenant->id)->where('nom', 'Riz 25kg')->exists()) {
            $this->command->info('Données de démo déjà présentes pour le tenant « ' . $tenant->nom . ' » — ignoré.');
            return;
        }

        $this->command->info('Création des données de démo pour le tenant « ' . $tenant->nom . ' »...');

        // ── Magasins ───────────────────────────────────────────────
        $mag1 = Magasin::firstOrCreate(
            ['tenant_id' => $tenant->id, 'nom' => 'Magasin Principal'],
            ['ville' => 'Dakar', 'loyer' => 250000]
        );
        $mag2 = Magasin::firstOrCreate(
            ['tenant_id' => $tenant->id, 'nom' => 'Magasin Plateau'],
            ['ville' => 'Dakar', 'loyer' => 180000]
        );

        // ── Fournisseur ────────────────────────────────────────────
        $fournisseur = Fournisseur::firstOrCreate(
            ['tenant_id' => $tenant->id, 'nom' => 'Import Demo SARL'],
            ['pays' => 'Chine', 'ville' => 'Shanghai', 'telephone' => '771234567', 'email' => 'contact@importdemo.com', 'actif' => true]
        );

        // ── Clients ────────────────────────────────────────────────
        $client1 = Client::firstOrCreate(
            ['tenant_id' => $tenant->id, 'nom' => 'Boutique Centrale'],
            ['telephone' => '770000001', 'ville' => 'Dakar', 'actif' => true]
        );
        $client2 = Client::firstOrCreate(
            ['tenant_id' => $tenant->id, 'nom' => 'Marché Sandaga'],
            ['telephone' => '770000002', 'ville' => 'Dakar', 'actif' => true]
        );

        // ── Produits ───────────────────────────────────────────────
        $produits = [
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Riz 25kg'],
                ['prix_vente_conseille' => 15000, 'seuil_alerte' => 10, 'actif' => true, 'stock' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Huile 20L'],
                ['prix_vente_conseille' => 22000, 'seuil_alerte' => 5, 'actif' => true, 'stock' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Sucre 50kg'],
                ['prix_vente_conseille' => 30000, 'seuil_alerte' => 8, 'actif' => true, 'stock' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Savon Carton x24'],
                ['prix_vente_conseille' => 12000, 'seuil_alerte' => 5, 'actif' => true, 'stock' => 0,
                 'a_cartouche' => true, 'cartouche_par_carton' => 24, 'prix_cartouche' => 600, 'stock_cartouches' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Jus Carton x12'],
                ['prix_vente_conseille' => 9000, 'seuil_alerte' => 6, 'actif' => true, 'stock' => 0,
                 'a_cartouche' => true, 'cartouche_par_carton' => 12, 'prix_cartouche' => 800, 'stock_cartouches' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Lait Carton x48'],
                ['prix_vente_conseille' => 36000, 'seuil_alerte' => 4, 'actif' => true, 'stock' => 0,
                 'a_cartouche' => true, 'cartouche_par_carton' => 48, 'prix_cartouche' => 800, 'stock_cartouches' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Farine 10kg'],
                ['prix_vente_conseille' => 8000, 'seuil_alerte' => 10, 'actif' => true, 'stock' => 0]
            ),
            Produit::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Eau 1.5L Carton x6'],
                ['prix_vente_conseille' => 3000, 'seuil_alerte' => 12, 'actif' => true, 'stock' => 0,
                 'a_cartouche' => true, 'cartouche_par_carton' => 6, 'prix_cartouche' => 600, 'stock_cartouches' => 0]
            ),
        ];
        $p = collect($produits);

        // ── Arrivages (réception) ──────────────────────────────────
        $arrivageService = app(ArrivageService::class);

        $arr1 = $arrivageService->creer([
            'tenant_id'     => $tenant->id,
            'magasin_id'    => $mag1->id,
            'fournisseur_id'=> $fournisseur->id,
            'user_id'       => $userId,
            'date_arrivage' => now()->subDays(10)->format('Y-m-d'),
            'pays_origine'  => 'Sénégal',
            'devise_origine'=> 'FCFA',
            'devise_locale' => 'FCFA',
            'taux_change'   => 1,
            'statut'        => 'en_cours',
        ], [
            ['produit_id' => $p[0]->id, 'quantite' => 60, 'prix_unitaire_origine' => 11000, 'prix_vente_suggere' => 15000, 'fournisseur_id' => $fournisseur->id],
            ['produit_id' => $p[1]->id, 'quantite' => 40, 'prix_unitaire_origine' => 17000, 'prix_vente_suggere' => 22000, 'fournisseur_id' => $fournisseur->id],
            ['produit_id' => $p[2]->id, 'quantite' => 30, 'prix_unitaire_origine' => 24000, 'prix_vente_suggere' => 30000, 'fournisseur_id' => $fournisseur->id],
            ['produit_id' => $p[3]->id, 'quantite' => 20, 'prix_unitaire_origine' => 9000, 'prix_vente_suggere' => 12000, 'fournisseur_id' => $fournisseur->id],
        ]);
        $arrivageService->valider($arr1);

        $arr2 = $arrivageService->creer([
            'tenant_id'     => $tenant->id,
            'magasin_id'    => $mag2->id,
            'fournisseur_id'=> $fournisseur->id,
            'user_id'       => $userId,
            'date_arrivage' => now()->subDays(3)->format('Y-m-d'),
            'pays_origine'  => 'Sénégal',
            'devise_origine'=> 'FCFA',
            'devise_locale' => 'FCFA',
            'taux_change'   => 1,
            'statut'        => 'en_cours',
        ], [
            ['produit_id' => $p[4]->id, 'quantite' => 25, 'prix_unitaire_origine' => 6500, 'prix_vente_suggere' => 9000, 'fournisseur_id' => $fournisseur->id],
            ['produit_id' => $p[5]->id, 'quantite' => 15, 'prix_unitaire_origine' => 28000, 'prix_vente_suggere' => 36000, 'fournisseur_id' => $fournisseur->id],
            ['produit_id' => $p[6]->id, 'quantite' => 50, 'prix_unitaire_origine' => 6000, 'prix_vente_suggere' => 8000, 'fournisseur_id' => $fournisseur->id],
            ['produit_id' => $p[7]->id, 'quantite' => 35, 'prix_unitaire_origine' => 2200, 'prix_vente_suggere' => 3000, 'fournisseur_id' => $fournisseur->id],
        ]);
        $arrivageService->valider($arr2);

        // ── Ventes (dont livraisons) ───────────────────────────────
        $venteService = app(VenteService::class);

        // Vente A — aujourd'hui, livrée
        $venteService->creer([
            'tenant_id'          => $tenant->id,
            'magasin_id'         => $mag1->id,
            'user_id'            => $userId,
            'client_id'          => $client1->id,
            'date_vente'         => now()->format('Y-m-d'),
            'montant_paye'       => 99999,
            'statut_livraison'   => 'livre',
        ], [
            ['produit_id' => $p[0]->id, 'quantite' => 2, 'prix_vente' => 15000, 'unite' => 'carton'],
            ['produit_id' => $p[3]->id, 'quantite' => 3, 'prix_vente' => 12000, 'unite' => 'carton'],
            ['produit_id' => $p[4]->id, 'quantite' => 5, 'prix_vente' => 800, 'unite' => 'cartouche'],
        ]);

        // Vente B — il y a 2 jours, partielle, en attente de livraison
        $venteService->creer([
            'tenant_id'          => $tenant->id,
            'magasin_id'         => $mag2->id,
            'user_id'            => $userId,
            'client_id'          => $client2->id,
            'date_vente'         => now()->subDays(2)->format('Y-m-d'),
            'montant_paye'       => 20000,
            'statut_livraison'   => 'en_attente',
            'date_livraison'     => now()->addDays(2)->format('Y-m-d'),
        ], [
            ['produit_id' => $p[1]->id, 'quantite' => 1, 'prix_vente' => 22000, 'unite' => 'carton'],
            ['produit_id' => $p[5]->id, 'quantite' => 10, 'prix_vente' => 800, 'unite' => 'cartouche'],
        ]);

        // Vente C — il y a 5 jours, livrée, payée
        $venteService->creer([
            'tenant_id'          => $tenant->id,
            'magasin_id'         => $mag1->id,
            'user_id'            => $userId,
            'client_id'          => $client1->id,
            'date_vente'         => now()->subDays(5)->format('Y-m-d'),
            'montant_paye'       => 99999,
            'statut_livraison'   => 'livre',
        ], [
            ['produit_id' => $p[2]->id, 'quantite' => 2, 'prix_vente' => 30000, 'unite' => 'carton'],
            ['produit_id' => $p[6]->id, 'quantite' => 3, 'prix_vente' => 8000, 'unite' => 'carton'],
        ]);

        // Vente D — hier, anonyme, en cours de livraison
        $venteService->creer([
            'tenant_id'          => $tenant->id,
            'magasin_id'         => $mag1->id,
            'user_id'            => $userId,
            'date_vente'         => now()->subDays(1)->format('Y-m-d'),
            'montant_paye'       => 15000,
            'statut_livraison'   => 'en_attente',
        ], [
            ['produit_id' => $p[0]->id, 'quantite' => 1, 'prix_vente' => 15000, 'unite' => 'carton'],
        ]);

        // Vente E — aujourd'hui, en attente de livraison (cartouches)
        $venteService->creer([
            'tenant_id'          => $tenant->id,
            'magasin_id'         => $mag2->id,
            'user_id'            => $userId,
            'client_id'          => $client2->id,
            'date_vente'         => now()->format('Y-m-d'),
            'montant_paye'       => 12000,
            'statut_livraison'   => 'en_attente',
            'date_livraison'     => now()->addDay()->format('Y-m-d'),
        ], [
            ['produit_id' => $p[7]->id, 'quantite' => 4, 'prix_vente' => 3000, 'unite' => 'carton'],
        ]);

        // ── Dettes envers les fournisseurs (avec devise d'origine) ──
        $dettesSociete = [
            [
                'fournisseur_id'  => $fournisseur->id,
                'devise'          => 'EUR',
                'taux_de_change'  => 655.957,
                'montant_origine' => 1500,
                'description'     => 'Conteneur Riz & Huile',
                'date_dette'      => now()->subDays(20)->format('Y-m-d'),
            ],
            [
                'fournisseur_id'  => $fournisseur->id,
                'devise'          => 'USD',
                'taux_de_change'  => 600,
                'montant_origine' => 3200,
                'description'     => 'Achat groupé de produits',
                'date_dette'      => now()->subDays(12)->format('Y-m-d'),
            ],
            [
                'fournisseur_id'  => $fournisseur->id,
                'devise'          => 'FCFA',
                'taux_de_change'  => 1,
                'montant_origine' => 450000,
                'description'     => 'Régularisation locale',
                'date_dette'      => now()->subDays(5)->format('Y-m-d'),
            ],
        ];

        foreach ($dettesSociete as $d) {
            $d['tenant_id']    = $tenant->id;
            $d['montant']      = round((float) $d['montant_origine'] * (float) $d['taux_de_change'], 2);
            $d['montant_paye'] = 0;
            $d['statut']       = 'en_cours';
            DetteSociete::create($d);
        }

        $this->command->info('Données de démo créées : ' . $p->count() . ' produits, 2 arrivages, 5 ventes/livraisons, ' . count($dettesSociete) . ' dettes fournisseur.');
    }
}
