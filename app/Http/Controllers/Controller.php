<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
            'tenants'    => $user->isSuperAdmin() || $user->hasRole('prestataire'),
            'catalogues' => $user->peutModifierCatalogues(),
            default      => true,
        };

        if (!$allowed) {
            abort(403, 'Accès refusé pour votre profil.');
        }
    }

    /**
     * Retourne une réponse intelligente (JSON pour les requêtes API/AJAX, redirection pour les requêtes classiques).
     */
    protected function smartResponse(string $routeOrUrl, string $successMessage, array $extraData = [], int $status = 200)
    {
        $redirectUrl = filter_var($routeOrUrl, FILTER_VALIDATE_URL) 
            ? $routeOrUrl 
            : (Route::has($routeOrUrl) ? route($routeOrUrl) : url($routeOrUrl));

        if (request()->expectsJson() || request()->is('api/*')) {
            session()->flash('success', $successMessage);
            return response()->json(array_merge([
                'success' => true,
                'message' => $successMessage,
                'redirect' => $redirectUrl,
            ], $extraData), $status);
        }

        return redirect()->to($redirectUrl)->with('success', $successMessage);
    }
}
