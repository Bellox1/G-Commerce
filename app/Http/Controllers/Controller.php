<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function authorizeModule(string $permission): void
    {
        $user = Auth::user();

        $allowed = match ($permission) {
            'produits'   => $user->peutGererProduits(),
            'arrivages'  => $user->peutGererArrivages(),
            'magasins'   => $user->peutGererMagasins(),
            'ventes'     => $user->peutGererVentes(),
            'clients'    => $user->peutGererClients(),
            'dettes'     => $user->peutGererDettes(),
            'stock'      => $user->peutGererStock(),
            'transferts' => $user->peutGererTransferts(),
            'employes'   => $user->peutGererUtilisateurs(),
            'livraisons' => $user->peutGererLivraisons(),
            'tenants'    => $user->isSuperAdmin(),
            'catalogues' => $user->peutModifierCatalogues(),
            default      => true,
        };

        if (!$allowed) {
            abort(403, 'Accès refusé pour votre profil.');
        }
    }
}
