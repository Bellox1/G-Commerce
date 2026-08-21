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

        if ($user && $user->isSuperAdmin() && !$user->tenant) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Accès réservé au super administrateur.'], 403);
            }
            return redirect()->route('tenants.index');
        }

        if ($user && !$user->tenant) {
            if ($user->hasRole('prestataire')) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Compte prestataire non associé à une société.'], 403);
                }
                return redirect()->route('prestataire.dashboard');
            }
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Votre compte n\'est associé à aucune société. Contactez l\'administrateur.'], 403);
            }
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Votre compte n\'est associé à aucune société. Contactez l\'administrateur.']);
        }

        return $next($request);
    }
}
