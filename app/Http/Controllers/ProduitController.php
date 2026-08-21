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

        $query = Produit::where('tenant_id', $tenant->id);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $sort = $request->get('sort', 'az');
        $direction = $sort === 'za' ? 'desc' : 'asc';
        // On renvoie tous les produits (cohérent avec le mobile qui charge tout le catalogue)
        $produits = $query->orderBy('nom', $direction)->get();

        $magasins = Magasin::where('tenant_id', $tenant->id)->get();
        $selectedMagasinId = $request->get('magasin_id', 'all');

        $stockParProduit = [];
        $stockCartouchesParProduit = [];
        if ($selectedMagasinId && $selectedMagasinId !== 'all') {
            $stockParProduit = $this->stockService->getStockMagasin($selectedMagasinId);
            $stockCartouchesParProduit = $this->stockService->getStockMagasinCartouches($selectedMagasinId);
        } else {
            $stockParProduit = $this->stockService->getStockTotalParProduit();
            $stockCartouchesParProduit = $this->stockService->getStockTotalCartouchesParProduit();
        }

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success'       => true,
                'data'          => $produits,
                'stock'         => $stockParProduit,
                'stock_cartouches' => $stockCartouchesParProduit,
            ]);
        }

        return view('produits.index', compact('produits', 'magasins', 'selectedMagasinId', 'stockParProduit', 'stockCartouchesParProduit'));
    }

    public function show(Produit $produit)
    {
        $this->authorizeTenant($produit);
        $tenant = Auth::user()->tenant;
        $magasins = Magasin::where('tenant_id', $tenant->id)->get();

        $stockParMagasin = [];
        $stockCartouchesParMagasin = [];
        foreach ($magasins as $m) {
            $detail = $this->stockService->getStockDetail($m->id, $produit->id);
            $stockParMagasin[$m->id] = $detail['cartons'];
            $stockCartouchesParMagasin[$m->id] = $detail['cartouches'];
        }

        if (request()->expectsJson() || request()->is('api/*')) {
            // Les mouvements sont chargés à part, paginés (voir mouvements()).
            return response()->json([
                'success'                    => true,
                'produit'                    => $produit,
                'magasins'                   => $magasins,
                'stockParMagasin'            => $stockParMagasin,
                'stockCartouchesParMagasin'  => $stockCartouchesParMagasin,
            ]);
        }

        $mouvements = $produit->mouvements()
            ->with(['magasin', 'user', 'reference'])
            ->latest('date_mouvement')
            ->take(50)
            ->get();

        return view('produits.show', compact('produit', 'magasins', 'stockParMagasin', 'mouvements'));
    }

    /**
     * Mouvements d'un produit, paginés (lots de 10) pour le mobile.
     */
    public function mouvements(Produit $produit, Request $request)
    {
        $this->authorizeTenant($produit);
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $paginator = $produit->mouvements()
            ->with(['magasin', 'user', 'reference'])
            ->latest('date_mouvement')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
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
            'magasin_id' => 'nullable|exists:magasins,id',
            'stock_initial' => 'nullable|integer|min:0',
            'stocks' => 'nullable|array',
            'stocks.*' => 'integer|min:0',
            'stocks_cartouches' => 'nullable|array',
            'stocks_cartouches.*' => 'integer|min:0',
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

        // Créer le stock initial par magasin (découplé d'un magasin unique)
        $stocks = $request->input('stocks', []);
        if (empty($stocks) && $request->filled('magasin_id') && $request->stock_initial > 0) {
            $stocks = [$request->magasin_id => $request->stock_initial];
        }
        $isCartouche = $request->boolean('a_cartouche');
        $cpc = $isCartouche ? max(1, (int) ($request->cartouche_par_carton ?? 1)) : 1;
        $stocksCartouches = $request->input('stocks_cartouches', []);
        foreach ($stocks as $magasinId => $qte) {
            $qte = (int) $qte;
            // Les cartouches isolées ne concernent que les produits a_cartouche,
            // et ne doivent jamais atteindre un carton complet (< cpc).
            $qteCartouches = 0;
            if ($isCartouche) {
                $raw = (int) ($stocksCartouches[$magasinId] ?? 0);
                $qteCartouches = max(0, min($raw, $cpc - 1));
            }
            if ($qte > 0 || $qteCartouches > 0 || $this->stockService->getStock($magasinId, $produit->id) > 0) {
                $this->stockService->ajuster(
                    $tenant->id,
                    $magasinId,
                    $produit->id,
                    $qte,
                    $user->id,
                    'Stock initial',
                    $qteCartouches
                );
            }
        }
        $produit->syncStock();

        return $this->smartResponse('produits.index', 'Produit créé avec succès.');
    }

    public function edit(Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);
        $magasins = Magasin::where('tenant_id', Auth::user()->tenant_id)->get();
        $stockParMagasin = [];
        $stockCartouchesParMagasin = [];
        foreach ($magasins as $m) {
            $detail = $this->stockService->getStockDetail($m->id, $produit->id);
            $stockParMagasin[$m->id] = $detail['cartons'];
            $stockCartouchesParMagasin[$m->id] = $detail['cartouches'];
        }
        return view('produits.edit', compact('produit', 'magasins', 'stockParMagasin', 'stockCartouchesParMagasin'));
    }

    /**
     * Vue dédiée de gestion des stocks par magasin
     */
    public function stockEdit(Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);
        $magasins = Magasin::where('tenant_id', Auth::user()->tenant_id)->get();
        $stockParMagasin = [];
        $stockCartouchesParMagasin = [];
        foreach ($magasins as $m) {
            $detail = $this->stockService->getStockDetail($m->id, $produit->id);
            $stockParMagasin[$m->id] = $detail['cartons'];
            $stockCartouchesParMagasin[$m->id] = $detail['cartouches'];
        }
        return view('produits.stocks', compact('produit', 'magasins', 'stockParMagasin', 'stockCartouchesParMagasin'));
    }

    public function stockUpdate(Request $request, Produit $produit)
    {
        $this->authorizeModule('catalogues');
        $this->authorizeTenant($produit);
        $request->validate([
            'stocks'              => 'nullable|array',
            'stocks.*'            => 'integer|min:0',
            'stocks_cartouches'   => 'nullable|array',
            'stocks_cartouches.*' => 'integer|min:0',
        ]);

        $isCartouche = $produit->a_cartouche;
        $cpc = $isCartouche ? max(1, (int) ($produit->cartouche_par_carton ?? 1)) : 1;
        $stocksCartouches = $request->input('stocks_cartouches', []);
        foreach ($request->input('stocks', []) as $magasinId => $qte) {
            $qte = (int) $qte;
            $qteCartouches = 0;
            if ($isCartouche) {
                $raw = (int) ($stocksCartouches[$magasinId] ?? 0);
                $qteCartouches = max(0, min($raw, $cpc - 1));
            }
            if ($qte > 0 || $qteCartouches > 0 || $this->stockService->getStock($magasinId, $produit->id) > 0) {
                $this->stockService->ajuster(
                    $produit->tenant_id,
                    $magasinId,
                    $produit->id,
                    $qte,
                    Auth::id(),
                    'Ajustement stock manuel',
                    $qteCartouches
                );
            }
        }
        $produit->syncStock();

        return $this->smartResponse('produits.show', 'Stocks par magasin mis à jour avec succès.');
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
            'stock' => 'nullable|integer|min:0',
            'stocks' => 'nullable|array',
            'stocks.*' => 'integer|min:0',
            'stocks_cartouches' => 'nullable|array',
            'stocks_cartouches.*' => 'integer|min:0',
            'a_cartouche' => 'boolean',
            'cartouche_par_carton' => 'nullable|required_if:a_cartouche,1|integer|min:1',
            'prix_cartouche' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except(['image', 'stock', 'stocks', 'stocks_cartouches']);
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

        // Mise à jour du stock par magasin (ajustement inventaire)
        $isCartouche = $request->boolean('a_cartouche');
        $cpc = $isCartouche ? max(1, (int) ($request->cartouche_par_carton ?? 1)) : 1;
        $stocksCartouches = $request->input('stocks_cartouches', []);
        foreach ($request->input('stocks', []) as $magasinId => $qte) {
            $qte = (int) $qte;
            $qteCartouches = 0;
            if ($isCartouche) {
                $raw = (int) ($stocksCartouches[$magasinId] ?? 0);
                $qteCartouches = max(0, min($raw, $cpc - 1));
            }
            if ($qte > 0 || $qteCartouches > 0 || $this->stockService->getStock($magasinId, $produit->id) > 0) {
                $this->stockService->ajuster(
                    $produit->tenant_id,
                    $magasinId,
                    $produit->id,
                    $qte,
                    Auth::id(),
                    'Ajustement stock (édition produit)',
                    $qteCartouches
                );
            }
        }
        $produit->syncStock();

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
