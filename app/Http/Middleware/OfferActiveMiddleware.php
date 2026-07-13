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

        if ($user && $user->tenant && $user->tenant->isOffreExpiree()) {
            if ($request->isMethod('GET')) {
                return $next($request);
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'offre de votre société a expiré. Contactez votre partenaire ou renouvelez l\'offre pour continuer.',
                ], 403);
            }

            return back()->with('error', 'L\'offre de votre société a expiré. Vous ne pouvez que consulter les données. Contactez votre partenaire ou renouvelez l\'offre.');
        }

        return $next($request);
    }
}
