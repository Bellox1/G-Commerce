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

class PrestataireSpaceController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $societes = Tenant::where('partenaire_id', $user->id)
            ->withCount(['magasins', 'users'])
            ->latest()
            ->get();

        $totalSocietes = $societes->count();

        $montantTotal = (float) Commission::where('partenaire_id', $user->id)->sum('montant');
        $montantRegle = (float) Commission::where('partenaire_id', $user->id)->where('statut', 'reglee')->sum('montant');
        $montantRestant = (float) Commission::where('partenaire_id', $user->id)->where('statut', 'en_attente')->sum('montant');

        $historiquePaiements = Commission::where('partenaire_id', $user->id)
            ->where('statut', 'reglee')
            ->with('tenant')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $commissions = Commission::where('partenaire_id', $user->id)
            ->with(['tenant'])
            ->latest()
            ->paginate(10);

        $societesExpirees = $societes->filter(fn($s) => $s->isOffreExpiree() && !$s->offre_en_pause);
        $rules = CommissionRule::where('code', '!=', 'locale')->get();

        return view('prestataire.dashboard', compact(
            'societes',
            'totalSocietes',
            'montantTotal',
            'montantRegle',
            'montantRestant',
            'historiquePaiements',
            'commissions',
            'societesExpirees',
            'rules'
        ));
    }

    public function mesSocietes()
    {
        $user = auth()->user();

        $tenants = Tenant::where('partenaire_id', $user->id)
            ->withCount(['magasins', 'users'])
            ->with('magasins')
            ->latest()
            ->paginate(15);

        return view('prestataire.mes-societes', compact('tenants'));
    }

    public function createTenant()
    {
        $rules = CommissionRule::where('code', '!=', 'locale')->get();
        return view('prestataire.create-tenant', compact('rules'));
    }

    public function storeTenant(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'marque' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'pays' => 'required|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:tenants,email',
            'offre_code' => 'required|exists:commission_rules,code',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_telephone' => 'nullable|string|max:50',
            'admin_password' => [
                'required', 'string', 'min:6', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'admin_password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
        ]);

        $partenaireId = auth()->id();

        DB::transaction(function () use ($request, $partenaireId) {
            $offerRule = CommissionRule::where('code', $request->offre_code)->first();

            $expiresAt = null;
            if ($offerRule && $offerRule->dureeEnMois()) {
                $expiresAt = now()->addMonths($offerRule->dureeEnMois());
            }

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

            $magasin = Magasin::create([
                'tenant_id' => $tenant->id,
                'nom' => 'Magasin Principal - ' . $tenant->nom,
                'adresse' => $request->ville ?? 'Adresse principale',
            ]);

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

            $tenant->update(['proprietaire_id' => $admin->id]);

            if ($offerRule) {
                Commission::create([
                    'partenaire_id' => $partenaireId,
                    'tenant_id' => $tenant->id,
                    'montant' => $offerRule->commission,
                    'statut' => 'en_attente',
                ]);
            }
        });

        return $this->smartResponse('prestataire.mes-societes', 'Société et son administrateur créés avec succès !');
    }

    public function editTenant(Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

        return view('prestataire.edit-tenant', compact('tenant'));
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'marque' => 'nullable|string|max:255',
            'activite' => 'nullable|string|max:255',
            'pays' => 'required|string|max:10',
            'ville' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:tenants,email,' . $tenant->id,
        ]);

        $tenant->update($request->only([
            'nom', 'marque', 'activite', 'pays', 'ville', 'telephone', 'email',
        ]));

        return $this->smartResponse('prestataire.mes-societes', 'Société mise à jour avec succès.');
    }

    public function renewOffer(Request $request, Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

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

        if ($offerRule) {
            Commission::create([
                'partenaire_id' => auth()->id(),
                'tenant_id' => $tenant->id,
                'montant' => $offerRule->commission,
                'statut' => 'en_attente',
            ]);
        }

        return $this->smartResponse('prestataire.dashboard', 'Offre renouvelée avec succès !');
    }

    public function changeOffer(Request $request, Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'offre_code' => 'required|exists:commission_rules,code',
        ]);

        $offerRule = CommissionRule::where('code', $request->offre_code)->first();
        $expiresAt = $offerRule && $offerRule->dureeEnMois()
            ? now()->addMonths($offerRule->dureeEnMois())
            : null;

        $tenant->update([
            'offre_code'        => $request->offre_code,
            'offre_expires_at'  => $expiresAt,
            'offre_en_pause'    => false,
            'offre_pause_depuis' => null,
        ]);

        if ($offerRule) {
            Commission::create([
                'partenaire_id' => auth()->id(),
                'tenant_id'     => $tenant->id,
                'montant'       => $offerRule->commission,
                'statut'        => 'en_attente',
            ]);
        }

        return $this->smartResponse('prestataire.dashboard', 'Offre de la société changée avec succès !');
    }

    public function extendOffer(Request $request, Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

        if (!$tenant->offre_code) {
            return $this->smartResponse('prestataire.dashboard', 'Aucune offre à prolonger pour cette société.', [], 422);
        }

        $offerRule = CommissionRule::where('code', $tenant->offre_code)->first();
        $duree = $offerRule ? $offerRule->dureeEnMois() : null;

        if (!$duree) {
            return $this->smartResponse('prestataire.dashboard', 'Cette offre est à vie et ne nécessite pas de prolongation.', [], 422);
        }

        $expiresAt = $tenant->offre_expires_at && $tenant->offre_expires_at->isFuture()
            ? $tenant->offre_expires_at->addMonths($duree)
            : now()->addMonths($duree);

        $tenant->update([
            'offre_expires_at'   => $expiresAt,
            'offre_en_pause'     => false,
            'offre_pause_depuis' => null,
        ]);

        if ($offerRule) {
            Commission::create([
                'partenaire_id' => auth()->id(),
                'tenant_id'     => $tenant->id,
                'montant'       => $offerRule->commission,
                'statut'        => 'en_attente',
            ]);
        }

        return $this->smartResponse('prestataire.dashboard', 'Offre prolongée avec succès !');
    }

    public function pauseOffer(Request $request, Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

        $tenant->update([
            'offre_en_pause'     => true,
            'offre_pause_depuis' => now(),
        ]);

        return $this->smartResponse('prestataire.dashboard', 'Offre mise en pause. Les fonctionnalités liées sont suspendues.');
    }

    public function resumeOffer(Request $request, Tenant $tenant)
    {
        if ($tenant->partenaire_id !== auth()->id()) {
            abort(403);
        }

        $tenant->update([
            'offre_en_pause'     => false,
            'offre_pause_depuis' => null,
        ]);

        return $this->smartResponse('prestataire.dashboard', 'Offre reprise avec succès. Les fonctionnalités sont de nouveau actives.');
    }
}
