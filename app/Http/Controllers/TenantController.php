<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Magasin;
use App\Models\User;
use App\Models\Commission;
use App\Models\CommissionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function index()
    {
        $this->authorizeModule('tenants');
        
        $user = auth()->user();
        $query = Tenant::query();
        if (!$user->isSuperAdmin() && $user->hasRole('prestataire')) {
            $query->where('partenaire_id', $user->id);
        }
        
        $tenants = $query->withCount(['magasins', 'users'])->paginate(15);

        // Statistiques (uniquement pour le super admin)
        $stats = null;
        if ($user->isSuperAdmin()) {
            $nbPartenaires = User::where('role', 'prestataire')
                ->orWhereJsonContains('roles_secondaires', 'prestataire')
                ->count();
            $nbVentes = Commission::count();

            $rulesMap = CommissionRule::pluck('prix', 'commission')->toArray();
            $totalPrixVente = Commission::get()->sum(fn($c) => $rulesMap[$c->montant] ?? 0);

            $totalAPayer = (float) Commission::where('statut', 'en_attente')->sum('montant');
            $totalRegle = (float) Commission::where('statut', 'reglee')->sum('montant');
            $revenuNet = $totalPrixVente - $totalAPayer - $totalRegle;

            // Stats par offre
            $offresVendues = Commission::selectRaw('montant, COUNT(*) as nb_ventes, SUM(montant) as total_commission')
                ->groupBy('montant')
                ->get()
                ->map(function ($item) use ($rulesMap) {
                    $prix = $rulesMap[$item->montant] ?? 0;
                    $item->nom_offre = CommissionRule::where('commission', $item->montant)->value('nom') ?? 'Inconnu';
                    $item->prix = $prix;
                    $item->total_ventes = $prix * $item->nb_ventes;
                    return $item;
                })
                ->sortByDesc('nb_ventes')
                ->values();

            $topVendu = $offresVendues->first();
            $topRevenue = $offresVendues->sortByDesc('total_ventes')->first();

            $stats = compact('nbPartenaires', 'nbVentes', 'totalPrixVente', 'totalAPayer', 'totalRegle', 'revenuNet', 'offresVendues', 'topVendu', 'topRevenue');
        }

        $societesExpirees = Tenant::whereHas('partenaire', fn($q) => $q->where('role', 'prestataire'))
            ->where('offre_expires_at', '<', now())
            ->whereNotNull('offre_code')
            ->with('partenaire')
            ->get();

        $rules = CommissionRule::all();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $tenants]);
        }

        return view('tenants.index', compact('tenants', 'stats', 'societesExpirees', 'rules'));
    }

    public function create()
    {
        $this->authorizeModule('tenants');
        $rules = \App\Models\CommissionRule::all();
        $partners = [];
        if (auth()->user()->isSuperAdmin()) {
            $partners = User::where('role', 'prestataire')->get();
        }
        return view('tenants.create', compact('rules', 'partners'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('tenants');
        
        $rules = [
            // Société
            'nom' => 'required|string|max:255',
            'marque' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'pays' => 'required|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:tenants,email',
            
            // Offre choisie
            'offre_code' => 'required|exists:commission_rules,code',
            
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
        ];

        if (auth()->user()->isSuperAdmin()) {
            $rules['partenaire_id'] = 'nullable|exists:users,id';
        }

        $request->validate($rules, [
            'admin_password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
        ]);

        $partenaireId = null;
        if (auth()->user()->hasRole('prestataire')) {
            $partenaireId = auth()->id();
        } elseif (auth()->user()->isSuperAdmin()) {
            $partenaireId = $request->partenaire_id;
        }

        DB::transaction(function () use ($request, $partenaireId) {
            $offerRule = CommissionRule::where('code', $request->offre_code)->first();

            $expiresAt = null;
            if ($offerRule && $offerRule->dureeEnMois()) {
                $expiresAt = now()->addMonths($offerRule->dureeEnMois());
            }

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
                'partenaire_id' => $partenaireId,
                'offre_code' => $request->offre_code,
                'offre_expires_at' => $expiresAt,
            ]);

            // 2. Créer le premier magasin (par défaut)
            $magasin = Magasin::create([
                'tenant_id' => $tenant->id,
                'nom' => 'Magasin Principal - ' . $tenant->nom,
                'adresse' => $request->ville ?? 'Adresse principale',
            ]);

            // 3. Créer l'administrateur associé au tenant et à son magasin principal
            $admin = User::create([
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

            // Définir le propriétaire de la société
            $tenant->update(['proprietaire_id' => $admin->id]);

            // 4. Générer automatiquement la commission si un partenaire est lié
            if ($partenaireId) {
                $offerRule = \App\Models\CommissionRule::where('code', $request->offre_code)->first();
                if ($offerRule) {
                    \App\Models\Commission::create([
                        'partenaire_id' => $partenaireId,
                        'tenant_id' => $tenant->id,
                        'montant' => $offerRule->commission,
                        'statut' => 'en_attente',
                    ]);
                }
            }
        });

        return $this->smartResponse('tenants.index', 'Société et son administrateur créés avec succès et commission générée !');
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

        return $this->smartResponse(route('tenants.show', $tenant), 'Société mise à jour avec succès.');
    }

    public function destroy(Tenant $tenant)
    {
        $this->authorizeModule('tenants');
        $tenant->delete();
        return $this->smartResponse('tenants.index', 'Société supprimée avec succès.');
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

        return $this->smartResponse(route('tenants.show', $tenant), 'Magasin ajouté avec succès !');
    }

    public function destroyMagasin(Tenant $tenant, Magasin $magasin)
    {
        $this->authorizeModule('tenants');

        if ($magasin->tenant_id !== $tenant->id) {
            abort(403);
        }

        $magasin->delete();

        return $this->smartResponse(route('tenants.show', $tenant), 'Magasin supprimé avec succès.');
    }

    public function renewOffer(Request $request, Tenant $tenant)
    {
        $this->authorizeModule('tenants');

        $request->validate([
            'offre_code' => 'required|exists:commission_rules,code',
        ]);

        $offerRule = CommissionRule::where('code', $request->offre_code)->first();

        $expiresAt = null;
        if ($offerRule && $offerRule->dureeEnMois()) {
            $expiresAt = now()->addMonths($offerRule->dureeEnMois());
        }

        $tenant->update([
            'offre_code' => $request->offre_code,
            'offre_expires_at' => $expiresAt,
        ]);

        if ($offerRule && $tenant->partenaire_id) {
            Commission::create([
                'partenaire_id' => $tenant->partenaire_id,
                'tenant_id' => $tenant->id,
                'montant' => $offerRule->commission,
                'statut' => 'en_attente',
            ]);
        }

        return $this->smartResponse(route('tenants.index'), 'Offre renouvelée avec succès !');
    }
}
