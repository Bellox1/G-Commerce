<?php

namespace App\Http\Controllers;

use App\Models\Magasin;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Transfert;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransfertController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $this->authorizeModule('transferts');
        $tenantId = Auth::user()->tenant_id ?? Auth::user()->tenant?->id;
        $transferts = Transfert::where('tenant_id', $tenantId)
            ->with(['magasinSource', 'magasinDestination', 'produits.produit', 'user'])
            ->latest()
            ->paginate(15);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $transferts]);
        }

        return view('transferts.index', compact('transferts'));
    }

    /**
     * Données de formulaire pour la création (magasins + produits avec stock par magasin).
     * Permet au mobile de filtrer les produits disponibles selon le magasin source choisi.
     */
    public function form()
    {
        try {
            $this->authorizeModule('transferts');
            $user = Auth::user();

            // Résolution du tenant_id — même logique que MagasinController
            $tenantId = $user->tenant_id
                ?? $user->tenant?->id
                ?? optional($user->magasin)->tenant_id;

            // Dernier recours : chercher les magasins liés à cet utilisateur directement
            if (!$tenantId) {
                $magasins = Magasin::where('user_id', $user->id)->orWhere(function ($q) use ($user) {
                    if ($user->magasin_id) $q->where('id', $user->magasin_id);
                })->orderBy('nom')->get(['id', 'nom']);

                return response()->json([
                    'success' => true,
                    'data'    => ['magasins' => $magasins, 'produits' => []],
                ]);
            }

            $magasins = Magasin::where('tenant_id', $tenantId)->orderBy('nom')->get(['id', 'nom']);
            $produits = Produit::where('tenant_id', $tenantId)
                ->where('actif', true)
                ->orderBy('nom')
                ->get(['id', 'nom']);

            $produitsData = $produits->map(function ($produit) use ($magasins) {
                $stocks = [];
                foreach ($magasins as $magasin) {
                    try {
                        $stocks[$magasin->id] = $this->stockService->getStock($magasin->id, $produit->id);
                    } catch (\Throwable $e) {
                        $stocks[$magasin->id] = 0;
                    }
                }
                return [
                    'id'     => $produit->id,
                    'nom'    => $produit->nom,
                    'unite'  => $produit->unite,
                    'stocks' => (object) $stocks,
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => [
                    'magasins' => $magasins,
                    'produits' => $produitsData,
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Transfert form error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données.',
                'data'    => [
                    'magasins' => [],
                    'produits' => [],
                ],
            ], 200);
        }
    }

    public function create()
    {
        $this->authorizeModule('transferts');
        $tenantId = Auth::user()->tenant_id ?? Auth::user()->tenant?->id;
        $magasins = Magasin::where('tenant_id', $tenantId)->get();
        $produits = Produit::where('tenant_id', $tenantId)->where('actif', true)->get();

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
        $transfert->load(['magasinSource', 'magasinDestination', 'produits.produit', 'produit', 'user', 'livreur']);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $transfert]);
        }

        return view('transferts.show', compact('transfert'));
    }

    public function receptionner(Request $request, Transfert $transfert)
    {
        $this->authorizeModule('transferts');
        $this->authorizeTenant($transfert);

        if ($transfert->statut === 'recu' || $transfert->statut === 'livre') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => "Le transfert {$transfert->reference} a déjà été réceptionné avec succès.",
                    'data'    => $transfert->fresh(['magasinSource', 'magasinDestination', 'produits.produit']),
                ]);
            }
            return redirect()->route('transferts.show', $transfert)->with('info', 'Ce transfert a déjà été réceptionné.');
        }

        if ($transfert->statut !== 'en_transit') {
            $msg = 'Seuls les transferts en transit peuvent être réceptionnés.';
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }
            return redirect()->route('transferts.show', $transfert)->with('error', $msg);
        }

        // Quantités réellement reçues (corrige les écarts de comptage)
        $quantitesRecues = [];
        if ($request->has('produits') && is_array($request->produits)) {
            foreach ($request->produits as $p) {
                if (isset($p['produit_id'])) {
                    $quantitesRecues[$p['produit_id']] = (int) ($p['quantite_recue'] ?? $p['quantite'] ?? 0);
                }
            }
        }

        try {
            $this->stockService->receptionner($transfert, $quantitesRecues);
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
            return redirect()->route('transferts.show', $transfert)->with('error', $e->getMessage());
        }

        return $this->smartResponse(route('transferts.show', $transfert), "Transfert {$transfert->reference} réceptionné avec succès.");
    }

    public function edit(Transfert $transfert)
    {
        $this->authorizeModule('transferts');
        $this->authorizeTenant($transfert);

        if ($transfert->statut !== 'en_transit') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Seuls les transferts en transit peuvent être modifiés.'], 400);
            }
            return redirect()->route('transferts.show', $transfert)->with('error', 'Seuls les transferts en transit peuvent être modifiés.');
        }

        $tenantId = Auth::user()->tenant_id ?? Auth::user()->tenant?->id;
        $magasins = Magasin::where('tenant_id', $tenantId)->get();
        $produits = Produit::where('tenant_id', $tenantId)->where('actif', true)->get();

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

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'transfert'   => $transfert->load(['magasinSource', 'magasinDestination', 'produits.produit']),
                    'magasins'    => $magasins,
                    'produits'    => $produits,
                    'produitsJson' => $produitsJson,
                ],
            ]);
        }

        return view('transferts.create', compact('magasins', 'produits', 'produitsJson', 'transfert'));
    }

    public function update(Request $request, Transfert $transfert)
    {
        $this->authorizeModule('transferts');
        $this->authorizeTenant($transfert);

        if ($transfert->statut !== 'en_transit') {
            if (request()->expectsJson() || request()->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Seuls les transferts en transit peuvent être modifiés.'], 400);
            }
            return redirect()->route('transferts.show', $transfert)->with('error', 'Seuls les transferts en transit peuvent être modifiés.');
        }

        $request->validate([
            'magasin_source_id'      => 'required|exists:magasins,id',
            'magasin_destination_id' => 'required|exists:magasins,id|different:magasin_source_id',
            'produits'               => 'required|array|min:1',
            'produits.*.produit_id'  => 'required|exists:produits,id',
            'produits.*.quantite'    => 'required|integer|min:1',
            'notes'                  => 'nullable|string|max:500',
        ], [
            'magasin_destination_id.different' => 'Le magasin de destination doit être différent du magasin source.',
        ]);

        // Vérifier les stocks (en tenant compte du retour des anciennes lignes)
        $oldLines = $transfert->produits;
        foreach ($request->produits as $p) {
            $oldQty = $oldLines->where('produit_id', $p['produit_id'])->sum('quantite');
            $stockDispo = $this->stockService->getStock($request->magasin_source_id, $p['produit_id']) + $oldQty;
            if ($stockDispo < $p['quantite']) {
                $produit = Produit::find($p['produit_id']);
                $msg = "Stock insuffisant pour {$produit->nom} dans le magasin source (Disponible: {$stockDispo}).";
                if (request()->expectsJson() || request()->is('api/*')) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }
                return redirect()->back()->withInput()->with('error', $msg);
            }
        }

        $this->stockService->mettreAjour($transfert, $request->produits, [
            'magasin_source_id'      => $request->magasin_source_id,
            'magasin_destination_id' => $request->magasin_destination_id,
            'notes'                  => $request->notes,
        ]);

        return $this->smartResponse(route('transferts.show', $transfert), "Transfert {$transfert->reference} mis à jour avec succès.");
    }

    public function destroy(Transfert $transfert)
    {
        $this->authorizeModule('transferts');
        $this->authorizeTenant($transfert);

        DB::transaction(function () use ($transfert) {
            $user = Auth::user();

            foreach ($transfert->produits as $tp) {
                // Rendre au magasin source
                StockMouvement::create([
                    'tenant_id'      => $transfert->tenant_id,
                    'magasin_id'     => $transfert->magasin_source_id,
                    'produit_id'     => $tp->produit_id,
                    'user_id'        => $user->id,
                    'type'           => 'transfert_entree',
                    'quantite'       => (int) $tp->quantite,
                    'note'           => "Annulation transfert #" . $transfert->reference . " (récupération source) par " . $user->name,
                    'date_mouvement' => now(),
                ]);

                // Retirer du magasin destination
                StockMouvement::create([
                    'tenant_id'      => $transfert->tenant_id,
                    'magasin_id'     => $transfert->magasin_destination_id,
                    'produit_id'     => $tp->produit_id,
                    'user_id'        => $user->id,
                    'type'           => 'transfert_sortie',
                    'quantite'       => (int) $tp->quantite,
                    'note'           => "Annulation transfert #" . $transfert->reference . " (retrait destination) par " . $user->name,
                    'date_mouvement' => now(),
                ]);
            }

            $transfert->produits()->delete();
            $transfert->delete();
        });

        return $this->smartResponse(route('transferts.index'), 'Transfert annulé et supprimé avec succès.');
    }

    private function authorizeTenant(Transfert $transfert)
    {
        $user = Auth::user();
        if (!$user || $user->isSuperAdmin() || $user->hasRole('prestataire')) return;

        $tenantId = $user->tenant_id 
            ?? $user->tenant?->id 
            ?? optional($user->magasin)->tenant_id;

        if ($tenantId && (int)$transfert->tenant_id !== (int)$tenantId) {
            abort(403, 'Action non autorisée sur ce transfert.');
        }
    }
}
