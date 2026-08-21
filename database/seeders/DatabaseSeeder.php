<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'pilotixcontact@gmail.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role'     => 'super_admin',
            ]
        );

        $tenant = \App\Models\Tenant::firstOrCreate(
            ['nom' => 'Pilotix'],
            [
                'marque'   => 'Pilotix',
                'email'    => 'mantinoubello123@gmail.com',
                'pays'     => 'Sénégal',
                'ville'    => 'Dakar',
                'actif'    => true,
            ]
        );

        // Attribuer une offre active au tenant pour débloquer le CRUD web
        // (sinon OfferActiveMiddleware bloque toutes les écritures sur le web).
        if (empty($tenant->offre_code) || $tenant->isOffreExpiree()) {
            $tenant->update([
                'offre_code'       => 'professionnel',
                'offre_expires_at' => now()->addYear(),
            ]);
        }

        \App\Models\User::firstOrCreate(
            ['email' => 'mantinoubello123@gmail.com'],
            [
                'name'      => 'Administrateur Pilotix',
                'tenant_id' => $tenant->id,
                'role'      => 'admin',
                'actif'     => true,
                'password'  => Hash::make('password'),
            ]
        );

        \App\Models\Magasin::firstOrCreate(
            ['tenant_id' => $tenant->id, 'nom' => 'Magasin Principal'],
            [
                'ville' => 'Dakar',
                'loyer' => 0,
            ]
        );

        $this->call(DemoDataSeeder::class);
    }
}
