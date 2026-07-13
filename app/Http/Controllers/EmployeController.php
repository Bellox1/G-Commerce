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
            'email'     => 'required|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'role'      => 'required|in:admin,vendeur,livreur,magasinier',
            'roles_secondaires' => 'nullable|array',
            'roles_secondaires.*' => 'in:vendeur,livreur,magasinier',
            'password'  => 'required|string|min:6|confirmed',
            'salaire'   => 'nullable|numeric|min:0',
        ]);

        $currentUser = Auth::user();
        $existingUser = User::withoutGlobalScopes()->where('email', $request->email)->first();

        if ($existingUser) {
            $roles = $existingUser->roles_secondaires ?? [];
            if ($existingUser->role === 'prestataire') {
                $existingUser->update([
                    'tenant_id' => $currentUser->tenant_id,
                    'magasin_id' => $currentUser->magasin_id,
                    'role' => $request->role,
                ]);
                if (!in_array('prestataire', $roles)) {
                    $roles[] = 'prestataire';
                }
                if ($request->roles_secondaires) {
                    foreach ($request->roles_secondaires as $sr) {
                        if (!in_array($sr, $roles)) {
                            $roles[] = $sr;
                        }
                    }
                }
                $existingUser->update(['roles_secondaires' => $roles]);
            } else {
                if ($request->expectsJson() || request()->is('api/*')) {
                    return response()->json(['success' => false, 'message' => 'Cet email est déjà utilisé.'], 422);
                }
                return back()->withInput()->with('error', 'Cet email est déjà utilisé par un autre compte.');
            }

            return $this->smartResponse('employes.index', 'Employé créé avec succès.');
        }

        User::create([
            'tenant_id'    => $currentUser->tenant_id,
            'name'         => $request->name,
            'email'        => $request->email,
            'telephone'    => $request->telephone,
            'salaire'      => $request->salaire,
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
            'email'     => 'required|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'role'      => 'required|in:admin,vendeur,livreur,magasinier',
            'roles_secondaires' => 'nullable|array',
            'roles_secondaires.*' => 'in:vendeur,livreur,magasinier',
            'actif'     => 'nullable|boolean',
            'salaire'   => 'nullable|numeric|min:0',
        ]);

        if ($request->email !== $employe->email) {
            $conflict = User::withoutGlobalScopes()->where('email', $request->email)->where('id', '!=', $employe->id)->first();
            if ($conflict) {
                if ($request->expectsJson() || request()->is('api/*')) {
                    return response()->json(['success' => false, 'message' => 'Cet email est déjà utilisé.'], 422);
                }
                return back()->withInput()->with('error', 'Cet email est déjà utilisé par un autre compte.');
            }
        }

        $data = $request->only(['name', 'email', 'telephone', 'role']);
        $data['actif'] = $request->boolean('actif');
        $data['salaire'] = $request->salaire ?: null;
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
