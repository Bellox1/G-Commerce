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

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $produits, 'stock' => $stockParProduit]);
        }

        return view('produits.index', compact('produits', 'magasins', 'selectedMagasinId', 'stockParProduit'));
    }

    public function show(Produit $produit)
    {
        $this->authorizeTenant($produit);
        $tenant = Auth::user()->tenant;
        $magasins = Magasin::where('tenant_id', $tenant->id)->get();

        $stockParMagasin = [];
        foreach ($magasins as $m) {
            $stockParMagasin[$m->id] = $this->stockService->getStock($m->id, $produit->id);
        }

        $mouvements = $produit->mouvements()
            ->with(['magasin', 'user'])
            ->latest('date_mouvement')
            ->take(50)
            ->get();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $produit, 'stock_par_magasin' => $stockParMagasin, 'mouvements' => $mouvements]);
        }

        return view('produits.show', compact('produit', 'magasins', 'stockParMagasin', 'mouvements'));
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string',
            'seuil_alerte' => 'required|integer|min:0',
            'prix_vente_conseille' => 'nullable|integer|min:0',
            'prix_marche' => 'nullable|integer|min:0',
            'magasin_id' => 'required|exists:magasins,id',
            'stock_initial' => 'integer|min:0',
            'a_cartouche' => 'boolean',
            'cartouche_par_carton' => 'nullable|required_if:a_cartouche,1|integer|min:1',
            'prix_cartouche' => 'nullable|numeric|min:0',
        ]);

        $prixCartouche = $request->boolean('a_cartouche') && $request->prix_cartouche
            ? (int) ceil($request->prix_cartouche / 100) * 100
            : null;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('produits', 'public');
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->image_url;
        }

        $produit = Produit::create([
            'tenant_id'           => $tenant->id,
            'nom'                 => $request->nom,
            'image'               => $imagePath,
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

        return $this->smartResponse('produits.index', 'Produit créé avec succès.');
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_url' => 'nullable|url|max:2048',
            'description' => 'nullable|string',
            'seuil_alerte' => 'required|integer|min:0',
            'prix_vente_conseille' => 'nullable|integer|min:0',
            'prix_marche' => 'nullable|integer|min:0',
            'stock' => 'required|integer|min:0',
            'a_cartouche' => 'boolean',
            'cartouche_par_carton' => 'nullable|required_if:a_cartouche,1|integer|min:1',
            'prix_cartouche' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except('image');
        $data['a_cartouche'] = $request->boolean('a_cartouche');

        if ($request->hasFile('image')) {
            if ($produit->image && !str_starts_with($produit->image, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($produit->image);
            }
            $data['image'] = $request->file('image')->store('produits', 'public');
        } elseif ($request->filled('image_url')) {
            if ($produit->image && !str_starts_with($produit->image, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($produit->image);
            }
            $data['image'] = $request->image_url;
        }
        if (!$data['a_cartouche']) {
            $data['cartouche_par_carton'] = null;
            $data['prix_cartouche'] = null;
        } elseif (!empty($data['prix_cartouche'])) {
            $data['prix_cartouche'] = (int) ceil($data['prix_cartouche'] / 100) * 100;
        }

        $produit->update($data);

        return $this->smartResponse('produits.index', 'Produit mis à jour avec succès.');
    }

    public function destroy(Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);
        $produit->delete();
        return $this->smartResponse('produits.index', 'Produit supprimé du catalogue.');
    }

    private function authorizeTenant(Produit $produit)
    {
        if ($produit->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée sur ce produit.');
        }
    }
}
