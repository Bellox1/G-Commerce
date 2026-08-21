<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie que le tenant de l'utilisateur dispose de la capacité demandée
 * (import, multi_magasin, advanced_stats, multi_user, cost_margin).
 * Bloque (403) sur le web comme sur l'API si l'offre ne le permet pas.
 */
class CheckPlan
{
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $user = $request->user();
        $tenant = $user?->tenant;

        if ($tenant && !$tenant->hasCapability($capability)) {
            $message = "Cette fonctionnalité n'est pas incluse dans votre offre actuelle ({$tenant->offre_code}). Passer à une offre supérieure pour l'activer.";

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
