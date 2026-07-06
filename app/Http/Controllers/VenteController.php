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

    public function index()
    {
        $tenant = Auth::user()->tenant;
        $ventes = Vente::where('tenant_id', $tenant->id)
            ->with(['client', 'user', 'magasin', 'dette'])
            ->latest()
            ->paginate(15);

        return view('ventes.index', compact('ventes'));
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

        return view('ventes.create', compact('clients', 'clientsJson', 'produits', 'produitsJson', 'selectedMagasin'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('ventes');
        $request->validate([
            'magasin_id'                  => 'required|exists:magasins,id',
            'ventes'                      => 'required|array|min:1',
            'ventes.*.client_id'          => 'nullable|exists:clients,id',
            'ventes.*.montant_paye'       => 'required|numeric|min:0',
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

            $montantPaye = (float) $vData['montant_paye'];

            $lignesPourService = [];
            $totalLignes = 0;

            foreach ($vData['lignes'] as $l) {
                $produit = Produit::find($l['produit_id']);
                $qteCarton = (int) ($l['quantite'] ?? 0);
                $qteCartouche = (int) ($l['quantite_cartouche'] ?? 0);

                if ($qteCarton === 0 && $qteCartouche === 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "La quantité doit être supérieure à zéro pour {$produit->nom}.");
                }

                // Vérifier les stocks globalement
                $stock = $this->stockService->getStock($magasinId, $l['produit_id']);
                $cartoucheParCarton = max(1, (int) ($produit->cartouche_par_carton ?? 1));
                $cartonsNecessaires = $qteCarton + (int) ceil($qteCartouche / $cartoucheParCarton);

                if ($stock < $cartonsNecessaires) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Stock insuffisant pour {$produit->nom} (demandé: {$cartonsNecessaires} ctn, dispo: {$stock} ctn).");
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

            // Not "À crédit" = automatically fully paid
            if (!($vData['a_credit'] ?? false)) {
                $montantPaye = $totalLignes;
                $montantRemis = $vData['montant_remis'] ?? null;
                $montantRemis = $montantRemis !== null && $montantRemis !== '' ? (float) $montantRemis : null;
                $du = $montantRemis && $montantRemis > $totalLignes ? $montantRemis - $totalLignes : null;
                if (!$du) $montantRemis = null;
            } elseif ($montantPaye > $totalLignes) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Le montant payé ne peut pas dépasser le total de la commande.');
            }

            // Vérifier la limite de crédit du client
            if (($vData['a_credit'] ?? false) && !empty($vData['client_id'])) {
                $client = Client::find($vData['client_id']);
                if ($client && $client->limite_credit > 0) {
                    $dettesEnCours = $client->totalDettesEnCours();
                    $montantRestant = $totalLignes - $montantPaye;
                    if ($dettesEnCours + $montantRestant > $client->limite_credit) {
                        $disponible = max(0, $client->limite_credit - $dettesEnCours);
                        return redirect()->back()
                            ->withInput()
                            ->with('error', "Ce client a atteint sa limite de crédit. Dettes actuelles : "
                                . number_format($dettesEnCours, 0, ',', ' ')
                                . " FCFA sur "
                                . number_format($client->limite_credit, 0, ',', ' ')
                                . " FCFA. Crédit disponible : "
                                . number_format($disponible, 0, ',', ' ') . " FCFA.");
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
            return redirect()->route('ventes.create')
                ->with('success', 'Vente enregistrée. Continuez avec les autres clients.');
        }

        $msg = count($saved) > 1
            ? count($saved) . ' ventes enregistrées avec succès.'
            : 'Vente enregistrée avec succès.';

        return redirect()->route('ventes.index')->with('success', $msg);
    }

    public function show(Vente $vente)
    {
        $this->authorizeTenant($vente);
        $vente->load(['client', 'user', 'magasin', 'lignes.produit', 'dette']);

        return view('ventes.show', compact('vente'));
    }

    public function edit(Vente $vente)
    {
        $this->authorizeModule('ventes');
        $this->authorizeTenant($vente);
        $vente->load(['client', 'magasin', 'lignes.produit']);

        $tenant = Auth::user()->tenant;
        $clients = Client::where('tenant_id', $tenant->id)->get();

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

        return view('ventes.edit', compact('vente', 'clients', 'produitsJson'));
    }

    public function update(Request $request, Vente $vente)
    {
        $this->authorizeModule('ventes');
        $this->authorizeTenant($vente);

        $request->validate([
            'client_id'      => 'nullable|exists:clients,id',
            'montant_paye'   => 'required|numeric|min:0',
            'montant_remis'  => 'nullable|numeric|min:0',
            'new_lignes'            => 'nullable|array',
            'new_lignes.*.produit_id' => 'required_with:new_lignes|exists:produits,id',
            'new_lignes.*.quantite'   => 'required_with:new_lignes|integer|min:1',
            'new_lignes.*.prix_vente' => 'required_with:new_lignes|numeric|min:0',
        ]);

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
                $stock = $this->stockService->getStock($magasinId, $l['produit_id']);
                if ($stock < $l['quantite']) {
                    $produit = Produit::find($l['produit_id']);
                    return redirect()->back()->withInput()->with('error', "Stock insuffisant pour {$produit->nom}.");
                }

                $totalLigne = (float) $l['prix_vente'] * $l['quantite'];
                $totalAjoute += $totalLigne;

                $ligne = $vente->lignes()->create([
                    'produit_id'     => $l['produit_id'],
                    'quantite'       => $l['quantite'],
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
                    'quantite'       => $l['quantite'],
                    'cout_unitaire'  => (float) $l['prix_vente'],
                    'reference_type' => Vente::class,
                    'reference_id'   => $vente->id,
                    'note'           => "Ajouté modification vente {$vente->reference}",
                ]);
            }
        }

        $nouveauTotal = $montantTotal + $totalAjoute;
        $montantPaye = (float) $request->montant_paye;
        // Anonymous client = automatically fully paid
        if (!$request->client_id) {
            $montantPaye = $nouveauTotal;
            $montantReste = 0;
            $statut = 'paye';
        } else {
            if ($montantPaye > $nouveauTotal) $montantPaye = $nouveauTotal;
            $montantReste = max(0, $nouveauTotal - $montantPaye);
            $statut = $montantReste <= 0 ? 'paye' : ($montantPaye > 0 ? 'partiel' : 'impaye');
        }

        $montantRemis = $request->montant_remis ?? null;
        $montantRemis = $montantRemis !== null && $montantRemis !== '' ? (float) $montantRemis : null;
        $du = $montantRemis && $montantRemis > $nouveauTotal ? $montantRemis - $nouveauTotal : null;
        if (!$du) $montantRemis = null;

        $vente->update([
            'client_id'      => $request->client_id,
            'montant_total'  => $nouveauTotal,
            'montant_paye'   => $montantPaye,
            'montant_reste'  => $montantReste,
            'montant_remis'  => $montantRemis,
            'du'             => $du,
            'statut_paiement'=> $statut,
        ]);

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

        return redirect()->route('ventes.show', $vente)
            ->with('success', 'Vente mise à jour.');
    }

    public function convertirDette(Request $request, Vente $vente)
    {
        $this->authorizeModule('ventes');
        $this->authorizeTenant($vente);

        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
        ]);

        if ($vente->dette) {
            return redirect()->back()->with('error', 'Cette vente est déjà liée à une dette.');
        }

        $montantDu = $vente->montant_reste > 0 ? $vente->montant_reste : $vente->montant_total;

        $vente->update([
            'montant_paye'    => $vente->montant_total - $montantDu,
            'montant_reste'   => $montantDu,
            'statut_paiement' => $montantDu >= $vente->montant_total ? 'impaye' : 'partiel',
            'client_id'       => $request->client_id ?: $vente->client_id,
        ]);

        Dette::create([
            'tenant_id'       => $vente->tenant_id,
            'client_id'       => $request->client_id ?: null,
            'vente_id'        => $vente->id,
            'montant_initial' => $montantDu,
            'montant_paye'    => 0,
            'montant_restant' => $montantDu,
            'statut'          => 'en_cours',
            'notes'           => $request->client_id ? null : 'Client anonyme',
        ]);

        return redirect()->route('ventes.show', $vente)
            ->with('success', 'Vente convertie en dette avec succès.');
    }

    private function authorizeTenant(Vente $vente)
    {
        if ($vente->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }
    }
}
