<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeController extends Controller
{
    public function index()
    {
        $this->authorizeModule('employes');
        $tenant = Auth::user()->tenant;
        $employes = User::where('tenant_id', $tenant->id)
            ->where('id', '!=', $tenant->proprietaire_id)
            ->get();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $employes]);
        }

        return view('employes.index', compact('employes'));
    }

    public function create()
    {
        return view('employes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'telephone' => 'nullable|string|max:30',
            'role'      => 'required|in:admin,vendeur,livreur,magasinier',
            'roles_secondaires' => 'nullable|array',
            'roles_secondaires.*' => 'in:vendeur,livreur,magasinier',
            'password'  => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        User::create([
            'tenant_id'    => $user->tenant_id,
            'name'         => $request->name,
            'email'        => $request->email,
            'telephone'    => $request->telephone,
            'role'         => $request->role,
            'roles_secondaires' => $request->roles_secondaires ?? [],
            'password'     => Hash::make($request->password),
        ]);

        return $this->smartResponse('employes.index', 'Employé créé avec succès.');
    }

    public function edit(User $employe)
    {
        $this->authorizeTenant($employe);
        return view('employes.edit', compact('employe'));
    }

    public function update(Request $request, User $employe)
    {
        $this->authorizeTenant($employe);

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email,' . $employe->id,
            'telephone' => 'nullable|string|max:30',
            'role'      => 'required|in:admin,vendeur,livreur,magasinier',
            'roles_secondaires' => 'nullable|array',
            'roles_secondaires.*' => 'in:vendeur,livreur,magasinier',
            'actif'     => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'email', 'telephone', 'role']);
        $data['actif'] = $request->boolean('actif');
        $data['roles_secondaires'] = $request->roles_secondaires ?? [];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $employe->update($data);

        return $this->smartResponse('employes.index', 'Employé mis à jour.');
    }

    public function destroy(User $employe)
    {
        $this->authorizeTenant($employe);

        if ($employe->id === Auth::id()) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 400);
            }
            return redirect()->route('employes.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $employe->delete();
        return $this->smartResponse('employes.index', 'Employé retiré.');
    }

    private function authorizeTenant(User $employe)
    {
        if ($employe->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }
    }
}
