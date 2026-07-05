<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use App\Models\DettePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeModule('dettes');
        $tenant = Auth::user()->tenant;
        
        $query = Dette::where('tenant_id', $tenant->id)
            ->with(['client', 'vente'])
            ->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            // Par défaut on n'affiche pas les dettes complètement soldées
            $query->where('statut', '!=', 'solde');
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $dettes = $query->paginate(15);
        $clients = \App\Models\Client::where('tenant_id', $tenant->id)->get();

        return view('dettes.index', compact('dettes', 'clients'));
    }

    public function show(Dette $dette)
    {
        $this->authorizeModule('dettes');
        $this->authorizeTenant($dette);
        $dette->load(['client', 'vente.lignes.produit', 'paiements.user']);

        return view('dettes.show', compact('dette'));
    }

    /**
     * Enregistre un versement sur la dette du client.
     */
    public function enregistrerPaiement(Request $request, Dette $dette)
    {
        $this->authorizeTenant($dette);

        $request->validate([
            'montant' => 'required|numeric|min:1|max:' . $dette->montant_restant,
        ]);

        $user = Auth::user();

        $dette->enregistrerPaiement($request->montant, 'especes', $user->id);

        return redirect()->route('dettes.show', $dette)
            ->with('success', 'Versement de ' . number_format($request->montant, 0, ',', ' ') . ' FCFA enregistré avec succès.');
    }

    private function authorizeTenant(Dette $dette)
    {
        if ($dette->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }
    }
}
