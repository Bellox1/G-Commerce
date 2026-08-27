<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OfferActiveMiddleware
{
    /**
     * Si l'offre du tenant est expirée, empêcher toute écriture (CRUD).
     * L'utilisateur ne pourra que consulter (read).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->tenant) {
            return $next($request);
        }

        $tenant = $user->tenant;

        // Offre en pause : équivaut à « pas d'offre » → toutes les fonctionnalités bloquées.
        if ($tenant->isOffrePause()) {
            $message = 'L\'offre de votre société est en pause. Toutes les fonctionnalités liées sont suspendues. Contactez votre administrateur ou partenaire pour la reprendre.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'code'    => 'offer_paused',
                    'message' => $message,
                ], 403);
            }

            // Page accessible même en pause (hors groupe offer_active) pour éviter une boucle de redirection.
            if ($request->isMethod('GET')) {
                return redirect()->route('offre')->with('error', $message);
            }

            return back()->with('error', $message);
        }

        // Offre expirée : consultation seule (les écritures sont bloquées).
        if ($tenant->isOffreExpiree()) {
            if ($request->isMethod('GET')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'code'    => 'offer_expired',
                    'message' => 'L\'offre de votre société a expiré. Contactez votre partenaire ou renouvelez l\'offre pour continuer.',
                ], 403);
            }

            return back()->with('error', 'L\'offre de votre société a expiré. Vous ne pouvez que consulter les données. Contactez votre partenaire ou renouvelez l\'offre.');
        }

        return $next($request);
    }
}
