<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Magasin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function index()
    {
        $this->authorizeModule('tenants');
        $tenants = Tenant::withCount(['magasins', 'users'])->paginate(15);
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        $this->authorizeModule('tenants');
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $this->authorizeModule('tenants');
        
        $request->validate([
            // Société
            'nom' => 'required|string|max:255',
            'marque' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'pays' => 'required|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:tenants,email',
            
            // Administrateur de la société
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_telephone' => 'nullable|string|max:50',
            'admin_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'admin_password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Créer la société (Tenant)
            $tenant = Tenant::create([
                'nom' => $request->nom,
                'marque' => $request->marque,
                'activite' => $request->activite,
                'pays' => $request->pays,
                'ville' => $request->ville,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'actif' => true,
            ]);

            // 2. Créer le premier magasin (par défaut)
            $magasin = Magasin::create([
                'tenant_id' => $tenant->id,
                'nom' => 'Magasin Principal - ' . $tenant->nom,
                'adresse' => $request->ville ?? 'Adresse principale',
            ]);

            // 3. Créer l'administrateur associé au tenant et à son magasin principal
            User::create([
                'tenant_id' => $tenant->id,
                'magasin_id' => $magasin->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'telephone' => $request->admin_telephone,
                'role' => 'admin',
                'roles_secondaires' => [],
                'actif' => true,
                'password' => Hash::make($request->admin_password),
            ]);
        });

        return redirect()->route('tenants.index')->with('success', 'Société et son administrateur créés avec succès !');
    }

    public function show(Tenant $tenant)
    {
        $this->authorizeModule('tenants');
        $tenant->load(['magasins', 'users']);
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $this->authorizeModule('tenants');
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $this->authorizeModule('tenants');

        $request->validate([
            'nom' => 'required|string|max:255',
            'marque' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'pays' => 'required|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:tenants,email,' . $tenant->id,
            'actif' => 'required|boolean',
        ]);

        $tenant->update($request->all());

        return redirect()->route('tenants.show', $tenant)->with('success', 'Société mise à jour avec succès.');
    }

    public function destroy(Tenant $tenant)
    {
        $this->authorizeModule('tenants');
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', 'Société supprimée avec succès.');
    }

    // Gestion des Magasins d'une société par le Super Admin
    public function storeMagasin(Request $request, Tenant $tenant)
    {
        $this->authorizeModule('tenants');

        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
        ]);

        Magasin::create([
            'tenant_id' => $tenant->id,
            'nom' => $request->nom,
            'adresse' => $request->adresse,
        ]);

        return redirect()->back()->with('success', 'Magasin ajouté avec succès !');
    }

    public function destroyMagasin(Tenant $tenant, Magasin $magasin)
    {
        $this->authorizeModule('tenants');

        if ($magasin->tenant_id !== $tenant->id) {
            abort(403);
        }

        $magasin->delete();

        return redirect()->back()->with('success', 'Magasin supprimé avec succès.');
    }
}
