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

    /**
     * Met à jour la date d'échéance d'une dette
     */
    public function updateEcheance(Request $request, Dette $dette)
    {
        $this->authorizeTenant($dette);

        $option = $request->input('echeance_option');
        $custom = $request->input('date_echeance_custom');

        if ($option === 'custom' && $custom) {
            $dette->date_echeance = $custom;
        } elseif ($option) {
            $map = [
                'today'          => 0,
                'tomorrow'       => 1,
                'after_tomorrow' => 2,
                '6_days'         => 6,
                '2_weeks'        => 14,
                '1_month'        => 30,
            ];
            if (isset($map[$option])) {
                $dette->date_echeance = now()->addDays($map[$option]);
            }
        } else {
            $dette->date_echeance = null;
        }

        // Mettre à jour le statut
        if ($dette->date_echeance && $dette->date_echeance->isPast() && $dette->montant_restant > 0) {
            $dette->statut = 'en_retard';
        } elseif ($dette->montant_restant <= 0) {
            $dette->statut = 'solde';
        } elseif ($dette->montant_paye > 0) {
            $dette->statut = 'partiel';
        } else {
            $dette->statut = 'en_cours';
        }

        $dette->save();

        return redirect()->route('dettes.show', $dette)
            ->with('success', 'Date d\'échéance mise à jour.');
    }

    private function authorizeTenant(Dette $dette)
    {
        if ($dette->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }
    }
}
