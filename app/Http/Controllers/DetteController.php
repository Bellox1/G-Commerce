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

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $dettes, 'clients' => $clients]);
        }

        return view('dettes.index', compact('dettes', 'clients'));
    }

    public function show(Dette $dette)
    {
        $this->authorizeModule('dettes');
        $this->authorizeTenant($dette);
        $dette->load(['client', 'vente.lignes.produit', 'paiements.user']);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $dette]);
        }

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
            'mode_paiement' => 'nullable|in:especes,mobile_money,cheque',
            'note' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        $dette->enregistrerPaiement(
            $request->montant,
            $request->input('mode_paiement', 'especes'),
            $user->id,
            $request->input('note')
        );

        if ($request->filled('echeance_option')) {
            $option = $request->input('echeance_option');
            $custom = $request->input('date_echeance_custom');
            $map = [
                'today'          => \Carbon\Carbon::today(),
                'tomorrow'       => \Carbon\Carbon::tomorrow(),
                'after_tomorrow' => \Carbon\Carbon::today()->addDays(2),
                '6_days'         => \Carbon\Carbon::today()->addDays(6),
                '2_weeks'        => \Carbon\Carbon::today()->addWeeks(2),
                '1_month'        => \Carbon\Carbon::today()->addMonth(),
            ];
            $newEcheance = null;
            if ($option === 'custom' && $custom) {
                $newEcheance = \Carbon\Carbon::parse($custom);
            } elseif (isset($map[$option])) {
                $newEcheance = $map[$option];
            }
            if ($newEcheance) {
                $dette->date_echeance = $newEcheance;
                $dette->save();
            }
        }

        return $this->smartResponse(route('dettes.show', $dette), 'Versement de ' . number_format($request->montant, 0, ',', ' ') . ' FCFA enregistré avec succès.');
    }

    /**
     * Met à jour la date d'échéance d'une dette
     */
    public function updateEcheance(Request $request, Dette $dette)
    {
        $this->authorizeTenant($dette);

        $option = $request->input('echeance_option');
        $custom = $request->input('date_echeance_custom');

        $map = [
            'today'          => 0,
            'tomorrow'       => 1,
            'after_tomorrow' => 2,
            '6_days'         => 6,
            '2_weeks'        => 14,
            '1_month'        => 30,
        ];

        if ($option === 'custom') {
            if (empty($custom)) {
                return $this->echeanceAbort($dette, 'Veuillez choisir une date personnalisée.');
            }
            $dette->date_echeance = $custom;
        } elseif (isset($map[$option])) {
            $dette->date_echeance = now()->addDays($map[$option]);
        } else {
            // Aucune option valide sélectionnée : on ne modifie pas la date
            return $this->echeanceAbort($dette, 'Veuillez sélectionner une option d\'échéance.');
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

        return $this->smartResponse(route('dettes.show', $dette), 'Date d\'échéance mise à jour.');
    }

    public function destroy(Dette $dette)
    {
        $this->authorizeModule('dettes');
        $this->authorizeTenant($dette);

        \Illuminate\Support\Facades\DB::transaction(function () use ($dette) {
            $dette->paiements()->delete();
            $dette->delete();
        });

        return $this->smartResponse(route('dettes.index'), 'Créance supprimée avec succès.');
    }

    private function authorizeTenant(Dette $dette)
    {
        $user = Auth::user();
        if (!$user || $user->isSuperAdmin() || $user->hasRole('prestataire')) return;

        $tenantId = $user->tenant_id 
            ?? $user->tenant?->id 
            ?? optional($user->magasin)->tenant_id;

        if ($tenantId && (int)$dette->tenant_id !== (int)$tenantId) {
            abort(403, 'Action non autorisée.');
        }
    }

    private function echeanceAbort(Dette $dette, string $message)
    {
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return redirect()->route('dettes.show', $dette)->with('error', $message);
    }
}
