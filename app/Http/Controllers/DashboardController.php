<?php

namespace App\Http\Controllers;

use App\Models\DepenseJournaliere;
use App\Models\Dette;
use App\Models\DettePaiement;
use App\Models\DetteSociete;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Vente;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // Dépenses du jour
        $depenseJour = DepenseJournaliere::where('tenant_id', $tenant->id)
            ->whereDate('date_depense', $date)
            ->sum('montant');

        // Dépenses du mois
        $depenseMois = DepenseJournaliere::where('tenant_id', $tenant->id)
            ->whereMonth('date_depense', \Carbon\Carbon::parse($date)->month)
            ->whereYear('date_depense', \Carbon\Carbon::parse($date)->year)
            ->sum('montant');

        // Total loyers des magasins (mensuel)
        $totalLoyerMois = (float) Magasin::where('tenant_id', $tenant->id)->sum('loyer');

        // Total salaires des employés actifs (mensuel)
        $totalSalairesMois = (float) User::where('tenant_id', $tenant->id)
            ->where('actif', true)
            ->whereNotNull('salaire')
            ->sum('salaire');

        // Chiffre d'affaire net (encaissements - dépenses)
        $caJour = $ventesJour - $depenseJour;
        $caMois = $ventesMois - $depenseMois;

        // Revenu net mensuel (ventes encaissées - dépenses - loyers - salaires)
        $revenuNetMois = $caMois - $totalLoyerMois - $totalSalairesMois;

        // Statistiques par personne (ventes du jour)
        $statsParPersonne = \DB::table('ventes')
            ->where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->selectRaw('user_id, SUM(montant_paye) as total_ventes')
            ->groupBy('user_id')
            ->get()
            ->map(function ($item) use ($tenant) {
                $item->user = User::find($item->user_id);
                return $item;
            });

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

        // Dettes en retard (non filtré) — avec détails
        $dettesEnRetardQuery = Dette::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->where('statut', 'en_retard')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('date_echeance')
                         ->where('date_echeance', '<', today())
                         ->whereNotIn('statut', ['solde']);
                  });
            });
        $dettesEnRetard = (clone $dettesEnRetardQuery)->count();
        $dettesEnRetardListe = (clone $dettesEnRetardQuery)
            ->with(['client', 'vente'])
            ->orderByRaw('date_echeance ASC NULLS LAST')
            ->limit(10)
            ->get();

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

        // Dépenses du jour (liste)
        $depensesDuJour = DepenseJournaliere::where('tenant_id', $tenant->id)
            ->whereDate('date_depense', $date)
            ->with('user')
            ->latest()
            ->get();

        // Livraisons
        $nbLivraisonsEnAttente = Vente::where('tenant_id', $tenant->id)
            ->where('statut_livraison', 'en_attente')
            ->count();

        $livraisonsDuJour = Vente::where('tenant_id', $tenant->id)
            ->whereDate('date_livraison', $date)
            ->count();

        // Dettes société (non filtré)
        $totalDettesSociete = DetteSociete::where('tenant_id', $tenant->id)
            ->where('statut', 'en_cours')
            ->sum(DB::raw('montant - montant_paye'));

        // Stock en temps réel (aperçu pour le 1er magasin)
        $magasins = Magasin::where('tenant_id', $tenant->id)->get();
        $stockApercu = collect();
        $magasinPrincipal = $magasins->first();
        if ($magasinPrincipal) {
            $stockParProduit = $this->stock->getStockMagasin($magasinPrincipal->id);
            $stockApercu = Produit::where('tenant_id', $tenant->id)
                ->whereIn('id', array_keys($stockParProduit))
                ->get()
                ->map(fn($p) => [
                    'produit' => $p,
                    'stock'   => $stockParProduit[$p->id] ?? 0,
                ])
                ->filter(fn($s) => $s['stock'] <= ($s['produit']->seuil_alerte ?? 0))
                ->sortBy('stock')
                ->take(10);
        }

        return view('dashboard', compact(
            'ventesJour','ventesMois','nbVentesJour',
            'depenseJour','depenseMois','caJour','caMois','statsParPersonne',
            'depensesDuJour',
            'dettePaiementsJour',
            'totalDettes','dettesEnRetard','dettesEnRetardListe',
            'totalDettesSociete',
            'stockAlertes','dernieresVentes','topProduits','tenant','date',
            'employes',
            'totalLoyerMois','revenuNetMois','totalSalairesMois',
            'nbLivraisonsEnAttente','livraisonsDuJour',
            'stockApercu','magasinPrincipal'
        ));
    }

    public function storeDepense(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $request->validate([
            'montant'      => 'required|numeric|min:1',
            'description'  => 'nullable|string|max:255',
            'audio_base64' => 'nullable|string',
        ]);

        $audioPath = null;
        if ($request->filled('audio_base64')) {
            $raw = $request->audio_base64;
            // Accept any data:audio/* or data:application/octet-stream base64
            if (preg_match('/^data:[^;]+;base64,(.+)$/', $raw, $m)) {
                $decoded = base64_decode($m[1]);
                if ($decoded !== false && strlen($decoded) > 100) {
                    $filename = 'depense_' . uniqid() . '.webm';
                    $dir = storage_path('app/public/depenses');
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    file_put_contents($dir . '/' . $filename, $decoded);
                    $audioPath = 'depenses/' . $filename;
                }
            }
        }

        DepenseJournaliere::create([
            'tenant_id'    => $tenant->id,
            'user_id'      => $user->id,
            'montant'      => $request->montant,
            'description'  => $request->description,
            'audio_path'   => $audioPath,
            'date_depense' => $request->date ?: today()->format('Y-m-d'),
        ]);

        return $this->smartResponse(route('dashboard', ['date' => $request->date]), 'Dépense enregistrée.');
    }
}
