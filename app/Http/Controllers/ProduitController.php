<?php

namespace App\Http\Controllers;

use App\Models\Magasin;
use App\Models\Produit;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduitController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request)
    {
        $this->authorizeModule('produits');
        $tenant = Auth::user()->tenant;
        $produits = Produit::where('tenant_id', $tenant->id)->get();
        $magasins = Magasin::where('tenant_id', $tenant->id)->get();

        $selectedMagasinId = $request->get('magasin_id', $magasins->first()?->id);

        $stockParProduit = [];
        if ($selectedMagasinId) {
            $stockParProduit = $this->stockService->getStockMagasin($selectedMagasinId);
        }

        return view('produits.index', compact('produits', 'magasins', 'selectedMagasinId', 'stockParProduit'));
    }

    public function create()
    {
        $this->authorizeModule('catalogues');
        $magasins = Magasin::where('tenant_id', Auth::user()->tenant_id)->get();
        return view('produits.create', compact('magasins'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('catalogues');
        $user = Auth::user();
        $tenant = $user->tenant;

        $request->validate([
            'nom' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($tenant) {
                    $exists = Produit::where('tenant_id', $tenant->id)
                        ->whereRaw('LOWER(nom) = ?', [mb_strtolower($value)])
                        ->exists();
                    if ($exists) {
                        $fail('Un produit avec ce nom existe déjà.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'seuil_alerte' => 'required|integer|min:0',
            'prix_vente_conseille' => 'nullable|numeric|min:0',
            'prix_marche' => 'nullable|numeric|min:0',
            'magasin_id' => 'required|exists:magasins,id',
            'stock_initial' => 'integer|min:0',
            'a_cartouche' => 'boolean',
            'cartouche_par_carton' => 'nullable|required_if:a_cartouche,1|integer|min:1',
            'prix_cartouche' => 'nullable|numeric|min:0',
        ]);

        $prixCartouche = $request->boolean('a_cartouche') && $request->prix_cartouche
            ? (int) ceil($request->prix_cartouche / 100) * 100
            : null;

        $produit = Produit::create([
            'tenant_id'           => $tenant->id,
            'nom'                 => $request->nom,
            'description'         => $request->description,
            'seuil_alerte'        => $request->seuil_alerte,
            'prix_vente_conseille'=> $request->prix_vente_conseille ?? 0,
            'prix_marche'         => $request->prix_marche ?? 0,
            'stock'               => 0,
            'actif'               => true,
            'a_cartouche'         => $request->boolean('a_cartouche'),
            'cartouche_par_carton'=> $request->boolean('a_cartouche') ? $request->cartouche_par_carton : null,
            'prix_cartouche'      => $prixCartouche,
        ]);

        // Créer le stock initial dans le magasin choisi
        if ($request->stock_initial > 0) {
            $this->stockService->ajuster(
                $tenant->id,
                $request->magasin_id,
                $produit->id,
                $request->stock_initial,
                $user->id,
                'Stock initial'
            );
        }

        return redirect()->route('produits.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function edit(Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);
        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);

        $request->validate([
            'nom' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($produit) {
                    $exists = Produit::where('tenant_id', $produit->tenant_id)
                        ->where('id', '!=', $produit->id)
                        ->whereRaw('LOWER(nom) = ?', [mb_strtolower($value)])
                        ->exists();
                    if ($exists) {
                        $fail('Un produit avec ce nom existe déjà.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'seuil_alerte' => 'required|integer|min:0',
            'prix_vente_conseille' => 'nullable|numeric|min:0',
            'prix_marche' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'a_cartouche' => 'boolean',
            'cartouche_par_carton' => 'nullable|required_if:a_cartouche,1|integer|min:1',
            'prix_cartouche' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();
        $data['a_cartouche'] = $request->boolean('a_cartouche');
        if (!$data['a_cartouche']) {
            $data['cartouche_par_carton'] = null;
            $data['prix_cartouche'] = null;
        } elseif (!empty($data['prix_cartouche'])) {
            $data['prix_cartouche'] = (int) ceil($data['prix_cartouche'] / 100) * 100;
        }

        $produit->update($data);

        return redirect()->route('produits.index')->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);
        $produit->delete();
        return redirect()->route('produits.index')->with('success', 'Produit supprimé du catalogue.');
    }

    private function authorizeTenant(Produit $produit)
    {
        if ($produit->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée sur ce produit.');
        }
    }
}
