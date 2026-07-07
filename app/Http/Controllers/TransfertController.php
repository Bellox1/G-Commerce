<?php

namespace App\Http\Controllers;

use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Transfert;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransfertController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $this->authorizeModule('transferts');
        $tenant = Auth::user()->tenant;
        $transferts = Transfert::where('tenant_id', $tenant->id)
            ->with(['magasinSource', 'magasinDestination', 'produits.produit', 'user'])
            ->latest()
            ->paginate(15);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $transferts]);
        }

        return view('transferts.index', compact('transferts'));
    }

    public function create()
    {
        $this->authorizeModule('transferts');
        $tenant = Auth::user()->tenant;
        $magasins = Magasin::where('tenant_id', $tenant->id)->get();
        $produits = Produit::where('tenant_id', $tenant->id)->where('actif', true)->get();

        // Stock par magasin pour tous les produits
        $stockParMagasin = [];
        foreach ($magasins as $m) {
            $stockParMagasin[$m->id] = $this->stockService->getStockMagasin($m->id);
        }

        $produitsJson = $produits->map(function ($p) use ($stockParMagasin, $magasins) {
            $stocks = [];
            foreach ($magasins as $m) {
                $stocks[$m->id] = $stockParMagasin[$m->id][$p->id] ?? 0;
            }
            return [
                'id'    => $p->id,
                'nom'   => $p->nom,
                'stockParMagasin' => $stocks,
            ];
        })->values();

        return view('transferts.create', compact('magasins', 'produits', 'produitsJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'magasin_source_id'      => 'required|exists:magasins,id',
            'magasin_destination_id' => 'required|exists:magasins,id|different:magasin_source_id',
            'produits'               => 'required|array|min:1',
            'produits.*.produit_id'  => 'required|exists:produits,id',
            'produits.*.quantite'    => 'required|integer|min:1',
            'notes'                  => 'nullable|string|max:500',
        ], [
            'magasin_destination_id.different' => 'Le magasin de destination doit être différent du magasin source. Veuillez choisir deux magasins différents.',
        ]);

        $user = Auth::user();

        // Vérifier les stocks
        foreach ($request->produits as $p) {
            $stockDispo = $this->stockService->getStock($request->magasin_source_id, $p['produit_id']);
            if ($stockDispo < $p['quantite']) {
                $produit = Produit::find($p['produit_id']);
                if (request()->expectsJson() || request()->is('api/*')) {
                    return response()->json(['success' => false, 'message' => "Stock insuffisant pour {$produit->nom} dans le magasin source (Disponible: {$stockDispo})."], 400);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Stock insuffisant pour {$produit->nom} dans le magasin source (Disponible: {$stockDispo}).");
            }
        }

        $data = [
            'tenant_id'              => $user->tenant_id,
            'magasin_source_id'      => $request->magasin_source_id,
            'magasin_destination_id' => $request->magasin_destination_id,
            'user_id'                => $user->id,
            'notes'                  => $request->notes,
        ];

        $transfert = $this->stockService->transferer($data, $request->produits);

        return $this->smartResponse('transferts.index', "Transfert {$transfert->reference} effectué avec succès.");
    }

    public function show(Transfert $transfert)
    {
        $this->authorizeModule('transferts');
        $this->authorizeTenant($transfert);
        $transfert->load(['magasinSource', 'magasinDestination', 'produits.produit', 'user', 'livreur']);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $transfert]);
        }

        return view('transferts.show', compact('transfert'));
    }

    private function authorizeTenant(Transfert $transfert)
    {
        if ($transfert->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée sur ce transfert.');
        }
    }
}
