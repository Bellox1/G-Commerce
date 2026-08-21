<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Dette;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Vente;
use App\Services\VenteService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenteController extends Controller
{
    public function __construct(
        private VenteService $venteService,
        private StockService $stockService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        // Période (par défaut : aujourd'hui)
        $periode = $request->input('periode', 'aujourd_hui');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');

        $applyPeriode = function ($q) use ($periode, $dateDebut, $dateFin) {
            if ($periode === 'tous') {
                return;
            }
            if ($periode === 'perso') {
                if ($dateDebut) {
                    $q->whereDate('date_vente', '>=', $dateDebut);
                }
                if ($dateFin) {
                    $q->whereDate('date_vente', '<=', $dateFin);
                }
                return;
            }
            $days = ['avant_hier' => 2, 'hier' => 1, 'aujourd_hui' => 0][$periode] ?? 0;
            $q->whereDate('date_vente', now()->subDays($days)->toDateString());
        };

        $query = Vente::where('tenant_id', $tenant->id)
            ->with(['client', 'user', 'magasin', 'dette']);

        $applyPeriode($query);

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($c) use ($search) {
                      $c->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%");
                  });
            });
        }

        $statut = $request->input('statut_paiement');
        if ($statut && $statut !== 'tous') {
            $query->where('statut_paiement', $statut);
        }

        $perPage = $request->input('per_page', 15);
        $ventes = $query->latest('date_vente')->paginate($perPage);

        // Stats sur la période sélectionnée (indépendantes du filtre statut côté client)
        $allQuery = Vente::where('tenant_id', $tenant->id);
        $applyPeriode($allQuery);
        $totalMontant = (clone $allQuery)->sum('montant_total');
        $totalPaye = (clone $allQuery)->sum('montant_paye');
        $nbVentes = (clone $allQuery)->count();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $ventes,
                'stats' => compact('totalMontant', 'totalPaye', 'nbVentes'),
            ]);
        }

        return view('ventes.index', compact('ventes', 'periode', 'dateDebut', 'dateFin'));
    }

    public function create()
    {
        $this->authorizeModule('ventes');
        $user = Auth::user();
        $tenant = $user->tenant;

        // Si le vendeur est assigné à un magasin, on le force, sinon on prend le premier
        $magasins = $tenant->magasins;
        $selectedMagasin = $user->magasin ?: $magasins->first();

        if (!$selectedMagasin) {
            return redirect()->route('dashboard')->with('error', 'Aucun magasin n\'est enregistré pour votre entité.');
        }

        $clients = Client::where('tenant_id', $tenant->id)->get();
        $clientsJson = $clients->map(function ($c) {
            return [
                'id'       => $c->id,
                'nom'      => $c->nomComplet(),
                'telephone'=> $c->telephone,
            ];
        })->values();

        // On récupère uniquement les produits qui ont du stock disponible
        $tousProduits = Produit::where('tenant_id', $tenant->id)->where('actif', true)->get();
        $produits = collect();
        
        foreach ($tousProduits as $p) {
            $stock = $this->stockService->getStock($selectedMagasin->id, $p->id);
            if ($stock > 0) {
                $p->stock_dispo = $stock;
                $produits->push($p);
            }
        }

        // Préparer les données pour l'autocomplete JS
        $produitsJson = $produits->map(function ($p) {
            $prixCartouche = $p->prix_cartouche
                ? (int) $p->prix_cartouche
                : ($p->cartouche_par_carton ? (int) ceil(($p->prix_vente_conseille / $p->cartouche_par_carton) / 100) * 100 : null);
            return [
                'id'                   => $p->id,
                'nom'                  => $p->nom,
                'prix'                 => (int) $p->prix_vente_conseille,
                'stock'                => $p->stock_dispo,
                'a_cartouche'          => $p->a_cartouche,
                'cartouche_par_carton' => $p->cartouche_par_carton,
                'prix_cartouche'       => $prixCartouche,
            ];
        })->values();

        return view('ventes.create', compact('clients', 'clientsJson', 'produits', 'produitsJson', 'selectedMagasin', 'magasins'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('ventes');
        $request->validate([
            'magasin_id'                  => 'required|exists:magasins,id',
            'ventes'                      => 'required|array|min:1',
            'ventes.*.client_id'          => 'nullable|exists:clients,id',
            'ventes.*.montant_paye'       => 'nullable|numeric|min:0',
            'ventes.*.montant_remis'      => 'nullable|numeric|min:0',
            'ventes.*.lignes'             => 'required|array|min:1',
            'ventes.*.lignes.*.produit_id'=> 'required|exists:produits,id',
            'ventes.*.lignes.*.quantite'  => 'nullable|integer|min:0',
            'ventes.*.lignes.*.quantite_cartouche' => 'nullable|integer|min:0',
            'ventes.*.lignes.*.prix_vente'=> 'nullable|numeric|min:0',
            'ventes.*.lignes.*.prix_cartouche' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $magasinId = $request->magasin_id;
        $saved = [];

        // Déterminer quelles ventes traiter
        $saveOne = $request->input('save_one');
        $indices = ($saveOne !== null && $saveOne !== '') ? [(int) $saveOne] : array_keys($request->ventes);

        foreach ($indices as $i) {
            if (!isset($request->ventes[$i])) continue;
            $vData = $request->ventes[$i];

            $estAnonyme = empty($vData['client_id']);
            $aCredit = !$estAnonyme && (bool) ($vData['a_credit'] ?? false);

            $montantRemis = $vData['montant_remis'] ?? null;
            $montantRemis = ($montantRemis !== null && $montantRemis !== '') ? (float) $montantRemis : null;

            $lignesPourService = [];
            $totalLignes = 0;

            foreach ($vData['lignes'] as $l) {
                $produit = Produit::find($l['produit_id']);
                $qteCarton = (int) ($l['quantite'] ?? 0);
                $qteCartouche = (int) ($l['quantite_cartouche'] ?? 0);

                if ($qteCarton === 0 && $qteCartouche === 0) {
                    if (request()->expectsJson() || request()->is('api/*')) {
                        return response()->json(['success' => false, 'message' => "La quantité doit être supérieure à zéro pour {$produit->nom}."], 400);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "La quantité doit être supérieure à zéro pour {$produit->nom}.");
                }

                // Un produit non déclaré "a_cartouche" ne peut pas être vendu en cartouches.
                if (!$produit->a_cartouche && $qteCartouche > 0) {
                    $msg = "Le produit « {$produit->nom} » n'est pas configuré pour la vente en cartouches.";
                    if (request()->expectsJson() || request()->is('api/*')) {
                        return response()->json(['success' => false, 'message' => $msg], 400);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $msg);
                }

                // Vérifier les stocks globalement (en cartouches pour gérer les cartouches isolées)
                $cartoucheParCarton = max(1, (int) ($produit->cartouche_par_carton ?? 1));
                $detail = $this->stockService->getStockDetail($magasinId, $l['produit_id']);
                $dispoCartouches = $detail['cartons'] * $cartoucheParCarton + $detail['cartouches'];
                $needCartouches = $qteCarton * $cartoucheParCarton + $qteCartouche;

                if ($dispoCartouches < $needCartouches) {
                    $msg = "Stock insuffisant pour {$produit->nom} (demandé: {$needCartouches} cartouche(s), dispo: {$dispoCartouches} cartouche(s)).";
                    if (request()->expectsJson() || request()->is('api/*')) {
                        return response()->json(['success' => false, 'message' => $msg], 400);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $msg);
                }

                // Si carton
                if ($qteCarton > 0) {
                    $prixC = (float) ($l['prix_vente'] ?? $produit->prix_vente_conseille);
                    $totalLignes += $prixC * $qteCarton;
                    $lignesPourService[] = [
                        'produit_id' => $l['produit_id'],
                        'quantite'   => $qteCarton,
                        'prix_vente' => $prixC,
                        'unite'      => 'carton'
                    ];
                }

                // Si cartouche
                if ($qteCartouche > 0) {
                    $fallbackPrixCartouche = $produit->prix_cartouche 
                        ?: (int) ceil(($produit->prix_vente_conseille / $cartoucheParCarton) / 100) * 100;
                    $prixCart = (float) ($l['prix_cartouche'] ?? $fallbackPrixCartouche);
                    $totalLignes += $prixCart * $qteCartouche;
                    $lignesPourService[] = [
                        'produit_id' => $l['produit_id'],
                        'quantite'   => $qteCartouche,
                        'prix_vente' => $prixCart,
                        'unite'      => 'cartouche'
                    ];
                }
            }

            // Montant payé dérivé du montant remis et du total.
            // Pas à crédit (ou anonyme) : le remis doit couvrir le total.
            if (!$aCredit) {
                if ($montantRemis === null || $montantRemis < $totalLignes) {
                    $msg = 'Le montant remis doit couvrir le total de la commande.';
                    if (request()->expectsJson() || request()->is('api/*')) {
                        return response()->json(['success' => false, 'message' => $msg], 400);
                    }
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $msg);
                }
                $montantPaye = $totalLignes;
                $du = $montantRemis - $totalLignes; // monnaie à rendre
            } else {
                $montantPaye = $montantRemis !== null ? min($montantRemis, $totalLignes) : 0;
                $du = $montantRemis !== null && $montantRemis > $totalLignes ? $montantRemis - $totalLignes : null;
            }

            // Vérifier la limite de crédit du client (non bloquant)
            if (($vData['a_credit'] ?? false) && !empty($vData['client_id']) && !$request->boolean('ignore_credit_warning')) {
                $client = Client::find($vData['client_id']);
                if ($client && $client->limite_credit > 0) {
                    $dettesEnCours = $client->totalDettesEnCours();
                    $montantRestant = $totalLignes - $montantPaye;
                    if ($dettesEnCours + $montantRestant > $client->limite_credit) {
                        $disponible = max(0, $client->limite_credit - $dettesEnCours);
                        $msg = "ATTENTION : Ce client dépasse sa limite de crédit !\n"
                            . "Dettes actuelles : " . number_format($dettesEnCours, 0, ',', ' ') . " FCFA\n"
                            . "Limite : " . number_format($client->limite_credit, 0, ',', ' ') . " FCFA\n"
                            . "Crédit disponible : " . number_format($disponible, 0, ',', ' ') . " FCFA.\n"
                            . "Voulez-vous continuer la vente ?";
                        if (request()->expectsJson() || request()->is('api/*')) {
                            return response()->json([
                                'credit_warning' => true,
                                'message' => $msg,
                                'confirm_label' => 'Oui, continuer',
                                'cancel_label' => 'Non, annuler',
                            ], 200);
                        }
                        return redirect()->back()
                            ->withInput()
                            ->with('warning', $msg);
                    }
                }
            }

            $dateEcheance = $vData['date_echeance'] ?? null;
            if (empty($dateEcheance)) $dateEcheance = null;

            $data = [
                'tenant_id'      => $user->tenant_id,
                'magasin_id'     => $magasinId,
                'user_id'        => $user->id,
                'client_id'      => $vData['client_id'] ?? null,
                'montant_paye'   => $montantPaye,
                'montant_remis'  => $montantRemis ?? null,
                'du'             => $du ?? null,
                'date_echeance'  => $dateEcheance,
                'date_vente'     => now(),
            ];

            $saved[] = $this->venteService->creer($data, $lignesPourService);
        }

        if ($saveOne !== null && count($saved) === 1) {
            // Flash the remaining unsaved ventes back to session
            $unsaved = [];
            foreach ($request->ventes as $i => $v) {
                if (!in_array((int) $i, $indices)) {
                    $unsaved[$i] = $v;
                }
            }
            if (!empty($unsaved)) {
                $request->session()->flash('unsaved_ventes', $unsaved);
            }
            return $this->smartResponse('ventes.create', 'Vente enregistrée. Continuez avec les autres clients.');
        }

        $msg = count($saved) > 1
            ? count($saved) . ' ventes enregistrées avec succès.'
            : 'Vente enregistrée avec succès.';

        return $this->smartResponse('ventes.index', $msg);
    }

    public function show(Vente $vente)
    {
        $this->authorizeTenant($vente);
        $vente->load(['client', 'user', 'magasin', 'lignes.produit', 'dette']);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $vente]);
        }

        return view('ventes.show', compact('vente'));
    }

    public function edit(Vente $vente)
    {
        $this->authorizeModule('ventes');
        $this->authorizeTenant($vente);
        $vente->load(['client', 'magasin', 'lignes.produit']);

        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->get();
        $magasins = $tenant->magasins;

        // Produits avec stock pour l'autocomplete
        $selectedMagasin = $vente->magasin;
        $tousProduits = Produit::where('tenant_id', $tenant->id)->where('actif', true)->get();
        $produitsJson = collect();
        foreach ($tousProduits as $p) {
            $stock = $this->stockService->getStock($selectedMagasin->id, $p->id);
            if ($stock > 0) {
                $produitsJson->push([
                    'id'    => $p->id,
                    'nom'   => $p->nom,
                    'prix'  => (int) $p->prix_vente_conseille,
                    'stock' => $stock,
                ]);
            }
        }

        return view('ventes.edit', compact('vente', 'clients', 'magasins', 'produitsJson'));
    }

    public function update(Request $request, Vente $vente)
    {
        $this->authorizeModule('ventes');
        $this->authorizeTenant($vente);

        $request->validate([
            'client_id'      => 'nullable|exists:clients,id',
            'magasin_id'     => 'nullable|exists:magasins,id',
            'montant_paye'   => 'nullable|numeric|min:0',
            'montant_remis'  => 'nullable|numeric|min:0',
            'new_lignes'            => 'nullable|array',
            'new_lignes.*.produit_id' => 'required_with:new_lignes|exists:produits,id',
            'new_lignes.*.quantite'   => 'required_with:new_lignes|integer|min:1',
            'new_lignes.*.prix_vente' => 'required_with:new_lignes|numeric|min:0',
            'new_lignes.*.unite'      => 'nullable|in:carton,cartouche',
            'lignes_supprimees'       => 'nullable|array',
            'lignes_supprimees.*'     => 'integer|exists:vente_lignes,id',
        ]);

        // Supprimer les lignes retirées (restituer le stock)
        if ($request->lignes_supprimees) {
            $aSupprimer = $vente->lignes()->whereIn('id', $request->lignes_supprimees)->get();
            foreach ($aSupprimer as $ligne) {
                StockMouvement::create([
                    'tenant_id'      => $vente->tenant_id,
                    'magasin_id'     => $vente->magasin_id,
                    'produit_id'     => $ligne->produit_id,
                    'user_id'        => Auth::id(),
                    'type'           => 'entree_ajustement',
                    'quantite'       => (int) $ligne->quantite,
                    'cout_unitaire'  => (float) $ligne->prix_vente,
                    'reference_type' => Vente::class,
                    'reference_id'   => $vente->id,
                    'note'           => "Suppression ligne modification vente {$vente->reference}",
                ]);
            }
            $vente->lignes()->whereIn('id', $request->lignes_supprimees)->delete();
        }

        // Mettre à jour les lignes existantes modifiées
        if ($request->lignes_existantes) {
            foreach ($request->lignes_existantes as $ligneId => $data) {
                $ligne = $vente->lignes()->find($ligneId);
                if ($ligne) {
                    $diffQte = $data['quantite'] - $ligne->quantite;
                    $ligne->update([
                        'quantite'    => $data['quantite'],
                        'prix_vente'  => (float) $data['prix_vente'],
                        'total_ligne' => (float) $data['quantite'] * (float) $data['prix_vente'],
                    ]);
                    // Ajuster le stock si la quantité a changé
                    if ($diffQte != 0) {
                        StockMouvement::create([
                            'tenant_id'      => $vente->tenant_id,
                            'magasin_id'     => $vente->magasin_id,
                            'produit_id'     => $ligne->produit_id,
                            'user_id'        => Auth::id(),
                            'type'           => $diffQte > 0 ? 'sortie_vente' : 'entree_ajustement',
                            'quantite'       => abs($diffQte),
                            'cout_unitaire'  => (float) $data['prix_vente'],
                            'reference_type' => Vente::class,
                            'reference_id'   => $vente->id,
                            'note'           => "Ajustement modification vente {$vente->reference}",
                        ]);
                    }
                }
            }
        }

        $montantTotal = $vente->lignes()->sum('total_ligne');
        $totalAjoute = 0;
        if ($request->new_lignes) {
            $magasinId = $vente->magasin_id;
            foreach ($request->new_lignes as $l) {
                $produit = Produit::find($l['produit_id']);
                $cartoucheParCarton = max(1, (int) ($produit->cartouche_par_carton ?? 1));
                $qteCartonsNecessaires = ($l['unite'] ?? 'carton') === 'cartouche'
                    ? (int) ceil((int) $l['quantite'] / $cartoucheParCarton)
                    : (int) $l['quantite'];
                $stock = $this->stockService->getStock($magasinId, $l['produit_id']);
                if ($stock < $qteCartonsNecessaires) {
                    if (request()->expectsJson() || request()->is('api/*')) {
                        return response()->json(['success' => false, 'message' => "Stock insuffisant pour {$produit->nom}."], 400);
                    }
                    return redirect()->back()->withInput()->with('error', "Stock insuffisant pour {$produit->nom}.");
                }

                $totalLigne = (float) $l['prix_vente'] * $l['quantite'];
                $totalAjoute += $totalLigne;

                $ligne = $vente->lignes()->create([
                    'produit_id'     => $l['produit_id'],
                    'quantite'       => $l['quantite'],
                    'unite'          => $l['unite'] ?? 'carton',
                    'prix_conseille' => Produit::find($l['produit_id'])->prix_vente_conseille,
                    'prix_vente'     => (float) $l['prix_vente'],
                    'cout_unitaire'  => (float) $l['prix_vente'],
                    'total_ligne'    => $totalLigne,
                ]);

                StockMouvement::create([
                    'tenant_id'      => $vente->tenant_id,
                    'magasin_id'     => $magasinId,
                    'produit_id'     => $l['produit_id'],
                    'user_id'        => Auth::id(),
                    'type'           => 'sortie_vente',
                    'quantite'       => $qteCartonsNecessaires,
                    'cout_unitaire'  => (float) $l['prix_vente'],
                    'reference_type' => Vente::class,
                    'reference_id'   => $vente->id,
                    'note'           => "Ajouté modification vente {$vente->reference}",
                ]);
            }
        }

        $nouveauTotal = $montantTotal + $totalAjoute;
        $montantRemis = $request->montant_remis ?? null;
        $montantRemis = ($montantRemis !== null && $montantRemis !== '') ? (float) $montantRemis : null;

        // Montant payé dérivé du montant remis et du total.
        if (!$request->client_id) {
            // Client anonyme = toujours entièrement payé (pas de crédit)
            $montantPaye = $nouveauTotal;
            $du = $montantRemis !== null ? max(0, $montantRemis - $nouveauTotal) : 0;
            $montantReste = 0;
            $statut = 'paye';
        } else {
            $aCredit = $request->boolean('a_credit');
            $montantPaye = $montantRemis !== null ? min($montantRemis, $nouveauTotal) : 0;
            $du = $montantRemis !== null && $montantRemis > $nouveauTotal ? $montantRemis - $nouveauTotal : null;
            $montantReste = max(0, $nouveauTotal - $montantPaye);
            $statut = $montantReste <= 0 ? 'paye' : ($montantPaye > 0 ? 'partiel' : 'impaye');
        }

        $venteUpdate = [
            'client_id'      => $request->client_id,
            'montant_total'  => $nouveauTotal,
            'montant_paye'   => $montantPaye,
            'montant_reste'  => $montantReste,
            'montant_remis'  => $montantRemis,
            'du'             => $du,
            'statut_paiement'=> $statut,
        ];
        if ($request->has('magasin_id') && $request->magasin_id) {
            $venteUpdate['magasin_id'] = $request->magasin_id;
        }
        $vente->update($venteUpdate);

        // Mettre à jour ou créer la dette si nécessaire
        if ($montantReste > 0) {
            if ($vente->dette) {
                $vente->dette->update([
                    'montant_initial' => $montantReste,
                    'montant_restant' => $montantReste,
                ]);
            } else {
                Dette::create([
                    'tenant_id'       => $vente->tenant_id,
                    'client_id'       => $request->client_id ?: null,
                    'vente_id'        => $vente->id,
                    'montant_initial' => $montantReste,
                    'montant_paye'    => 0,
                    'montant_restant' => $montantReste,
                    'statut'          => $montantPaye > 0 ? 'partiel' : 'en_cours',
                    'notes'           => $request->client_id ? null : 'Client anonyme',
                ]);
            }
        } elseif ($montantReste <= 0 && $vente->dette) {
            $vente->dette->delete();
        }

        return $this->smartResponse(route('ventes.show', $vente), 'Vente mise à jour.');
    }

    private function authorizeTenant(Vente $vente)
    {
        if ($vente->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }
    }
}
