<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SaimousSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. Société SAÏMOUS ─────────────────────────────────────────
        $tenant = Tenant::create([
            'nom'      => 'SAÏMOUS',
            'marque'   => 'RICCI',
            'activite' => 'Importation et vente de produits alimentaires et ménagers',
            'pays'     => 'BJ',
            'ville'    => 'Cotonou',
        ]);

        // ─── 2. Magasins ────────────────────────────────────────────────
        $magasinPrincipal = Magasin::create([
            'tenant_id' => $tenant->id,
            'nom'       => 'Boutique Saint Michel',
            'adresse'   => 'Quartier Saint Michel',
            'ville'     => 'Cotonou',
        ]);
        $magasinDepot = Magasin::create([
            'tenant_id' => $tenant->id,
            'nom'       => 'Dépôt Central',
            'adresse'   => 'Zone Industrielle',
            'ville'     => 'Cotonou',
        ]);

        // ─── 3. Utilisateurs ────────────────────────────────────────────
        // ✅ Super Admin GLOBAL de la plateforme (sans société, sans magasin)
        User::create([
            'tenant_id'  => null,
            'magasin_id' => null,
            'name'       => 'Super Admin Plateforme',
            'email'      => 'mantinoubello123@gmail.com',
            'telephone'  => '+22997000000',
            'role'       => 'super_admin',
            'password'   => Hash::make('saimous2026'),
        ]);

        // Administrateur de la société SAÏMOUS
        $adminUser = User::create([
            'tenant_id'  => $tenant->id,
            'magasin_id' => $magasinPrincipal->id,
            'name'       => 'Matinou BELLO (Admin)',
            'email'      => 'chezbkr@gmail.com',
            'telephone'  => '+22997000001',
            'role'       => 'admin',
            'password'   => Hash::make('saimous2026'),
        ]);

        // Vendeur
        User::create([
            'tenant_id'  => $tenant->id,
            'magasin_id' => $magasinPrincipal->id,
            'name'       => 'Vendeur Principal',
            'email'      => 'vendeur@saimous.bj',
            'telephone'  => '+22997000003',
            'role'       => 'vendeur',
            'password'   => Hash::make('saimous2026'),
        ]);

        // Magasinier (avec rôle secondaire livreur)
        User::create([
            'tenant_id'        => $tenant->id,
            'magasin_id'       => $magasinDepot->id,
            'name'             => 'Magasinier Dépôt',
            'email'            => 'magasinier@saimous.bj',
            'telephone'        => '+22997000004',
            'role'             => 'magasinier',
            'roles_secondaires'=> ['livreur'],
            'password'         => Hash::make('saimous2026'),
        ]);

        // Livreur dédié
        User::create([
            'tenant_id'  => $tenant->id,
            'magasin_id' => $magasinPrincipal->id,
            'name'       => 'Livreur RICCI',
            'email'      => 'livreur@saimous.bj',
            'telephone'  => '+22997000005',
            'role'       => 'livreur',
            'password'   => Hash::make('saimous2026'),
        ]);

        // ─── 4. Fournisseurs ────────────────────────────────────────────
        $fourNigeria = Fournisseur::create([
            'tenant_id'  => $tenant->id,
            'nom'        => 'Lagos Trade Center',
            'pays'       => 'NG',
            'ville'      => 'Lagos',
            'telephone'  => '+2348012345678',
            'devise'     => 'NGN',
        ]);
        Fournisseur::create([
            'tenant_id'  => $tenant->id,
            'nom'        => 'Aba Market Goods',
            'pays'       => 'NG',
            'ville'      => 'Aba',
            'devise'     => 'NGN',
        ]);

        // ─── 5. Produits RICCI ──────────────────────────────────────────
        $produitsData = [
            ['nom' => 'Savon GLIC',        'prix_vente_conseille' => 500,   'stock' => 50,  'a_cartouche' => false, 'seuil_alerte' => 10],
            ['nom' => 'Huile Coco',        'prix_vente_conseille' => 1200,  'stock' => 30,  'a_cartouche' => false, 'seuil_alerte' => 5],
            ['nom' => 'Riz Parfumé 5kg',   'prix_vente_conseille' => 3500,  'stock' => 20,  'a_cartouche' => false, 'seuil_alerte' => 3],
            ['nom' => 'Lait Concentré',    'prix_vente_conseille' => 900,   'stock' => 40,  'a_cartouche' => false, 'seuil_alerte' => 8],
            ['nom' => 'Boisson Energie',   'prix_vente_conseille' => 2500,  'stock' => 102, 'a_cartouche' => true,  'cartouche_par_carton' => 12, 'prix_cartouche' => 300, 'seuil_alerte' => 20],
        ];

        foreach ($produitsData as $p) {
            $produit = Produit::create(array_merge($p, ['tenant_id' => $tenant->id]));

            // Créer le mouvement de stock initial si stock > 0
            $stockInitial = $p['stock'] ?? 0;
            if ($stockInitial > 0) {
                StockMouvement::create([
                    'tenant_id'  => $tenant->id,
                    'magasin_id' => $magasinPrincipal->id,
                    'produit_id' => $produit->id,
                    'user_id'    => $adminUser->id,
                    'type'       => 'ajustement_positif',
                    'quantite'   => $stockInitial,
                    'note'       => 'Stock initial seeder',
                ]);
            }
        }

        // ─── 6. Clients ─────────────────────────────────────────────────
        $clientsData = [
            ['nom' => 'Adjoua Marie',     'telephone' => '+22996111001'],
            ['nom' => 'Koffi Jean',       'telephone' => '+22996111002'],
            ['nom' => 'Assogba Pascal',   'telephone' => '+22996111003'],
            ['nom' => 'Hounsou Brice',    'telephone' => '+22996111004', 'limite_credit' => 100000],
        ];

        foreach ($clientsData as $c) {
            Client::create(array_merge($c, ['tenant_id' => $tenant->id]));
        }

        $this->command->info('✅ Données SAÏMOUS/RICCI créées avec succès !');
        $this->command->info('-------------------------------------------');
        $this->command->info('🌍 Super Admin Plateforme : superadmin@estock.com  | mdp: saimous2026');
        $this->command->info('🏢 Admin SAÏMOUS          : admin@saimous.bj       | mdp: saimous2026');
        $this->command->info('🛒 Vendeur                : vendeur@saimous.bj     | mdp: saimous2026');
        $this->command->info('📦 Magasinier             : magasinier@saimous.bj  | mdp: saimous2026');
        $this->command->info('🚚 Livreur                : livreur@saimous.bj     | mdp: saimous2026');
    }
}
