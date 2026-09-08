<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->actif) {
                Auth::logout();
                $message = 'Votre compte a été désactivé. Contactez l\'administrateur.';
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['success' => false, 'message' => $message], 403);
                }
                return back()->withErrors(['email' => $message])->onlyInput('email');
            }

            // Compte rattaché à une société désactivée → connexion impossible (web & mobile).
            if ($user->tenant && !$user->tenant->actif) {
                Auth::logout();
                $message = 'La société associée à votre compte est désactivée. Contactez l\'administrateur.';
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['success' => false, 'message' => $message], 403);
                }
                return back()->withErrors(['email' => $message])->onlyInput('email');
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'success'      => true,
                    'message'      => 'Connexion réussie.',
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                    'user'         => $user,
                    'tenant'       => $user->tenant ? [
                        'id'              => $user->tenant->id,
                        'nom'             => $user->tenant->nom,
                        'marque'          => $user->tenant->marque,
                        'email'           => $user->tenant->email,
                        'ville'           => $user->tenant->ville,
                        'pays'            => $user->tenant->pays,
                        'offre_code'      => $user->tenant->offre_code,
                        'offre_en_pause'  => $user->tenant->offre_en_pause,
                        'offre_statut'    => $user->tenant->offreStatut(),
                        'plan_level'      => $user->tenant->planLevel(),
                        'capabilities'    => [
                            'import'         => $user->tenant->hasCapability('import'),
                            'multi_magasin' => $user->tenant->hasCapability('multi_magasin'),
                            'advanced_stats' => $user->tenant->hasCapability('advanced_stats'),
                            'multi_user'     => $user->tenant->hasCapability('multi_user'),
                            'cost_margin'    => $user->tenant->hasCapability('cost_margin'),
                        ],
                    ] : null,
                ]);
            }

            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->hasRole('prestataire') && !$user->tenant) {
                return redirect()->route('prestataire.dashboard');
            }

            return redirect()->route('dashboard');
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrects.'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrects.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $token = $user->currentAccessToken();
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie.'
            ]);
        }

        return redirect()->route('login');
    }
}
