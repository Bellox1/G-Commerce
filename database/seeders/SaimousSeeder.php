<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Magasin;
use App\Models\Produit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SaimousSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Utilisateur administrateur pour SAÏMOUS
            $user = User::firstOrCreate(
                ['email' => 'mantinoubello123@gmail.com'],
                [
                    'name'       => 'ABOU Koudé', //LAMIDI CHEFIATOU
                    'telephone'  => '+229 0197691110',
                    'role'       => 'admin',
                    'password'   => Hash::make('password'),
                    'actif'      => true,
                ]
            );

            // 2. Société SAÏMOUS (sans marque ni email obligatoire)
            $tenant = Tenant::firstOrCreate(
                ['nom' => 'SAÏMOUS'], //Ma société
                [
                    'activite'        => 'Commerce',
                    'pays'            => 'Bénin',
                    'ville'           => 'Cotonou',
                    'actif'           => true,
                    'proprietaire_id' => $user->id,
                    'offre_code'      => 'locale', // Offre active à vie
                ]
            );

            // Attribuer la société à l'utilisateur
            $user->update(['tenant_id' => $tenant->id]);

            // 3. Deux Dépôts / Magasins : Saint Michel & Dédokpo
            $magasin1 = Magasin::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Dépôt Saint Michel'],
                [
                    'adresse' => 'Saint Michel',
                    'ville'   => 'Cotonou',
                ]
            );

            $magasin2 = Magasin::firstOrCreate(
                ['tenant_id' => $tenant->id, 'nom' => 'Dépôt Dédokpo'],
                [
                    'adresse' => 'Dédokpo',
                    'ville'   => 'Cotonou',
                ]
            );

            // Lier l'utilisateur au premier dépôt (Saint Michel)
            if (!$user->magasin_id) {
                $user->update(['magasin_id' => $magasin1->id]);
            }

            // 4. Liste complète des 103 produits
            $produits = [
                ['nom' => 'Viva 1 kg', 'prix' => 6100],
                ['nom' => 'Viva G', 'prix' => 5000],
                ['nom' => 'Wave G', 'prix' => 4800],
                ['nom' => 'Wave P', 'prix' => 4500],
                ['nom' => 'Klim 300 g', 'prix' => 4500],
                ['nom' => 'Klim 300 g (4800)', 'prix' => 4800],
                ['nom' => 'Yes', 'prix' => 4200],
                ['nom' => 'Too Clean G', 'prix' => 4500],
                ['nom' => 'Too Clean P', 'prix' => 4200],
                ['nom' => 'Sure Clean G', 'prix' => 4700],
                ['nom' => 'Sure Clean 80 g', 'prix' => 4500],
                ['nom' => 'Sure Clean 40 g', 'prix' => 3800],
                ['nom' => 'Supa G', 'prix' => 4500],
                ['nom' => 'Supa 45 g', 'prix' => 3800],
                ['nom' => 'Waw 1 kg', 'prix' => 5300],
                ['nom' => 'Waw 60 g', 'prix' => 3900],
                ['nom' => 'Mama Jay 1 kg', 'prix' => 5500],
                ['nom' => 'Mama Joy 40 g', 'prix' => 2800],
                ['nom' => 'Supa 1 kg', 'prix' => 6300],
                ['nom' => 'Medical 105 g', 'prix' => 550],
                ['nom' => 'Soft Clean', 'prix' => 5800],
                ['nom' => 'Senamie', 'prix' => 5500],
                ['nom' => 'Amazone', 'prix' => 6500],
                ['nom' => '3D', 'prix' => 11500],
                ['nom' => 'Kampé G', 'prix' => 7300],
                ['nom' => 'Kampé S', 'prix' => 6500],
                ['nom' => 'Sai', 'prix' => 6800],
                ['nom' => 'Siri', 'prix' => 13000],
                ['nom' => 'Eva', 'prix' => 13200],
                ['nom' => 'Pamida', 'prix' => 9000],
                ['nom' => 'Cool Fresh', 'prix' => 11300],
                ['nom' => 'Nova', 'prix' => 11000],
                ['nom' => 'Viva S blanc', 'prix' => 7300],
                ['nom' => 'Viva S vert', 'prix' => 7300],
                ['nom' => 'Vedam G', 'prix' => 4300],
                ['nom' => 'Vedam P', 'prix' => 4300],
                ['nom' => 'Crevette G', 'prix' => 9500],
                ['nom' => 'Crevette P', 'prix' => 2500],
                ['nom' => 'Maggi Star', 'prix' => 13000],
                ['nom' => 'Chicken', 'prix' => 3000],
                ['nom' => 'Chicken G', 'prix' => 3000],
                ['nom' => 'Chicken P', 'prix' => 21500],
                ['nom' => 'Mymy pâte', 'prix' => 9800],
                ['nom' => 'Super Pack G', 'prix' => 6700],
                ['nom' => 'Super Pack P', 'prix' => 4500],
                ['nom' => 'Supermega G', 'prix' => 6200],
                ['nom' => 'Super Kill', 'prix' => 3800],
                ['nom' => 'Care Me', 'prix' => 16000],
                ['nom' => 'Swam', 'prix' => 14000],
                ['nom' => 'Coco G blanc', 'prix' => 8000],
                ['nom' => 'Coco G bleu', 'prix' => 7500],
                ['nom' => 'Coco G jaune', 'prix' => 7500],
                ['nom' => 'Cerelac', 'prix' => 14000],
                ['nom' => 'Cowbell 12 g', 'prix' => 9300],
                ['nom' => 'Klemela', 'prix' => 7400],
                ['nom' => 'Milo 270 g', 'prix' => 15500],
                ['nom' => 'Milo 400 g', 'prix' => 15500],
                ['nom' => 'Peak S', 'prix' => 15000],
                ['nom' => 'Cowbell 320 g', 'prix' => 14500],
                ['nom' => 'Charcol', 'prix' => 20000],
                ['nom' => 'Vitalis S', 'prix' => 9200],
                ['nom' => 'Vitalis boîte', 'prix' => 5800],
                ['nom' => 'Bama G', 'prix' => 2100],
                ['nom' => 'Bama S', 'prix' => 3400],
                ['nom' => 'Noir de muscade', 'prix' => 10000],
                ['nom' => 'Whippy G', 'prix' => 13700],
                ['nom' => 'Fatala', 'prix' => 16000],
                ['nom' => 'Semo P', 'prix' => 7000],
                ['nom' => 'Semo G', 'prix' => 7000],
                ['nom' => 'Jago', 'prix' => 13500],
                ['nom' => 'Checkers Chapelai', 'prix' => 7000],
                ['nom' => 'Tom Tom', 'prix' => 9500],
                ['nom' => 'Golden 900 g', 'prix' => 11000],
                ['nom' => 'Golden 300 g', 'prix' => 9000],
                ['nom' => 'Golden 45 g', 'prix' => 10500],
                ['nom' => 'Three Crown', 'prix' => 5000],
                ['nom' => 'Peak boîte', 'prix' => 26500],
                ['nom' => 'Peak 350 g', 'prix' => 20500],
                ['nom' => 'Checkers Vanille P', 'prix' => 7000],
                ['nom' => 'Milo boîte', 'prix' => 26500],
                ['nom' => 'Golden Pen', 'prix' => 5500],
                ['nom' => 'Coco P', 'prix' => 7500],
                ['nom' => 'Coco S blanc', 'prix' => 7000],
                ['nom' => 'Bourn Vita', 'prix' => 19000],
                ['nom' => 'Whippy P', 'prix' => 6000],
                ['nom' => 'Visa 45 g', 'prix' => 0],
                ['nom' => 'Kampé P', 'prix' => 6900],
                ['nom' => 'Delfin', 'prix' => 3800],
                ['nom' => 'Maxam P', 'prix' => 20000],
                ['nom' => 'Sardine', 'prix' => 15000],
                ['nom' => 'Olvatine', 'prix' => 23500],
                ['nom' => 'Peak plaquette', 'prix' => 8200],
                ['nom' => 'Levure', 'prix' => 0],
                ['nom' => 'Klin Smart', 'prix' => 3400],
                ['nom' => 'Super Hart', 'prix' => 22000],
                ['nom' => 'Salé', 'prix' => 9000],
                ['nom' => 'Mix Fruit', 'prix' => 0],
                ['nom' => 'Pilé Tiger', 'prix' => 0],
                ['nom' => 'Olivary', 'prix' => 0],
                ['nom' => 'Chalk', 'prix' => 0],
                [
                    'nom'                  => 'Charlie',
                    'prix'                 => 11000,
                    'a_cartouche'          => true,
                    'cartouche_par_carton' => 2,
                    'prix_cartouche'       => 11000,
                ],
                [
                    'nom'                  => 'Nescafé',
                    'prix'                 => 0,
                    'a_cartouche'          => true,
                    'cartouche_par_carton' => 9,
                    'prix_cartouche'       => 6200,
                ],
                [
                    'nom'                  => 'Lipton',
                    'prix'                 => 29500,
                    'a_cartouche'          => true,
                    'cartouche_par_carton' => 4,
                    'prix_cartouche'       => 7500,
                ],
            ];

            foreach ($produits as $p) {
                $prixVente = isset($p['prix']) && $p['prix'] !== null ? $p['prix'] : 0;

                Produit::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'nom'       => $p['nom'],
                    ],
                    [
                        'prix_vente_conseille'   => $prixVente,
                        'prix_marche'            => $prixVente,
                        'seuil_alerte'           => 5,
                        'actif'                  => true,
                        'stock'                  => 0,
                        'stock_cartouches'       => 0,
                        'a_cartouche'            => $p['a_cartouche'] ?? false,
                        'cartouche_par_carton'   => $p['cartouche_par_carton'] ?? null,
                        'prix_cartouche'         => $p['prix_cartouche'] ?? null,
                    ]
                );
            }
        });
    }
}
