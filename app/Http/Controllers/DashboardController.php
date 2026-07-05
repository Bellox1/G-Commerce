<?php

namespace App\Http\Controllers;

use App\Models\Dette;
use App\Models\DettePaiement;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Vente;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $user   = Auth::user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('tenants.index');
        }
        $tenant = $user->tenant;

        $date = $request->date ?: today()->format('Y-m-d');
        if ($request->date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = today()->format('Y-m-d');
        }

        // Ventes du jour filtré
        $ventesJour = Vente::where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->sum('montant_paye');

        // Ventes du mois (filtré par la date sélectionnée)
        $ventesMois = Vente::where('tenant_id', $tenant->id)
            ->whereMonth('date_vente', \Carbon\Carbon::parse($date)->month)
            ->whereYear('date_vente', \Carbon\Carbon::parse($date)->year)
            ->sum('montant_paye');

        // Nombre de ventes du jour filtré
        $nbVentesJour = Vente::where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->count();

        // Encaissement dette du jour filtré
        $dettePaiementsJour = (int) DettePaiement::whereHas('dette', fn($q) => $q->where('tenant_id', $tenant->id))
            ->whereDate('created_at', $date)
            ->sum('montant');

        // Total dettes actives (non filtré)
        $totalDettes = Dette::where('tenant_id', $tenant->id)
            ->whereIn('statut', ['en_cours', 'partiel', 'en_retard'])
            ->sum('montant_restant');

        // Dettes en retard (non filtré)
        $dettesEnRetard = Dette::where('tenant_id', $tenant->id)
            ->where('statut', 'en_retard')
            ->orWhere(function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)
                  ->whereNotNull('date_echeance')
                  ->where('date_echeance', '<', today())
                  ->whereNotIn('statut', ['solde']);
            })
            ->count();

        // Produits sous seuil d'alerte (non filtré)
        $produits = Produit::where('tenant_id', $tenant->id)->get();
        $stockAlertes = [];
        $magasinIds = $tenant->magasins->pluck('id');

        foreach ($produits as $produit) {
            $mouvements = (int) StockMouvement::whereIn('magasin_id', $magasinIds)
                ->where('produit_id', $produit->id)
                ->selectRaw("SUM(CASE
                    WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite
                    WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite
                    ELSE 0 END) as total")
                ->value('total') ?? 0;

            $stockTotal = (int) $produit->stock + $mouvements;

            if ($stockTotal <= $produit->seuil_alerte) {
                $stockAlertes[] = ['produit' => $produit, 'stock' => $stockTotal];
            }
        }

        // Dernières ventes du jour filtré
        $dernieresVentes = Vente::where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->with(['client', 'user', 'magasin'])
            ->latest('date_vente')
            ->limit(5)
            ->get();

        // Produits les plus vendus du mois (filtré par la date sélectionnée)
        $topProduits = \DB::table('vente_lignes')
            ->join('ventes', 'ventes.id', '=', 'vente_lignes.vente_id')
            ->join('produits', 'produits.id', '=', 'vente_lignes.produit_id')
            ->where('ventes.tenant_id', $tenant->id)
            ->whereMonth('ventes.date_vente', \Carbon\Carbon::parse($date)->month)
            ->select('produits.nom', \DB::raw('SUM(vente_lignes.quantite) as total_vendu'))
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('total_vendu')
            ->limit(5)
            ->get();

        // Collaborateurs de la société (pour le statut de connexion)
        $employes = User::where('tenant_id', $tenant->id)
            ->where('id', '!=', $user->id) // On exclut l'utilisateur connecté actuel si on veut, ou on le garde. Gardons-les tous pour que l'admin puisse s'y voir aussi.
            ->orderByRaw('last_seen DESC NULLS LAST')
            ->get();

        return view('dashboard', compact(
            'ventesJour','ventesMois','nbVentesJour',
            'dettePaiementsJour',
            'totalDettes','dettesEnRetard',
            'stockAlertes','dernieresVentes','topProduits','tenant','date',
            'employes'
        ));
    }
}
