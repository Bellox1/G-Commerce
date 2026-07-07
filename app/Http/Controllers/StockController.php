<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Magasin;
use App\Models\StockMouvement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    /**
     * Affiche le stock global par magasin
     */
    public function index(Request $request)
    {
        $this->authorizeModule('stock');
        $tenant = Auth::user()->tenant;
        $magasins = $tenant->magasins;
        $produits = Produit::where('tenant_id', $tenant->id)->where('actif', true)->get();

        $selectedMagasinId = $request->get('magasin_id', $magasins->first()?->id);
        
        $stockParProduit = [];
        if ($selectedMagasinId) {
            $stockParProduit = $this->stockService->getStockMagasin($selectedMagasinId);
        }

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $stockParProduit, 'magasin_id' => $selectedMagasinId]);
        }

        return view('stock.index', compact('magasins', 'produits', 'selectedMagasinId', 'stockParProduit'));
    }

    /**
     * Affiche l'historique complet des mouvements de stock
     */
    public function mouvements(Request $request)
    {
        $this->authorizeModule('stock');
        $tenant = Auth::user()->tenant;
        
        $query = StockMouvement::where('tenant_id', $tenant->id)
            ->with(['magasin', 'produit', 'user'])
            ->latest('date_mouvement');

        if ($request->filled('magasin_id')) {
            $query->where('magasin_id', $request->magasin_id);
        }
        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $mouvements = $query->paginate(20);
        $magasins = $tenant->magasins;
        $produits = Produit::where('tenant_id', $tenant->id)->get();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $mouvements]);
        }

        return view('stock.mouvements', compact('mouvements', 'magasins', 'produits'));
    }

    /**
     * Effectue un ajustement de stock manuel (inventaire)
     */
    public function ajuster(Request $request)
    {
        $request->validate([
            'magasin_id' => 'required|exists:magasins,id',
            'produit_id' => 'required|exists:produits,id',
            'quantite'   => 'required|integer|min:0',
            'note'       => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        
        $this->stockService->ajuster(
            $user->tenant_id,
            $request->magasin_id,
            $request->produit_id,
            $request->quantite,
            $user->id,
            $request->note ?: 'Ajustement d\'inventaire manuel'
        );

        return $this->smartResponse('stock.index', 'Le stock a été ajusté avec succès.');
    }
}
