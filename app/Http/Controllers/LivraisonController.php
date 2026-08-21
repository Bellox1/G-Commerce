<?php

namespace App\Http\Controllers;

use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivraisonController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeModule('livraisons');
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
            ->with(['client', 'user', 'magasin', 'livreur']);

        // Filtre par période (date)
        $applyPeriode($query);

        // Filtre par statut (n'impacte QUE la liste, pas les stats)
        if ($request->filled('statut')) {
            $query->where('statut_livraison', $request->statut);
        }

        $ventes = $query->latest('date_vente')->paginate(15);

        // Stats globales (filtrées par période, mais indépendantes du statut)
        $allQuery = Vente::where('tenant_id', $tenant->id);
        $applyPeriode($allQuery);

        $totalMontant = (clone $allQuery)->sum('montant_total');
        $totalPaye    = (clone $allQuery)->sum('montant_paye');
        $nbLivraisons = (clone $allQuery)->count();

        // Compteurs par statut (sur la période sélectionnée, sans filtre de statut)
        $nbParStatutRaw = (clone $allQuery)
            ->selectRaw("statut_livraison, COUNT(*) as nb")
            ->groupBy('statut_livraison')
            ->get()
            ->pluck('nb', 'statut_livraison')
            ->toArray();
        $nbParStatut = [
            'en_attente' => $nbParStatutRaw['en_attente'] ?? 0,
            'livre'      => $nbParStatutRaw['livre'] ?? 0,
            'probleme'   => $nbParStatutRaw['probleme'] ?? 0,
        ];

        if (request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $ventes,
                'stats' => compact('totalMontant', 'totalPaye', 'nbLivraisons', 'nbParStatut'),
            ]);
        }

        return view('livraisons.index', compact(
            'ventes', 'totalMontant', 'totalPaye', 'nbLivraisons', 'nbParStatut',
            'periode', 'dateDebut', 'dateFin'
        ));
    }

    public function show(Request $request, $id = null)
    {
        $this->authorizeModule('livraisons');

        $id = $id ?? $request->route('vente') ?? $request->route('livraison');
        $vente = Vente::where('tenant_id', Auth::user()->tenant_id)->findOrFail($id);
        $vente->load(['client', 'user', 'magasin', 'lignes.produit', 'livreur']);

        if (request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $vente]);
        }

        return view('livraisons.show', compact('vente'));
    }

    public function updateStatut(Request $request, Vente $vente)
    {
        $this->authorizeModule('livraisons');
        if ($vente->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }

        $request->validate([
            'statut_livraison' => 'required|in:en_attente,livre,probleme',
            'note_livraison' => 'nullable|string|max:1000',
        ]);

        if ($request->statut_livraison === 'en_attente'
            && in_array($vente->statut_livraison, ['livre', 'probleme'])) {
            abort(422, 'Impossible de repasser une livraison déjà livrée ou en problème à « en attente ».');
        }

        $vente->update([
            'statut_livraison' => $request->statut_livraison,
            'livreur_id' => Auth::id(),
            'date_livraison' => $request->statut_livraison === 'livre' ? now() : null,
            'note_livraison' => $request->note_livraison,
        ]);

        if (request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Statut de livraison mis à jour avec succès.',
                'redirect' => route('livraisons.index'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Statut de livraison mis à jour avec succès.');
    }
}
