<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\Magasin;
use App\Models\User;
use App\Models\Client;
use App\Models\Produit;
use App\Models\Fournisseur;
use App\Models\Arrivage;
use App\Models\ArrivageProduit;
use App\Models\Vente;
use App\Models\VenteLigne;
use App\Models\Dette;
use App\Models\DettePaiement;
use App\Models\Transfert;
use App\Models\TransfertProduit;
use App\Models\StockMouvement;
use Carbon\Carbon;

class MassSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 MassSeeder — Démarrage...');

        // ─── Récupérer le tenant SAÏMOUS ───
        $tenant = Tenant::where('nom', 'Ma société')->firstOrFail();
        $magasins = Magasin::where('tenant_id', $tenant->id)->get();
        $magasin1 = $magasins->first(); // Dépôt Saint Michel
        $magasin2 = $magasins->last();  // Dépôt Dédokpo
        $produits = Produit::where('tenant_id', $tenant->id)->get();
        $admin = User::where('email', 'mantinoubello123@gmail.com')->first();

        // 🧹 Nettoyage préalable des anciennes données MASS
        $this->command->info('🧹 Nettoyage des anciennes données MASS...');
        \App\Models\DepenseJournaliere::where('tenant_id', $tenant->id)->delete();
        DettePaiement::whereHas('dette', fn($q) => $q->whereHas('vente', fn($v) => $v->where('reference', 'like', 'VNT-MASS-%')))->delete();
        Dette::whereHas('vente', fn($v) => $v->where('reference', 'like', 'VNT-MASS-%'))->delete();
        VenteLigne::whereHas('vente', fn($v) => $v->where('reference', 'like', 'VNT-MASS-%'))->delete();
        StockMouvement::where('note', 'like', '%MASS%')->delete();
        Vente::where('reference', 'like', 'VNT-MASS-%')->forceDelete();
        ArrivageProduit::whereHas('arrivage', fn($a) => $a->where('reference', 'like', 'ARR-%'))->delete();
        Arrivage::where('reference', 'like', 'ARR-%')->forceDelete();
        TransfertProduit::whereHas('transfert', fn($t) => $t->where('reference', 'like', 'TRF-MASS-%'))->delete();
        Transfert::where('reference', 'like', 'TRF-MASS-%')->forceDelete();

        // ════════════════════════════════════════════
        // 0. SEUILS D'ALERTE & STOCK INITIAL DES PRODUITS
        // ════════════════════════════════════════════
        $this->command->info('⚙️ Configuration des seuils d\'alerte et stocks initiaux des produits...');

        foreach ($produits as $produit) {
            $produit->update([
                'seuil_alerte' => 10,
            ]);
        }

        // ════════════════════════════════════════════
        // 1. PERSONNEL — 10 employés
        // ════════════════════════════════════════════
        $this->command->info('👥 Création de 10 employés...');

        $personnelData = [
            ['name' => 'Abdoulaye KONE',     'email' => 'abdoulaye.kone@saimous.test',     'role' => 'vendeur',     'magasin' => $magasin1],
            ['name' => 'Fatoumata DIALLO',   'email' => 'fatoumata.diallo@saimous.test',   'role' => 'vendeur',     'magasin' => $magasin2],
            ['name' => 'Moussa TRAORE',      'email' => 'moussa.traore@saimous.test',      'role' => 'controleur',  'magasin' => $magasin1],
            ['name' => 'Aminata SYLLA',      'email' => 'aminata.sylla@saimous.test',      'role' => 'controleur',  'magasin' => $magasin2],
            ['name' => 'Ibrahim SECK',       'email' => 'ibrahim.seck@saimous.test',       'role' => 'magasinier',  'magasin' => $magasin1],
            ['name' => 'Aissatou BA',        'email' => 'aissatou.ba@saimous.test',        'role' => 'magasinier',  'magasin' => $magasin2],
            ['name' => 'Ousmane CISSE',      'email' => 'ousmane.cisse@saimous.test',      'role' => 'vendeur',     'magasin' => $magasin1, 'roles_secondaires' => ['magasinier']],
            ['name' => 'Kadiatou BARRY',     'email' => 'kadiatou.barry@saimous.test',     'role' => 'vendeur',     'magasin' => $magasin2, 'roles_secondaires' => ['magasinier']],
            ['name' => 'Sékou CAMARA',       'email' => 'sekou.camara@saimous.test',       'role' => 'vendeur',     'magasin' => $magasin1],
            ['name' => 'Mariam TOURE',       'email' => 'mariam.toure@saimous.test',       'role' => 'vendeur',     'magasin' => $magasin2],
        ];

        $personnel = [];
        foreach ($personnelData as $p) {
            $personnel[] = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'               => $p['name'],
                    'password'           => Hash::make('password123'),
                    'role'               => $p['role'],
                    'roles_secondaires'  => $p['roles_secondaires'] ?? null,
                    'tenant_id'          => $tenant->id,
                    'magasin_id'         => $p['magasin']->id,
                    'actif'              => true,
                    'salaire'            => rand(80000, 200000),
                ]
            );
        }
        $vendeurs = collect($personnel)->filter(fn($u) => $u->role === 'vendeur' || $u->role === 'controleur');

        // ════════════════════════════════════════════
        // 2. CLIENTS — 10 clients avec limites de crédit variées
        // ════════════════════════════════════════════
        $this->command->info('🧑‍💼 Création de 10 clients...');

        $clientsData = [
            ['nom' => 'Maman Dohou',         'telephone' => '22961000001', 'limite_credit' => 500000],
            ['nom' => 'Papa Bio',            'telephone' => '22961000002', 'limite_credit' => 300000],
            ['nom' => 'M. Koffi Kodjo',      'telephone' => '22961000003', 'limite_credit' => 200000],
            ['nom' => 'Mme Mensah Abla',     'telephone' => '22961000004', 'limite_credit' => 150000],
            ['nom' => 'Maman Cica',          'telephone' => '22961000005', 'limite_credit' => 100000],
            ['nom' => 'Papa Soglo',          'telephone' => '22961000006', 'limite_credit' => 75000],
            ['nom' => 'Mme Lawson Fati',     'telephone' => '22961000007', 'limite_credit' => 250000],
            ['nom' => 'M. Dossou Yves',      'telephone' => '22961000008', 'limite_credit' => 400000],
            ['nom' => 'Maman Zinsou',        'telephone' => '22961000009', 'limite_credit' => 50000],
            ['nom' => 'Mme Adjanohoun Rose', 'telephone' => '22961000010', 'limite_credit' => 180000],
        ];

        $clients = [];
        foreach ($clientsData as $c) {
            $clients[] = Client::firstOrCreate(
                ['tenant_id' => $tenant->id, 'telephone' => $c['telephone']],
                [
                    'nom'            => $c['nom'],
                    'limite_credit'  => $c['limite_credit'],
                ]
            );
        }

        // ════════════════════════════════════════════
        // 3. FOURNISSEURS
        // ════════════════════════════════════════════
        $fournisseurs = Fournisseur::where('tenant_id', $tenant->id)->get();
        if ($fournisseurs->isEmpty()) {
            $fournisseurs = collect([
                Fournisseur::firstOrCreate(['nom' => 'SDTF Nigeria', 'tenant_id' => $tenant->id], ['telephone' => '2348001111']),
                Fournisseur::firstOrCreate(['nom' => 'Import Global SA', 'tenant_id' => $tenant->id], ['telephone' => '2298002222']),
                Fournisseur::firstOrCreate(['nom' => 'Négoce Afrique', 'tenant_id' => $tenant->id], ['telephone' => '2298003333']),
            ]);
        }

        // ════════════════════════════════════════════
        // 4. ARRIVAGES — 11 arrivages sur 3 mois
        // ════════════════════════════════════════════
        $this->command->info('📦 Création de 11 arrivages réceptionnés + 2 en attente...');

        $baseDate = Carbon::now()->subMonths(3);
        $arrivageDates = [];
        for ($i = 0; $i < 11; $i++) {
            $arrivageDates[] = $baseDate->copy()->addDays($i * 8 + rand(0, 3));
        }

        $arrivages = [];
        foreach ($arrivageDates as $idx => $date) {
            $ref = 'ARR-MASS-' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);

            if (Arrivage::where('reference', $ref)->exists()) {
                continue;
            }

            $fournisseur = $fournisseurs->random();
            $magasin = $idx % 3 === 0 ? $magasin2 : $magasin1;
            $isNigeria = $fournisseur->nom === 'SDTF Nigeria';

            $fraisTransport = rand(25000, 150000);
            $fraisDouane = rand(50000, 300000);
            $fraisManutention = rand(10000, 50000);
            $totalFrais = $fraisTransport + $fraisDouane + $fraisManutention;

            $arrivage = Arrivage::create([
                'reference'          => $ref,
                'date_arrivage'      => $date,
                'fournisseur_id'     => $fournisseur->id,
                'statut'             => 'receptionne',
                'devise_origine'     => $isNigeria ? 'NGN' : 'XOF',
                'devise_locale'      => 'XOF',
                'taux_change'        => $isNigeria ? rand(3, 5) : 1,
                'frais_transport'    => $fraisTransport,
                'frais_douane'       => $fraisDouane,
                'frais_manutention'  => $fraisManutention,
                'total_frais'        => $totalFrais,
                'tenant_id'          => $tenant->id,
                'magasin_id'         => $magasin->id,
                'user_id'            => $admin?->id,
                'commentaire'        => 'Arrivage massif #' . ($idx + 1),
            ]);

            // Ajouter 5-10 produits par arrivage
            $selectedProduits = $produits->random(rand(5, min(10, $produits->count())));

            foreach ($selectedProduits as $produit) {
                $quantite = rand(10, 100);
                $prixUnitaireOrigine = rand(500, 15000);
                $totalOrigine = $quantite * $prixUnitaireOrigine;

                ArrivageProduit::create([
                    'arrivage_id'           => $arrivage->id,
                    'produit_id'            => $produit->id,
                    'fournisseur_id'        => $fournisseur->id,
                    'quantite'              => $quantite,
                    'prix_unitaire_origine' => $prixUnitaireOrigine,
                    'total_origine'         => $totalOrigine,
                    'valeur_fcfa'           => $totalOrigine * $arrivage->taux_change,
                    'prix_vente_suggere'    => Produit::arrondir(($totalOrigine * $arrivage->taux_change / $quantite) * 1.25),
                ]);

                // Stock mouvement
                StockMouvement::create([
                    'produit_id'      => $produit->id,
                    'magasin_id'      => $magasin->id,
                    'tenant_id'       => $tenant->id,
                    'user_id'         => $admin?->id,
                    'type'            => 'entree_arrivage',
                    'quantite'        => $quantite,
                    'reference_type'  => Arrivage::class,
                    'reference_id'    => $arrivage->id,
                    'note'            => 'Entrée arrivage MASS #' . ($idx + 1),
                ]);
            }

            $arrivage->load('produits');
            $arrivage->recalculer();
            $arrivages[] = $arrivage;
        }

        // ─── 2 arrivages EN COURS (pas encore réceptionnés) ───
        foreach ([1, 2] as $n) {
            $ref = 'ARR-PEND-00' . $n;
            if (!Arrivage::where('reference', $ref)->exists()) {
                $fournisseur = $fournisseurs->random();
                $magasin    = $n === 1 ? $magasin1 : $magasin2;
                $isNigeria  = $fournisseur->nom === 'SDTF Nigeria';

                $fraisTransport   = rand(25000, 150000);
                $fraisDouane      = rand(50000, 300000);
                $fraisManutention = rand(10000, 50000);
                $totalFrais       = $fraisTransport + $fraisDouane + $fraisManutention;

                $pending = Arrivage::create([
                    'reference'         => $ref,
                    'date_arrivage'     => Carbon::now()->addDays(rand(3, 10)),
                    'fournisseur_id'    => $fournisseur->id,
                    'statut'            => 'en_cours',
                    'devise_origine'    => $isNigeria ? 'NGN' : 'XOF',
                    'devise_locale'     => 'XOF',
                    'taux_change'       => $isNigeria ? rand(3, 5) : 1,
                    'frais_transport'   => $fraisTransport,
                    'frais_douane'      => $fraisDouane,
                    'frais_manutention' => $fraisManutention,
                    'total_frais'       => $totalFrais,
                    'tenant_id'         => $tenant->id,
                    'magasin_id'        => $magasin->id,
                    'user_id'           => $admin?->id,
                    'commentaire'       => 'Arrivage en attente de réception #' . $n,
                ]);

                $selectedProduits = $produits->random(rand(4, min(8, $produits->count())));
                foreach ($selectedProduits as $produit) {
                    $quantite           = rand(20, 80);
                    $prixUnitaireOrigine = rand(2000, 20000);
                    $totalOrigine        = $quantite * $prixUnitaireOrigine;
                    ArrivageProduit::create([
                        'arrivage_id'            => $pending->id,
                        'produit_id'             => $produit->id,
                        'fournisseur_id'         => $fournisseur->id,
                        'quantite'               => $quantite,
                        'prix_unitaire_origine'  => $prixUnitaireOrigine,
                        'total_origine'          => $totalOrigine,
                        'valeur_fcfa'            => $totalOrigine * $pending->taux_change,
                        'prix_vente_suggere'     => Produit::arrondir(($totalOrigine * $pending->taux_change / $quantite) * 1.25),
                    ]);
                }

                $pending->load('produits');
                $pending->recalculer();
            }
        }

        // ════════════════════════════════════════════
        // 5. VENTES — 100 ventes avec statuts variés
        // ════════════════════════════════════════════
        $this->command->info('🛒 Création de 100 ventes...');

        $venteConfigs = [];

        // 15 ventes d'aujourd'hui payées
        for ($i = 0; $i < 15; $i++) {
            $venteConfigs[] = ['paiement' => 'paye', 'livraison' => 'livre', 'anonyme' => false, 'today' => true];
        }
        // 5 ventes d'aujourd'hui partielles (créances créées AUJOURD'HUI !)
        for ($i = 0; $i < 5; $i++) {
            $venteConfigs[] = ['paiement' => 'partiel', 'livraison' => 'livre', 'anonyme' => false, 'today' => true];
        }
        // 5 ventes d'aujourd'hui à crédit (créances créées AUJOURD'HUI !)
        for ($i = 0; $i < 5; $i++) {
            $venteConfigs[] = ['paiement' => 'impaye', 'livraison' => 'en_attente', 'anonyme' => false, 'today' => true];
        }
        // 15 ventes payées + non livrées
        for ($i = 0; $i < 15; $i++) {
            $venteConfigs[] = ['paiement' => 'paye', 'livraison' => 'en_attente', 'anonyme' => false];
        }
        // 15 ventes partielles avec échéance sur les mois passés
        for ($i = 0; $i < 15; $i++) {
            $venteConfigs[] = ['paiement' => 'partiel', 'livraison' => 'livre', 'anonyme' => false];
        }
        // 10 ventes impayées (à crédit) sur les mois passés
        for ($i = 0; $i < 10; $i++) {
            $venteConfigs[] = ['paiement' => 'impaye', 'livraison' => 'en_attente', 'anonyme' => false];
        }
        // 15 ventes anonymes (LES ANONYMES PAYENT TOUJOURS TOUT CASH À 100%)
        for ($i = 0; $i < 15; $i++) {
            $venteConfigs[] = ['paiement' => 'paye', 'livraison' => 'livre', 'anonyme' => true];
        }
        // 10 ventes avec problème livraison
        for ($i = 0; $i < 10; $i++) {
            $venteConfigs[] = ['paiement' => 'paye', 'livraison' => 'probleme', 'anonyme' => false];
        }

        shuffle($venteConfigs);
        $venteCounter = 0;

        foreach ($venteConfigs as $config) {
            $venteCounter++;
            $ref = 'VNT-MASS-' . str_pad($venteCounter, 4, '0', STR_PAD_LEFT);

            if (Vente::where('reference', $ref)->exists()) {
                continue;
            }

            $venteDate = $config['today'] ?? false
                ? Carbon::today()->setHour(rand(7, 20))->setMinute(rand(0, 59))
                : Carbon::now()->subDays(rand(1, 90))->setHour(rand(7, 20))->setMinute(rand(0, 59));
            $vendeur = $vendeurs->random();
            $magasin = $vendeur->magasin_id === $magasin1->id ? $magasin1 : $magasin2;
            $client = $config['anonyme'] ? null : $clients[array_rand($clients)];
            if ($config['anonyme']) {
                $config['paiement'] = 'paye';
            }

            // Ventes du jour: 4-8 lignes avec prix plus élevés pour atteindre ≥ 120k par vente
            $isToday = $config['today'] ?? false;
            $nbLignes = $isToday ? rand(4, 8) : rand(1, 6);
            $venteProduits = $produits->random(min($nbLignes, $produits->count()));
            $montantTotal = 0;
            $lignesData = [];

            foreach ($venteProduits as $prod) {
                $qte = $isToday ? rand(5, 20) : rand(1, 10);
                $basePrix = $prod->prix_vente_conseille > 0 ? (float)$prod->prix_vente_conseille : rand(5000, 30000);
                $prixVente = $isToday ? max($basePrix, rand(8000, 35000)) : $basePrix;
                $totalLigne = $qte * $prixVente;
                $montantTotal += $totalLigne;

                $lignesData[] = [
                    'produit_id'     => $prod->id,
                    'quantite'       => $qte,
                    'prix_vente'     => $prixVente,
                    'total_ligne'    => $totalLigne,
                    'unite'          => $prod->a_cartouche ? 'cartouche' : 'carton',
                ];
            }

            // Calculer montant payé selon statut
            $montantPaye = match($config['paiement']) {
                'paye'    => $montantTotal,
                'partiel' => round($montantTotal * (rand(30, 70) / 100)),
                'impaye'  => 0,
            };
            $montantReste = $montantTotal - $montantPaye;

            $controleur = $config['livraison'] !== 'en_attente'
                ? collect($personnel)->filter(fn($u) => $u->role === 'controleur')->random()
                : null;

            $vente = Vente::create([
                'reference'          => $ref,
                'date_vente'         => $venteDate,
                'client_id'          => $client?->id,
                'user_id'            => $vendeur->id,
                'livreur_id'         => $controleur?->id,
                'montant_total'      => $montantTotal,
                'montant_paye'       => $montantPaye,
                'montant_reste'      => $montantReste,
                'montant_remis'      => $config['paiement'] === 'paye' ? $montantTotal + rand(0, 500) : $montantPaye,
                'statut_paiement'    => $config['paiement'],
                'statut_livraison'   => $config['livraison'],
                'date_livraison'     => $config['livraison'] === 'livre' ? $venteDate->copy()->addHours(rand(1, 48)) : null,
                'note_livraison'     => $config['livraison'] === 'probleme' ? 'Client absent lors de la livraison' : null,
                'tenant_id'          => $tenant->id,
                'magasin_id'         => $magasin->id,
            ]);

            // Créer les lignes de vente
            foreach ($lignesData as $ligne) {
                VenteLigne::create(array_merge($ligne, ['vente_id' => $vente->id]));

                // Stock mouvement de sortie
                StockMouvement::create([
                    'produit_id'      => $ligne['produit_id'],
                    'magasin_id'      => $magasin->id,
                    'tenant_id'       => $tenant->id,
                    'user_id'         => $vendeur->id,
                    'type'            => 'sortie_vente',
                    'quantite'        => $ligne['quantite'],
                    'reference_type'  => Vente::class,
                    'reference_id'    => $vente->id,
                    'note'            => 'Sortie vente MASS #' . $venteCounter,
                ]);
            }

            // Créer dette si impayé ou partiel
            if ($config['paiement'] !== 'paye' && $client) {
                $initialDebt = $montantTotal;
                $isPartiel = $config['paiement'] === 'partiel';
                $isOverdue = rand(0, 3) === 0; // 25% des dettes en retard
                $echeanceDate = $isOverdue
                    ? $venteDate->copy()->subDays(rand(5, 20))
                    : $venteDate->copy()->addDays(rand(15, 45));

                $versementActuel = $montantPaye; // Acompte payé à la vente
                $remaining = max(0, $initialDebt - $versementActuel);

                $statutFinal = match(true) {
                    $remaining <= 0 => 'solde',
                    $isOverdue     => 'en_retard',
                    $isPartiel     => 'partiel',
                    default        => 'en_cours',
                };

                $dette = Dette::create([
                    'vente_id'         => $vente->id,
                    'client_id'        => $client->id,
                    'montant_initial'  => $initialDebt,
                    'montant_paye'     => $versementActuel,
                    'montant_restant'  => $remaining,
                    'date_echeance'    => $echeanceDate,
                    'statut'           => $statutFinal,
                    'tenant_id'        => $tenant->id,
                ]);

                if ($versementActuel > 0) {
                    DettePaiement::create([
                        'dette_id'      => $dette->id,
                        'user_id'       => $vendeur->id,
                        'montant'       => $versementActuel,
                        'date_paiement' => $venteDate->copy(),
                        'notes'         => 'Acompte versé à la commande',
                    ]);
                }
            }
        }

        // ════════════════════════════════════════════
        // 6. TRANSFERTS — 50 transferts entre dépôts
        // ════════════════════════════════════════════
        $this->command->info('🔄 Création de 50 transferts...');

        for ($i = 1; $i <= 50; $i++) {
            $ref = 'TRF-MASS-' . str_pad($i, 3, '0', STR_PAD_LEFT);

            if (Transfert::where('reference', $ref)->exists()) {
                continue;
            }

            $transfertDate = Carbon::now()->subDays(rand(0, 90));
            $direction = rand(0, 1);
            $source = $direction ? $magasin1 : $magasin2;
            $destination = $direction ? $magasin2 : $magasin1;
            $livreur = collect($personnel)->random();

            // Transferts 1 à 20 sont en transit (modifiables), 21 à 50 sont reçus
            $statut = ($i <= 20) ? 'en_transit' : 'recu';

            $transfert = Transfert::create([
                'reference'              => $ref,
                'magasin_source_id'      => $source->id,
                'magasin_destination_id' => $destination->id,
                'livreur_id'             => $livreur->id,
                'user_id'                => $admin?->id,
                'statut'                 => $statut,
                'date_transfert'         => $transfertDate,
                'date_livraison'         => $statut === 'recu' ? $transfertDate->copy()->addHours(rand(2, 48)) : null,
                'tenant_id'              => $tenant->id,
                'notes'                  => 'Transfert massif #' . $i,
            ]);

            // Ajouter 2-5 produits par transfert
            $transfertProduits = $produits->random(rand(2, min(5, $produits->count())));
            foreach ($transfertProduits as $prod) {
                $qte = rand(5, 20);

                TransfertProduit::create([
                    'transfert_id'    => $transfert->id,
                    'produit_id'      => $prod->id,
                    'quantite'        => $qte,
                    'quantite_recue'  => $statut === 'recu' ? $qte : null,
                ]);

                // Stock mouvements pour transfert
                StockMouvement::create([
                    'produit_id'      => $prod->id,
                    'magasin_id'      => $source->id,
                    'tenant_id'       => $tenant->id,
                    'user_id'         => $admin?->id,
                    'type'            => 'transfert_sortie',
                    'quantite'        => $qte,
                    'reference_type'  => Transfert::class,
                    'reference_id'    => $transfert->id,
                    'note'            => 'Sortie transfert MASS #' . $i,
                ]);

                if ($statut === 'recu') {
                    StockMouvement::create([
                        'produit_id'      => $prod->id,
                        'magasin_id'      => $destination->id,
                        'tenant_id'       => $tenant->id,
                        'user_id'         => $admin?->id,
                        'type'            => 'transfert_entree',
                        'quantite'        => $qte,
                        'reference_type'  => Transfert::class,
                        'reference_id'    => $transfert->id,
                        'note'            => 'Entrée transfert MASS #' . $i,
                    ]);
                }
            }
        }

        // ════════════════════════════════════════════
        // 7. ÉQUILIBRAGE ET SYNCHRONISATION PRÉCISE DES STOCKS (0 STOCKS NÉGATIFS, 20 ALERTES CONTRÔLÉES)
        // ════════════════════════════════════════════
        $this->command->info('⚙️ Équilibrage des stocks : 0 stock négatif, 0 rupture fortuite, 20 alertes contrôlées...');

        // Sélectionner 20 produits au hasard qui seront les seuls en alerte de stock
        $alertProductIds = $produits->random(min(20, $produits->count()))->pluck('id')->toArray();

        foreach ($produits as $prod) {
            $cpc = $prod->cartouchesParCartonEffectif();

            // Total des cartouches sorties (ventes, transferts sortants)
            $sortiesCartouches = (int) StockMouvement::where('produit_id', $prod->id)
                ->whereIn('type', ['sortie_vente', 'transfert_sortie', 'ajustement_negatif'])
                ->selectRaw("SUM(quantite * ? + quantite_cartouche) as total", [$cpc])
                ->value('total') ?? 0;

            // Total des cartouches entrées (arrivages, transferts entrants)
            $entreesCartouches = (int) StockMouvement::where('produit_id', $prod->id)
                ->whereIn('type', ['entree_arrivage', 'transfert_entree'])
                ->selectRaw("SUM(quantite * ? + quantite_cartouche) as total", [$cpc])
                ->value('total') ?? 0;

            $isAlertProduct = in_array($prod->id, $alertProductIds);

            if ($isAlertProduct) {
                // Produit en alerte contrôlée : seuil entre 15 et 50 cartons
                $seuilCartons = rand(15, 50);
                // Stock ciblé positif et légèrement sous le seuil (ex: 3 à seuil-2 cartons)
                $targetStockCartons = max(3, $seuilCartons - rand(2, 6));
            } else {
                // Produit sain : seuil entre 5 et 20 cartons
                $seuilCartons = rand(5, 20);
                // Stock ciblé très confortable (80 à 300 cartons)
                $targetStockCartons = $seuilCartons + rand(80, 300);
            }

            $targetStockCartouches = $targetStockCartons * $cpc;
            $netCartouchesActuel = $entreesCartouches - $sortiesCartouches;
            $ajustementNec = $targetStockCartouches - $netCartouchesActuel;

            if ($ajustementNec > 0) {
                $qteCartons = intdiv($ajustementNec, $cpc);
                $qteCartouchesRes = $ajustementNec % $cpc;

                StockMouvement::create([
                    'tenant_id'          => $tenant->id,
                    'magasin_id'         => $magasin1->id,
                    'produit_id'         => $prod->id,
                    'user_id'            => $admin?->id,
                    'type'               => 'ajustement_positif',
                    'quantite'           => $qteCartons,
                    'quantite_cartouche' => $qteCartouchesRes,
                    'note'               => 'Stock initial équilibré MassSeeder',
                ]);
            }

            $prod->update([
                'seuil_alerte' => $seuilCartons,
            ]);
            $prod->syncStock();
        }

        // ════════════════════════════════════════════
        // 8. DÉPENSES JOURNALIÈRES (Aujourd'hui et du mois)
        // ════════════════════════════════════════════
        $this->command->info('💸 Création des dépenses journalières...');

        $depensesDesc = [
            'Frais de transport marchandises Dépôt 1',
            'Achat carburant pour livraison tricycle',
            'Facture électricité SBEE Dépôt Saint Michel',
            'Restauration équipe manutentionnaires',
            'Achat fournitures de bureau & facturettes',
            'Réparation pneu camionnette de livraison',
            'Entretien & vidange tricycle de livraison',
            'Achat emballages et ruban adhésif',
            'Frais de déchargement conteneur',
            'Paiement gardiennage sécurité nuit',
        ];

        // Dépenses d'AUJOURD'HUI
        for ($d = 1; $d <= 6; $d++) {
            \App\Models\DepenseJournaliere::create([
                'tenant_id'    => $tenant->id,
                'user_id'      => $admin?->id,
                'montant'      => rand(5000, 45000),
                'description'  => $depensesDesc[array_rand($depensesDesc)],
                'date_depense' => Carbon::today(),
            ]);
        }

        // Dépenses des 60 derniers jours
        for ($d = 1; $d <= 20; $d++) {
            \App\Models\DepenseJournaliere::create([
                'tenant_id'    => $tenant->id,
                'user_id'      => $admin?->id,
                'montant'      => rand(3000, 75000),
                'description'  => $depensesDesc[array_rand($depensesDesc)],
                'date_depense' => Carbon::today()->subDays(rand(1, 60)),
            ]);
        }

        $this->command->info('✅ MassSeeder terminé !');
        $this->command->info('   → 10 employés');
        $this->command->info('   → 10 clients avec vrais noms');
        $this->command->info('   → 11 arrivages réceptionnés + 2 en attente');
        $this->command->info('   → 100 ventes (dont 25 aujourd\'hui)');
        $this->command->info('   → 50 transferts (49 reçus, 1 en transit)');
        $this->command->info('   → 15 produits sous le seuil d\'alerte de stock');
        $this->command->info('   → 26 dépenses journalières (dont 6 aujourd\'hui)');
    }
}
