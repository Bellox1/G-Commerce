<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
                    'offre_code'    => $user->tenant->offre_code,
                    'plan_level'    => $user->tenant->planLevel(),
                    'offre_active'  => $user->tenant->isOffreActive(),
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

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone'=> 'nullable|string|max:20',
        ]);

        $user->update($request->only(['name', 'email', 'telephone']));

        return $this->smartResponse('profile', 'Profil mis à jour.');
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
