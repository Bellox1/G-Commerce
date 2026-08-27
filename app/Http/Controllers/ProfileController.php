<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load('tenant', 'magasin');

        if (request()->expectsJson() || request()->is('api/*')) {

            // Indiquer à l'app mobile quelle section afficher selon le rôle
            $redirectTo = match(true) {
                $user->isSuperAdmin()  => 'tenants',
                $user->isPrestataire() => 'prestataire',
                default                => 'dashboard',
            };

            return response()->json([
                'success'     => true,
                'data'        => $user,
                'redirect_to' => $redirectTo,
                'tenant'      => $user->tenant ? [
                    'offre_code'      => $user->tenant->offre_code,
                    'offre_en_pause'  => $user->tenant->offre_en_pause,
                    'offre_statut'    => $user->tenant->offreStatut(),
                    'plan_level'      => $user->tenant->planLevel(),
                    'offre_active'    => $user->tenant->isOffreActive(),
                    'capabilities' => [
                        'import'         => $user->tenant->hasCapability('import'),
                        'multi_magasin' => $user->tenant->hasCapability('multi_magasin'),
                        'advanced_stats' => $user->tenant->hasCapability('advanced_stats'),
                        'multi_user'     => $user->tenant->hasCapability('multi_user'),
                        'cost_margin'    => $user->tenant->hasCapability('cost_margin'),
                    ],
                ] : null,
            ]);
        }

        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $newEmail = trim($request->input('email'));
        $emailChanged = strtolower($newEmail) !== strtolower($user->email);

        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
        ];

        if ($emailChanged) {
            $rules['current_password_email'] = 'required|string';
        }

        $request->validate($rules, [
            'current_password_email.required' => 'Votre mot de passe actuel est obligatoire pour modifier votre adresse e-mail.',
        ]);

        if ($emailChanged) {
            $key = 'change-email-attempts:' . $user->id;
            $maxAttempts = 3;
            $decayMinutes = 15;

            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                $minutes = ceil($seconds / 60);
                $msg = "Trop de tentatives infructueuses pour modifier l'adresse e-mail. Veuillez réessayer dans {$minutes} minute(s).";
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['success' => false, 'message' => $msg], 429);
                }
                return back()->withInput()->with('error', $msg);
            }

            $passwordInput = $request->input('current_password_email') ?? $request->input('password');
            if (!Hash::check($passwordInput, $user->password)) {
                RateLimiter::hit($key, $decayMinutes * 60);
                $remaining = RateLimiter::remaining($key, $maxAttempts);
                $msg = "Mot de passe actuel incorrect. Modification d'e-mail refusée. (Tentatives restantes : {$remaining})";
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withInput()->with('error', $msg);
            }

            RateLimiter::clear($key);
        }

        $user->update([
            'name'      => $request->name,
            'email'     => $newEmail,
            'telephone' => $request->telephone,
        ]);

        return $this->smartResponse('profile', 'Profil mis à jour avec succès.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return $this->smartResponse('profile', 'Mot de passe modifié.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'delete_password' => 'required|current_password',
        ]);

        $isApi = $request->expectsJson() || $request->is('api/*');

        if ($isApi) {
            $user->tokens()->delete();
        } else {
            Auth::logout();
        }
        $user->delete();

        if ($isApi) {
            return response()->json(['success' => true, 'message' => 'Compte supprimé.']);
        }

        return $this->smartResponse('/', 'Compte supprimé.');
    }
}
