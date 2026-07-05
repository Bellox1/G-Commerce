<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
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

        return redirect()->route('profile')->with('success', 'Profil mis à jour.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile')->with('success', 'Mot de passe modifié.');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'delete_password' => 'required|current_password',
        ]);

        Auth::logout();
        $user->delete();

        return redirect('/')->with('success', 'Compte supprimé.');
    }
}
