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

        $query = Vente::where('tenant_id', $tenant->id)
            ->with(['client', 'user', 'magasin', 'livreur']);

        // Filtre par statut (en_attente ou livre)
        if ($request->filled('statut')) {
            $query->where('statut_livraison', $request->statut);
        }

        $ventes = $query->latest('date_vente')->paginate(15);

        return view('livraisons.index', compact('ventes'));
    }

    public function show(Vente $vente)
    {
        $this->authorizeModule('livraisons');
        if ($vente->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }

        $vente->load(['client', 'user', 'magasin', 'lignes.produit', 'livreur']);

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

        $vente->update([
            'statut_livraison' => $request->statut_livraison,
            'livreur_id' => Auth::id(),
            'date_livraison' => $request->statut_livraison === 'livre' ? now() : null,
            'note_livraison' => $request->note_livraison,
        ]);

        return redirect()->back()->with('success', 'Statut de livraison mis à jour avec succès.');
    }
}
