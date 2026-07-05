<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMiddleware
{
    /**
     * Si l'utilisateur est super_admin (sans tenant), le rediriger vers /tenants.
     * Pour tous les autres, s'assurer qu'ils ont bien un tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isSuperAdmin()) {
            // Le super_admin n'a pas de tenant : on le renvoie vers la gestion des sociétés
            return redirect()->route('tenants.index');
        }

        if ($user && !$user->tenant) {
            // Utilisateur sans tenant (incohérence de données)
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte n\'est associé à aucune société. Contactez l\'administrateur.']);
        }

        return $next($request);
    }
}
