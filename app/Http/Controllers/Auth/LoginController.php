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
            if ($request->expectsJson() || $request->is('api/*')) {
                $user = Auth::user();
                $token = $user->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'success'      => true,
                    'message'      => 'Connexion réussie.',
                    'access_token' => $token,
                    'token_type'   => 'Bearer',
                    'user'         => $user
                ]);
            }

            $request->session()->regenerate();
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
