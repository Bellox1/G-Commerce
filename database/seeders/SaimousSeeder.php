<?php

namespace Database\Seeders;

use App\Models\Arrivage;
use App\Models\ArrivageProduit;
use App\Models\Client;
use App\Models\Dette;
use App\Models\DetteSociete;
use App\Models\DepenseJournaliere;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Tenant;
use App\Models\Transfert;
use App\Models\TransfertProduit;
use App\Models\User;
use App\Models\Vente;
use App\Models\VenteLigne;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SaimousSeeder extends Seeder
{
    private int $venteRef = 0;

    public function run(): void
    {
        // ─── 1. Société ─────────────────────────────────────────────────
        $tenant = Tenant::create([
            'nom'             => 'SAÏMOUS',
            'marque'          => 'RICCI',
            'activite'        => 'Importation et vente de produits alimentaires et ménagers',
            'pays'            => 'BJ',
            'ville'           => 'Cotonou',
            'offre_code'      => 'locale',
            'offre_expires_at'=> null,
        ]);

        // ─── 2. Magasins (3) ────────────────────────────────────────────
        $magBoutique = Magasin::create([
            'tenant_id'=>$tenant->id, 'nom'=>'Boutique Saint Michel',
            'adresse'=>'Quartier Saint Michel', 'ville'=>'Cotonou', 'loyer'=>250000,
        ]);
        $magDepot = Magasin::create([
            'tenant_id'=>$tenant->id, 'nom'=>'Dépôt Central',
            'adresse'=>'Zone Industrielle', 'ville'=>'Cotonou', 'loyer'=>350000,
        ]);
        $magEntrepot = Magasin::create([
            'tenant_id'=>$tenant->id, 'nom'=>'Entrepôt Djogbanou',
            'adresse'=>'Djogbanou', 'ville'=>'Cotonou', 'loyer'=>200000,
        ]);

        // ─── 3. Utilisateurs ────────────────────────────────────────────
        User::create([
            'tenant_id'=>null,'magasin_id'=>null,'name'=>'Super Admin Plateforme',
            'email'=>'mantinoubello123@gmail.com','telephone'=>'+22997000000',
            'role'=>'super_admin','password'=>Hash::make('saimous2026'),
        ]);
        $admin = User::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$magBoutique->id,
            'name'=>'Matinou BELLO (Admin)','email'=>'chezbkr@gmail.com',
            'telephone'=>'+22997000001','role'=>'admin','password'=>Hash::make('saimous2026'),
        ]);
        $vendeur = User::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$magBoutique->id,
            'name'=>'Vendeur Principal','email'=>'vendeur@saimous.bj',
            'telephone'=>'+22997000003','role'=>'vendeur','password'=>Hash::make('saimous2026'),
        ]);
        $magasinier = User::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$magDepot->id,
            'name'=>'Magasinier Dépôt','email'=>'magasinier@saimous.bj',
            'telephone'=>'+22997000004','role'=>'magasinier',
            'roles_secondaires'=>['livreur'],'password'=>Hash::make('saimous2026'),
        ]);
        $livreur = User::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$magBoutique->id,
            'name'=>'Livreur RICCI','email'=>'livreur@saimous.bj',
            'telephone'=>'+22997000005','role'=>'livreur','password'=>Hash::make('saimous2026'),
        ]);

        // ─── 4. Fournisseurs ────────────────────────────────────────────
        $fourLagos = Fournisseur::create([
            'tenant_id'=>$tenant->id,'nom'=>'Lagos Trade Center',
            'pays'=>'NG','ville'=>'Lagos','telephone'=>'+2348012345678','devise'=>'NGN',
        ]);
        $fourAba = Fournisseur::create([
            'tenant_id'=>$tenant->id,'nom'=>'Aba Market Goods',
            'pays'=>'NG','ville'=>'Aba','devise'=>'NGN',
        ]);

        // ─── 5. Produits (10) — prix 5 500 à 25 000 ─────────────────────
        // Images produits (URLs Unsplash/Pexels publiques et stables)
        $produits = [];
        foreach ([
            ['Ciment 50kg (Tomato)',   6500, 20, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80'],
            ['Huile Végétale 5L',     15000, 10, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&q=80'],
            ['Riz Parboilé 25kg',     22000,  5, 'https://images.unsplash.com/photo-1536304993881-ff86e0c9b0e3?w=400&q=80'],
            ['Farine de Blé 50kg',    18000,  8, 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=400&q=80'],
            ['Sucre Cristal 25kg',    16000,  6, 'https://images.unsplash.com/photo-1559181567-c3190ca9be7c?w=400&q=80'],
            ['Lait en Poudre 400g',    5500, 15, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&q=80'],
            ['Huile de Palme 5L',     12000, 10, 'https://images.unsplash.com/photo-1600348759200-5b94753c7073?w=400&q=80'],
            ['Boisson Energie (crt)', 25000, 10, 'https://images.unsplash.com/photo-1622543925917-763c34d1a86e?w=400&q=80'],
            ['Lessive OMO 5L',         9500, 12, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&q=80'],
            ['Ciment 50kg (Dangote)',  7000, 20, 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&q=80'],
        ] as [$nom, $prix, $seuil, $image]) {
            $produits[] = Produit::create([
                'tenant_id'=>$tenant->id, 'nom'=>$nom,
                'prix_vente_conseille'=>$prix, 'seuil_alerte'=>$seuil, 'stock'=>0,
                'image'=>$image,
            ]);
        }

        // ─── 6. Clients (10) ────────────────────────────────────────────
        $clients = [];
        foreach ([
            ['Adjoua Marie',   '+22996111001'],
            ['Koffi Jean',     '+22996111002'],
            ['Assogba Pascal', '+22996111003'],
            ['Hounsou Brice',  '+22996111004'],
            ['Aïcha Bello',    '+22996111005'],
            ['Emmanuel Codjo', '+22996111006'],
            ['Fatima Issaka',  '+22996111007'],
            ['Gérard Agossa',  '+22996111008'],
            ['Henri Dossou',   '+22996111009'],
            ['Isabelle Tossou','+22996111010'],
        ] as [$nom, $tel]) {
            $clients[] = Client::create([
                'tenant_id'=>$tenant->id, 'nom'=>$nom, 'telephone'=>$tel,
                'limite_credit' => in_array($nom, ['Hounsou Brice','Fatima Issaka']) ? 200000 : 0,
            ]);
        }

        // ─── 7. Arrivages (6, TOUS réceptionnés) ────────────────────────
        // Quantités gonflées pour éviter tout stock négatif
        //          produits                         transport  douane  manut  com   divers
        $arr1 = $this->creerArrivage($tenant,$magDepot,$fourLagos,$admin,'ARR-2026-001',
            now()->subDays(25),'receptionne', 50000,25000,15000,8000,3000, [
            [$produits[0], 300, 3500],  // Ciment Tomato 300 sacs (suffisant pour ventes+transferts)
            [$produits[1], 200, 8500],  // Huile Végétale 200 units
        ]);
        $arr2 = $this->creerArrivage($tenant,$magBoutique,$fourLagos,$admin,'ARR-2026-002',
            now()->subDays(20),'receptionne', 40000,20000,12000,6000,2000, [
            [$produits[2], 150,12000],  // Riz 150 sacs
            [$produits[3], 150,10000],  // Farine 150 sacs
        ]);
        $arr3 = $this->creerArrivage($tenant,$magDepot,$fourAba,$vendeur,'ARR-2026-003',
            now()->subDays(15),'receptionne', 35000,18000,10000,5000,2000, [
            [$produits[4], 250, 6000],  // Sucre 250 sacs
            [$produits[5], 300, 2800],  // Lait 300 boîtes
        ]);
        $arr4 = $this->creerArrivage($tenant,$magBoutique,$fourLagos,$admin,'ARR-2026-004',
            now()->subDays(10),'receptionne', 45000,22000,13000,7000,3000, [
            [$produits[6], 200, 6500],  // Huile Palme 200 units
            [$produits[7], 200,14000],  // Boisson Énergie 200 cartons
        ]);
        $arr5 = $this->creerArrivage($tenant,$magBoutique,$fourAba,$vendeur,'ARR-2026-005',
            now()->subDays(8),'receptionne', 30000,15000,10000,5000,2000, [
            [$produits[8], 200, 4500],  // Lessive 200 units
            [$produits[9], 200, 5000],  // Ciment Dangote 200 sacs
        ]);
        $arr6 = $this->creerArrivage($tenant,$magDepot,$fourLagos,$admin,'ARR-2026-006',
            now()->subDays(2),'receptionne', 25000,12000,8000,4000,1000, [
            [$produits[1], 100, 8500],  // Huile Végétale 100 units
            [$produits[4], 100, 6000],  // Sucre 100 sacs
        ]);

        // 2 arrivages en cours (pas encore réceptionnés → pas de mouvements stock)
        $arr7 = $this->creerArrivage($tenant,$magBoutique,$fourAba,$vendeur,'ARR-2026-007',
            now()->subDay(),'en_cours', 20000,10000,7000,3000,1000, [
            [$produits[0],  80, 3500],  // Ciment Tomato 80 sacs attendus
            [$produits[6],  60, 6500],  // Huile Palme 60 units attendus
        ]);
        $arr8 = $this->creerArrivage($tenant,$magDepot,$fourLagos,$admin,'ARR-2026-008',
            now(),'en_cours', 30000,15000,9000,4000,2000, [
            [$produits[2],  50,12000],  // Riz 50 sacs attendus
            [$produits[7],  60,14000],  // Boisson Énergie 60 cartons attendus
        ]);

        $this->mettreAJourStocks($produits);


        // ─── 8. Transferts (3) ──────────────────────────────────────────
        $this->creerTransfert($tenant,$magDepot,$magBoutique,$admin,$livreur,
            'TRF-2026-001', now()->subDays(12),'livre',now()->subDays(11), [
            [$produits[0],30],  // Ciment Tomato 30
            [$produits[1],25],  // Huile Végétale 25
        ], 'Réappro boutique.');

        $this->creerTransfert($tenant,$magBoutique,$magDepot,$vendeur,$magasinier,
            'TRF-2026-002', now()->subDays(5),'en_transit',null, [
            [$produits[2],15],  // Riz 15
            [$produits[3],10],  // Farine 10
        ], 'Réappro dépôt en cours.');

        $this->creerTransfert($tenant,$magDepot,$magEntrepot,$admin,$livreur,
            'TRF-2026-003', now()->subDays(1),'en_attente',null, [
            [$produits[4],20],  // Sucre 20
            [$produits[9],20],  // Ciment Dangote 20
        ], 'Vers entrepôt. En attente.');

        $this->creerTransfert($tenant,$magDepot,$magBoutique,$admin,$livreur,
            'TRF-2026-004', now()->subDays(2),'livre',now()->subDays(2), [
            [$produits[4],25],  // Sucre 25
            [$produits[5],25],  // Lait 25
        ], 'Réappro boutique sucre & lait.');

        // ─── 9. Ventes (20) ─────────────────────────────────────────────
        // SEMAINE DERNIÈRE (10 ventes payées)
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[0],now()->subDays(18),
            'livre',$livreur,now()->subDays(17), [
            [$produits[0],10,6500],  // 10 Ciment Tomato = 65 000
            [$produits[1], 5,15000], // 5 Huile Végétale = 75 000
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[1],now()->subDays(17),
            'livre',$livreur,now()->subDays(16), [
            [$produits[2], 4,22000], // 4 Riz = 88 000
        ]);
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[2],now()->subDays(16),
            'livre',$livreur,now()->subDays(15), [
            [$produits[3], 5,18000], // 5 Farine = 90 000
            [$produits[5],20, 5500], // 20 Lait = 110 000
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[3],now()->subDays(15),
            'en_attente',null,null, [
            [$produits[7], 8,25000], // 8 Boisson Énergie = 200 000
        ]);
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[4],now()->subDays(14),
            'livre',$livreur,now()->subDays(13), [
            [$produits[6],10,12000], // 10 Huile Palme = 120 000
            [$produits[8],12, 9500], // 12 Lessive = 114 000
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[5],now()->subDays(13),
            'probleme',$livreur,now()->subDays(12), [
            [$produits[9],15, 7000], // 15 Ciment Dangote = 105 000
        ], 'Client absent. 1ère tentative échouée.');
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[6],now()->subDays(12),
            'livre',$livreur,now()->subDays(11), [
            [$produits[4], 8,16000], // 8 Sucre = 128 000
            [$produits[0],10, 6500], // 10 Ciment Tomato = 65 000
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[7],now()->subDays(11),
            'en_attente',null,null, [
            [$produits[1],12,15000], // 12 Huile Végétale = 180 000
        ]);
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[8],now()->subDays(10),
            'livre',$livreur,now()->subDays(9), [
            [$produits[2], 6,22000], // 6 Riz = 132 000
            [$produits[3], 4,18000], // 4 Farine = 72 000
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[9],now()->subDays(9),
            'probleme',$livreur,now()->subDays(8), [
            [$produits[7], 5,25000], // 5 Boisson Énergie = 125 000
        ], 'Adresse incorrecte. Contacté pour correction.');

        // HIER (5 ventes payées)
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[0],now()->subDay(),
            'livre',$livreur,now()->subDay(), [
            [$produits[5],25, 5500], // 25 Lait = 137 500
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[1],now()->subDay(),
            'en_attente',null,null, [
            [$produits[8], 8, 9500], // 8 Lessive = 76 000
            [$produits[4], 6,16000], // 6 Sucre = 96 000
        ]);
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[2],now()->subDay(),
            'livre',$livreur,now()->subDay(), [
            [$produits[0],20, 6500], // 20 Ciment Tomato = 130 000
        ]);
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[3],now()->subDay(),
            'en_attente',null,null, [
            [$produits[6],15,12000], // 15 Huile Palme = 180 000
            [$produits[1], 8,15000], // 8 Huile Végétale = 120 000
        ]);
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[4],now()->subDay(),
            'livre',$livreur,now()->subDay(), [
            [$produits[2], 5,22000], // 5 Riz = 110 000
            [$produits[5],30, 5500], // 30 Lait = 165 000
        ]);

        // AUJOURD'HUI — 2 ventes payées
        $this->creerVente($tenant,$magBoutique,$vendeur,$clients[6],now(),
            'livre',$livreur,now(), [
            [$produits[0], 5, 6500], // 5 Ciment Tomato = 32 500
            [$produits[8],10, 9500], // 10 Lessive = 95 000
        ]);
        $this->creerVente($tenant,$magDepot,$vendeur,$clients[7],now(),
            'en_attente',null,null, [
            [$produits[3], 8,18000], // 8 Farine = 144 000
            [$produits[9],10, 7000], // 10 Ciment Dangote = 70 000
        ]);

        // AUJOURD'HUI — 5 ventes avec dettes (5 clients différents)
        // VNT-016 : Aïcha Bello — partiel (payé 100 000 / 320 000)
        $this->creerVenteAvecDette($tenant,$magBoutique,$vendeur,$clients[4],now(),
            320000,100000,'en_attente',null,null, [
            [$produits[7],10,25000], // 10 Boisson Énergie = 250 000
            [$produits[9],10, 7000], // 10 Ciment Dangote = 70 000
        ], 'Acompte 100 000 reçu. Solde 220 000 attendu vendredi.');

        // VNT-017 : Emmanuel Codjo — partiel (payé 80 000 / 304 000)
        $this->creerVenteAvecDette($tenant,$magDepot,$vendeur,$clients[5],now(),
            304000,80000,'livre',$livreur,now(), [
            [$produits[3], 8,18000], // 8 Farine = 144 000
            [$produits[4],10,16000], // 10 Sucre = 160 000
        ], 'Paiement partiel 80 000. Rendez-vous lundi pour le reste.');

        // VNT-018 : Fatima Issaka — impayé (0 / 239 000)
        $this->creerVenteAvecDette($tenant,$magBoutique,$vendeur,$clients[6],now(),
            239000,0,'livre',$livreur,now(), [
            [$produits[6],12,12000], // 12 Huile Palme = 144 000
            [$produits[8],10, 9500], // 10 Lessive = 95 000
        ], 'Promet de payer la semaine prochaine. Bonne cliente.');

        // VNT-019 : Gérard Agossa — impayé (0 / 187 500)
        $this->creerVenteAvecDette($tenant,$magDepot,$vendeur,$clients[7],now(),
            187500,0,'en_attente',null,null, [
            [$produits[0],15, 6500], // 15 Ciment Tomato = 97 500
            [$produits[1], 6,15000], // 6 Huile Végétale = 90 000
        ], 'Client nouveau. 1ère commande à crédit.');

        // VNT-020 : Henri Dossou — impayé (0 / 302 000)
        $this->creerVenteAvecDette($tenant,$magBoutique,$vendeur,$clients[8],now(),
            302000,0,'probleme',$livreur,now(), [
            [$produits[5],20, 5500], // 20 Lait = 110 000
            [$produits[4],12,16000], // 12 Sucre = 192 000
        ], 'Client injoignable. Tenter re-livraison demain.');

        // ─── 10. Dépenses ──────────────────────────────────────────────
        foreach ([
            [25000,'Carburant moto livraison',     now()->subDays(18)],
            [15000,'Eau et électricité boutique',   now()->subDays(14)],
            [45000,'Entretien climatisation dépôt', now()->subDays(10)],
            [ 8000,'Papeterie et fournures bureau', now()->subDays(8)],
            [35000,'Charges personnel semaine',     now()->subDays(5)],
            [ 5000,'Recharge crédit téléphone',     now()->subDays(3)],
            [20000,'Nettoyage magasin + dépôt',     now()->subDay()],
            [12000,'Frais bancaires transfert',     now()],
        ] as [$montant,$desc,$date]) {
            DepenseJournaliere::create([
                'tenant_id'=>$tenant->id,'user_id'=>$admin->id,
                'montant'=>$montant,'description'=>$desc,'date_depense'=>$date->format('Y-m-d'),
            ]);
        }

        // ─── 11. Dette société ──────────────────────────────────────────
        DetteSociete::create([
            'tenant_id'=>$tenant->id,'fournisseur_id'=>$fourLagos->id,
            'arrivage_id'=>$arr4->id,'montant'=>$arr4->total_cout_reel,
            'montant_paye'=>0,'description'=>"Arrivage {$arr4->reference}",
            'date_dette'=>$arr4->date_arrivage,'statut'=>'en_cours',
        ]);

        $this->command->info('✅ Seeder OK — 10 produits (6 500–25 000 F), 3 dépôts, 10 clients');
        $this->command->info('   6 arrivages réceptionnés, 20 ventes (5 dettes aujourd\'hui), 3 transferts, 8 dépenses');
        $this->command->info('   Admin: chezbkr@gmail.com / saimous2026');
    }

    // ─── ARRIVAGE ───────────────────────────────────────────────────────
    private function creerArrivage(
        Tenant $tenant, Magasin $magasin, Fournisseur $four, User $user,
        string $ref, $date, string $statut,
        float $transport, float $douane, float $manut, float $com, float $divers,
        array $lignes
    ): Arrivage {
        $arrivage = Arrivage::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$magasin->id,
            'fournisseur_id'=>$four->id,'user_id'=>$user->id,
            'reference'=>$ref,'date_arrivage'=>$date,
            'pays_origine'=>'NG','taux_change'=>1.5,
            'devise_origine'=>'NGN','devise_locale'=>'XOF',
            'frais_transport'=>$transport,'frais_douane'=>$douane,
            'frais_manutention'=>$manut,'frais_commission'=>$com,'frais_divers'=>$divers,
            'statut'=>$statut,
        ]);

        $nb = count($lignes);
        $totalFrais = $transport+$douane+$manut+$com+$divers;
        foreach ($lignes as [$p,$qty,$prixOrig]) {
            $totOrig  = $qty*$prixOrig;
            $valFcfa  = $totOrig*1.5;
            $partF    = $totalFrais/$nb;
            $coutTot  = $valFcfa+$partF;
            $coutUnit = $qty>0?$coutTot/$qty:0;
            ArrivageProduit::create([
                'arrivage_id'=>$arrivage->id,'produit_id'=>$p->id,'fournisseur_id'=>$four->id,
                'quantite'=>$qty,'prix_unitaire_origine'=>$prixOrig,
                'total_origine'=>$totOrig,'valeur_fcfa'=>$valFcfa,
                'part_frais'=>$partF,'cout_unitaire_reel'=>$coutUnit,
                'cout_total_reel'=>$coutTot,'prix_vente_suggere'=>Produit::arrondir($coutUnit),
            ]);
        }

        $arrivage->load('produits');
        $arrivage->recalculer();

        if ($statut==='receptionne') {
            foreach ($arrivage->produits as $ligne) {
                StockMouvement::create([
                    'tenant_id'=>$tenant->id,'magasin_id'=>$magasin->id,
                    'produit_id'=>$ligne->produit_id,'user_id'=>$user->id,
                    'type'=>'entree_arrivage','quantite'=>$ligne->quantite,
                    'cout_unitaire'=>$ligne->cout_unitaire_reel,
                    'reference_type'=>Arrivage::class,'reference_id'=>$arrivage->id,
                    'note'=>"Arrivage {$ref}",'date_mouvement'=>$date,
                ]);
            }
        }

        return $arrivage;
    }

    // ─── STOCK ──────────────────────────────────────────────────────────
    private function mettreAJourStocks(array $produits): void
    {
        foreach ($produits as $p) {
            $total = StockMouvement::where('tenant_id',$p->tenant_id)
                ->where('produit_id',$p->id)
                ->selectRaw("SUM(CASE WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite ELSE 0 END) as t")
                ->value('t') ?? 0;
            $p->update(['stock'=>max(0,$total)]);
        }
    }

    // ─── VENTE PAYÉE ────────────────────────────────────────────────────
    private function creerVente(
        Tenant $tenant, Magasin $mag, User $vendeur, Client $client,
        $date, string $livraison, ?User $livreur, $dateLiv, array $lignes, ?string $note=null
    ): Vente {
        $this->venteRef++;
        $ref = sprintf('VNT-2026-%03d',$this->venteRef);
        $total = 0;
        foreach ($lignes as [,$qty,$prix]) $total += $qty*$prix;

        $vente = Vente::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$mag->id,'user_id'=>$vendeur->id,
            'client_id'=>$client->id,'reference'=>$ref,'date_vente'=>$date,
            'montant_total'=>$total,'montant_paye'=>$total,'montant_reste'=>0,
            'statut_paiement'=>'paye','statut_livraison'=>$livraison,
            'livreur_id'=>$livreur?->id,'date_livraison'=>$dateLiv,
            'note_livraison'=>$note,'mode_paiement'=>'especes',
        ]);

        foreach ($lignes as [$p,$qty,$prix]) {
            $cout = round($prix*0.55);
            VenteLigne::create([
                'vente_id'=>$vente->id,'produit_id'=>$p->id,'quantite'=>$qty,
                'prix_conseille'=>$p->prix_vente_conseille,'prix_vente'=>$prix,
                'cout_unitaire'=>$cout,'total_ligne'=>$qty*$prix,'unite'=>'pcs',
            ]);
            StockMouvement::create([
                'tenant_id'=>$tenant->id,'magasin_id'=>$mag->id,'produit_id'=>$p->id,
                'user_id'=>$vendeur->id,'type'=>'sortie_vente','quantite'=>$qty,
                'cout_unitaire'=>$cout,'reference_type'=>Vente::class,'reference_id'=>$vente->id,
                'note'=>"Vente {$ref}",'date_mouvement'=>$date,
            ]);
            $p->stock = max(0,$p->stock-$qty);
            $p->save();
        }
        return $vente;
    }

    // ─── VENTE AVEC DETTE ──────────────────────────────────────────────
    private function creerVenteAvecDette(
        Tenant $tenant, Magasin $mag, User $vendeur, Client $client,
        $date, float $total, float $paye, string $livraison,
        ?User $livreur, $dateLiv, array $lignes, ?string $note=null
    ): Vente {
        $this->venteRef++;
        $ref = sprintf('VNT-2026-%03d',$this->venteRef);
        $reste = $total-$paye;

        $vente = Vente::create([
            'tenant_id'=>$tenant->id,'magasin_id'=>$mag->id,'user_id'=>$vendeur->id,
            'client_id'=>$client->id,'reference'=>$ref,'date_vente'=>$date,
            'montant_total'=>$total,'montant_paye'=>$paye,'montant_reste'=>$reste,
            'statut_paiement'=>$paye<=0?'impaye':'partiel',
            'statut_livraison'=>$livraison,'livreur_id'=>$livreur?->id,
            'date_livraison'=>$dateLiv,'note_livraison'=>$note,
            'mode_paiement'=>$paye>0?'especes':'credit',
        ]);

        foreach ($lignes as [$p,$qty,$prix]) {
            $cout = round($prix*0.55);
            VenteLigne::create([
                'vente_id'=>$vente->id,'produit_id'=>$p->id,'quantite'=>$qty,
                'prix_conseille'=>$p->prix_vente_conseille,'prix_vente'=>$prix,
                'cout_unitaire'=>$cout,'total_ligne'=>$qty*$prix,'unite'=>'pcs',
            ]);
            StockMouvement::create([
                'tenant_id'=>$tenant->id,'magasin_id'=>$mag->id,'produit_id'=>$p->id,
                'user_id'=>$vendeur->id,'type'=>'sortie_vente','quantite'=>$qty,
                'cout_unitaire'=>$cout,'reference_type'=>Vente::class,'reference_id'=>$vente->id,
                'note'=>"Vente {$ref}",'date_mouvement'=>$date,
            ]);
            $p->stock = max(0,$p->stock-$qty);
            $p->save();
        }

        Dette::create([
            'tenant_id'=>$tenant->id,'client_id'=>$client->id,'vente_id'=>$vente->id,
            'montant_initial'=>$reste,'montant_paye'=>0,'montant_restant'=>$reste,
            'date_echeance'=>\Carbon\Carbon::parse($date)->addDays(30),
            'statut'=>$paye<=0?'impaye':'partiel','notes'=>$note,
        ]);

        return $vente;
    }

    // ─── TRANSFERT ─────────────────────────────────────────────────────
    private function creerTransfert(
        Tenant $tenant, Magasin $src, Magasin $dst, User $init, User $liv,
        string $ref, $date, string $statut, $dateLiv, array $lignes, ?string $notes=null
    ): void {
        $trf = Transfert::create([
            'tenant_id'=>$tenant->id,'magasin_source_id'=>$src->id,
            'magasin_destination_id'=>$dst->id,'user_id'=>$init->id,
            'livreur_id'=>$liv->id,'reference'=>$ref,'statut'=>$statut,
            'date_transfert'=>$date,'date_livraison'=>$dateLiv,'notes'=>$notes,
        ]);

        foreach ($lignes as [$p,$q]) {
            TransfertProduit::create(['transfert_id'=>$trf->id,'produit_id'=>$p->id,'quantite'=>$q]);

            if (in_array($statut,['livre','en_transit'])) {
                StockMouvement::create([
                    'tenant_id'=>$tenant->id,'magasin_id'=>$src->id,'produit_id'=>$p->id,
                    'user_id'=>$init->id,'type'=>'transfert_sortie','quantite'=>$q,
                    'reference_type'=>Transfert::class,'reference_id'=>$trf->id,
                    'note'=>"{$ref} → {$dst->nom}",'date_mouvement'=>$date,
                ]);
                $p->stock = max(0,$p->stock-$q);
                $p->save();
            }

            if ($statut==='livre') {
                StockMouvement::create([
                    'tenant_id'=>$tenant->id,'magasin_id'=>$dst->id,'produit_id'=>$p->id,
                    'user_id'=>$liv->id,'type'=>'transfert_entree','quantite'=>$q,
                    'reference_type'=>Transfert::class,'reference_id'=>$trf->id,
                    'note'=>"{$ref} ← {$src->nom}",'date_mouvement'=>$dateLiv??$date,
                ]);
            }
        }
    }
}
