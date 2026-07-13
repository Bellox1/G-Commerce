<?php

namespace App\Http\Controllers;

use App\Models\Arrivage;
use App\Models\ArrivageProduit;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Services\ArrivageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArrivageController extends Controller
{
    public function __construct(private ArrivageService $arrivageService) {}

    public function index()
    {
        $tenant = Auth::user()->tenant;
        $arrivages = Arrivage::where('tenant_id', $tenant->id)
            ->with(['fournisseur', 'magasin', 'user', 'produits'])
            ->orderByDesc('id')
            ->paginate(15);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $arrivages]);
        }

        return view('arrivages.index', compact('arrivages'));
    }

    public function create()
    {
        $this->authorizeModule('arrivages');
        $tenant = Auth::user()->tenant;
        $fournisseurs = Fournisseur::where('tenant_id', $tenant->id)->get();
        $magasins = $tenant->magasins;
        $produits = Produit::where('tenant_id', $tenant->id)->get();

        $produitsJson = $produits->map(function ($p) {
            return [
                'id'    => $p->id,
                'nom'   => $p->nom,
                'prix'  => (int) $p->prix_vente_conseille,
                'stock' => $p->stockGlobal(),
            ];
        })->values();

        $fournisseursJson = $fournisseurs->map(function ($f) {
            return [
                'id'    => $f->id,
                'nom'   => $f->nom,
                'ville' => $f->ville,
                'pays'  => $f->pays,
            ];
        })->values();

        return view('arrivages.create', compact('fournisseurs', 'magasins', 'produits', 'produitsJson', 'fournisseursJson'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('arrivages');
        $request->validate([
            'fournisseur_id'   => 'nullable|exists:fournisseurs,id',
            'magasin_id'       => 'required|exists:magasins,id',
            'taux_change_naira_cfa' => 'required|numeric|min:0.0001',
            'frais_transport_cfa'   => 'required|numeric|min:0',
            'frais_douane_cfa'      => 'required|numeric|min:0',
            'frais_manutention_cfa' => 'required|numeric|min:0',
            'autres_frais_cfa'      => 'required|numeric|min:0',
            'produits'              => 'required|array|min:1',
            'produits.*.produit_id' => 'required|exists:produits,id',
            'produits.*.quantite'   => 'required|integer|min:1',
            'produits.*.prix_unitaire_origine' => 'required|numeric|min:0',
            'produits.*.fournisseur_id' => 'nullable|exists:fournisseurs,id',
        ]);

        $user = Auth::user();

        $data['magasin_id']       = $request->input('magasin_id');
        $data['fournisseur_id']   = $request->input('fournisseur_id');
        $data['taux_change']      = $request->input('taux_change_naira_cfa');
        $data['frais_transport']   = $request->input('frais_transport_cfa', 0);
        $data['frais_douane']      = $request->input('frais_douane_cfa', 0);
        $data['frais_manutention'] = $request->input('frais_manutention_cfa', 0);
        $data['frais_divers']      = $request->input('autres_frais_cfa', 0);

        $data['tenant_id'] = $user->tenant_id;
        $data['user_id']   = $user->id;
        $data['date_arrivage'] = now();
        $data['statut']    = 'en_cours';

        $arrivage = $this->arrivageService->creer($data, $request->produits);

        return $this->smartResponse(route('arrivages.show', $arrivage), 'Arrivage enregistré avec succès. Vous pouvez maintenant le vérifier et le valider.');
    }

    public function show(Arrivage $arrivage)
    {
        $this->authorizeTenant($arrivage);
        $arrivage->load(['fournisseur', 'magasin', 'user', 'produits.produit', 'produits.fournisseur']);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $arrivage]);
        }

        return view('arrivages.show', compact('arrivage'));
    }

    public function edit(Arrivage $arrivage)
    {
        $this->authorizeModule('arrivages');
        $this->authorizeTenant($arrivage);

        $tenant = Auth::user()->tenant;
        $fournisseurs = Fournisseur::where('tenant_id', $tenant->id)->get();
        $magasins = $tenant->magasins;
        $produits = Produit::where('tenant_id', $tenant->id)->get();

        $produitsJson = $produits->map(function ($p) {
            return [
                'id'    => $p->id,
                'nom'   => $p->nom,
                'prix'  => (int) $p->prix_vente_conseille,
                'stock' => $p->stockGlobal(),
            ];
        })->values();

        $fournisseursJson = $fournisseurs->map(function ($f) {
            return [
                'id'    => $f->id,
                'nom'   => $f->nom,
                'ville' => $f->ville,
                'pays'  => $f->pays,
            ];
        })->values();

        $arrivage->load('produits.produit', 'produits.fournisseur');

        return view('arrivages.edit', compact('arrivage', 'fournisseurs', 'magasins', 'produits', 'produitsJson', 'fournisseursJson'));
    }

    public function update(Request $request, Arrivage $arrivage)
    {
        $this->authorizeModule('arrivages');
        $this->authorizeTenant($arrivage);

        $request->validate([
            'fournisseur_id'   => 'nullable|exists:fournisseurs,id',
            'magasin_id'       => 'required|exists:magasins,id',
            'taux_change_naira_cfa' => 'required|numeric|min:0.0001',
            'frais_transport_cfa'   => 'required|numeric|min:0',
            'frais_douane_cfa'      => 'required|numeric|min:0',
            'frais_manutention_cfa' => 'required|numeric|min:0',
            'autres_frais_cfa'      => 'required|numeric|min:0',
            'produits'              => 'required|array|min:1',
            'produits.*.produit_id' => 'required|exists:produits,id',
            'produits.*.quantite'   => 'required|integer|min:1',
            'produits.*.prix_unitaire_origine' => 'required|numeric|min:0',
            'produits.*.fournisseur_id' => 'nullable|exists:fournisseurs,id',
        ]);

        $data['magasin_id']       = $request->input('magasin_id');
        $data['fournisseur_id']   = $request->input('fournisseur_id');
        $data['taux_change']      = $request->input('taux_change_naira_cfa');
        $data['frais_transport']   = $request->input('frais_transport_cfa', 0);
        $data['frais_douane']      = $request->input('frais_douane_cfa', 0);
        $data['frais_manutention'] = $request->input('frais_manutention_cfa', 0);
        $data['frais_divers']      = $request->input('autres_frais_cfa', 0);

        DB::transaction(function () use ($arrivage, $data, $request) {
            $arrivage->produits()->delete();

            foreach ($request->produits as $p) {
                $totalOrigine = $p['quantite'] * $p['prix_unitaire_origine'];
                ArrivageProduit::create([
                    'arrivage_id'          => $arrivage->id,
                    'produit_id'           => $p['produit_id'],
                    'fournisseur_id'       => $p['fournisseur_id'] ?? null,
                    'quantite'             => $p['quantite'],
                    'prix_unitaire_origine'=> $p['prix_unitaire_origine'],
                    'total_origine'        => $totalOrigine,
                ]);
            }

            $arrivage->update($data);
            $arrivage->load('produits');
            $arrivage->recalculer();
        });

        return $this->smartResponse(route('arrivages.show', $arrivage), 'Arrivage mis à jour avec succès.');
    }

    public function valider(Arrivage $arrivage)
    {
        $this->authorizeModule('arrivages');
        $this->authorizeTenant($arrivage);

        if ($arrivage->statut === 'receptionne') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Cet arrivage a déjà été validé et réceptionné.'], 400);
            }
            return redirect()->back()->with('error', 'Cet arrivage a déjà été validé et réceptionné.');
        }

        $this->arrivageService->valider($arrivage);

        return $this->smartResponse(route('arrivages.show', $arrivage), 'L\'arrivage a été validé. Les stocks ont été mis à jour et les prix conseillés ont été ajustés.');
    }

    public function updatePrixSuggere(Request $request, ArrivageProduit $arrivageProduit)
    {
        $this->authorizeModule('arrivages');
        $arrivage = $arrivageProduit->arrivage;
        $this->authorizeTenant($arrivage);

        $request->validate([
            'prix_vente_suggere' => 'required|numeric|min:0',
        ]);

        $arrivageProduit->update([
            'prix_vente_suggere' => $request->input('prix_vente_suggere'),
        ]);

        return $this->smartResponse(route('arrivages.show', $arrivage), 'Prix suggéré mis à jour.');
    }

    public function destroy(Arrivage $arrivage)
    {
        $this->authorizeModule('arrivages');
        $this->authorizeTenant($arrivage);

        if ($arrivage->statut === 'receptionne') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Impossible de supprimer un arrivage déjà réceptionné.'], 400);
            }
            return redirect()->back()->with('error', 'Impossible de supprimer un arrivage déjà réceptionné.');
        }

        $arrivage->delete();

        return $this->smartResponse('arrivages.index', 'Arrivage supprimé avec succès.');
    }

    private function authorizeTenant(Arrivage $arrivage)
    {
        if ($arrivage->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée sur cet arrivage.');
        }
    }
}
