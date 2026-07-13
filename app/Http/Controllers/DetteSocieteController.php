<?php

namespace App\Http\Controllers;

use App\Models\Arrivage;
use App\Models\DetteSociete;
use App\Models\DetteSocietePaiement;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetteSocieteController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;
        $query = DetteSociete::where('tenant_id', $tenant->id)
            ->with(['fournisseur', 'arrivage'])
            ->latest('date_dette');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            $query->where('statut', '!=', 'solde');
        }

        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        $dettes = $query->paginate(15);
        $fournisseurs = Fournisseur::where('tenant_id', $tenant->id)->get();
        $totalDettes = DetteSociete::where('tenant_id', $tenant->id)->where('statut', '!=', 'solde')->sum(\DB::raw('montant - montant_paye'));
        $totalSolde = DetteSociete::where('tenant_id', $tenant->id)->where('statut', 'solde')->sum('montant');

        $arrivages = Arrivage::where('tenant_id', $tenant->id)
            ->whereDoesntHave('detteSociete')
            ->latest()
            ->get();

        return view('dettes-societe.index', compact('dettes', 'fournisseurs', 'totalDettes', 'totalSolde', 'arrivages'));
    }

    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $data = $request->validate([
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'arrivage_id'    => 'nullable|exists:arrivages,id',
            'montant'        => 'required|numeric|min:1',
            'description'    => 'nullable|string|max:255',
            'date_dette'     => 'required|date',
        ]);

        $data['tenant_id'] = $tenant->id;
        $data['statut'] = 'en_cours';
        $data['montant_paye'] = 0;

        $dette = DetteSociete::create($data);

        return $this->smartResponse(route('dettes-societe.index'), 'Dette société enregistrée avec succès.');
    }

    public function enregistrerPaiement(Request $request, DetteSociete $dette)
    {
        if ($dette->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'montant'          => 'required|numeric|min:1',
            'date_paiement'    => 'required|date',
            'mode_paiement'    => 'required|string',
        ]);

        $reste = $dette->montant - $dette->montant_paye;
        if ($request->montant > $reste) {
            return back()->withErrors(['montant' => 'Le montant ne peut pas dépasser ' . number_format($reste, 0, ',', ' ') . ' FCFA.']);
        }

        DetteSocietePaiement::create([
            'dette_societe_id' => $dette->id,
            'user_id'          => Auth::id(),
            'montant'          => $request->montant,
            'date_paiement'    => $request->date_paiement,
            'mode_paiement'    => $request->mode_paiement,
            'notes'            => $request->notes,
        ]);

        $dette->montant_paye += $request->montant;
        $dette->updateStatut();
        $dette->save();

        return $this->smartResponse(route('dettes-societe.index'), 'Paiement de ' . number_format($request->montant, 0, ',', ' ') . ' FCFA enregistré.');
    }

    public function show(DetteSociete $dette)
    {
        if ($dette->tenant_id !== Auth::user()->tenant_id) abort(403);
        $dette->load(['fournisseur', 'arrivage', 'paiements.user']);

        return view('dettes-societe.show', compact('dette'));
    }

    public function destroy(DetteSociete $dette)
    {
        if ($dette->tenant_id !== Auth::user()->tenant_id) abort(403);
        $dette->delete();

        return $this->smartResponse(route('dettes-societe.index'), 'Dette supprimée.');
    }
}
