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

        // API / mobile → retourner JSON au lieu d'une redirection HTTP
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($user->isSuperAdmin()) {
                return response()->json(['success' => false, 'role' => 'super_admin', 'message' => 'Tableau de bord super admin non disponible via API.'], 403);
            }
            if ($user->role === 'prestataire') {
                return response()->json(['success' => false, 'role' => 'prestataire', 'message' => 'Tableau de bord prestataire non disponible via API.'], 403);
            }
        } else {
            if ($user->isSuperAdmin()) {
                return redirect()->route('tenants.index');
            }
            if ($user->role === 'prestataire') {
                return redirect()->route('prestataire.dashboard');
            }
        }

        $tenant = $user->tenant;

        $date = $request->date ?: today()->format('Y-m-d');
        if ($request->date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = today()->format('Y-m-d');
        }

        // ─── Période (Jour / Semaine / Mois / Année) ───
        $periode = $request->periode ?: 'jour';
        if (!in_array($periode, ['jour', 'semaine', 'mois', 'annee'])) {
            $periode = 'jour';
        }
        $base = \Carbon\Carbon::parse($date);
        switch ($periode) {
            case 'semaine':
                $start = $base->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $end   = $base->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $periodeLabel = "Semaine du " . $start->fr('d F') . " au " . $end->fr('d F Y');
                break;
            case 'mois':
                $start = $base->copy()->startOfMonth();
                $end   = $base->copy()->endOfMonth();
                $periodeLabel = "Mois de " . $base->fr('F Y');
                break;
            case 'annee':
                $start = $base->copy()->startOfYear();
                $end   = $base->copy()->endOfYear();
                $periodeLabel = "Année " . $base->year;
                break;
            case 'jour':
            default:
                $start = $base->copy()->startOfDay();
                $end   = $base->copy()->endOfDay();
                $periodeLabel = $base->isToday() ? "Aujourd'hui" : "Le " . $base->fr('d F Y');
                $periode = 'jour';
                break;
        }

        // Agrégats de la période sélectionnée
        $encaissePeriode = (float) Vente::where('tenant_id', $tenant->id)
            ->whereBetween('date_vente', [$start, $end])
            ->sum('montant_paye');
        $caPeriode = (float) Vente::where('tenant_id', $tenant->id)
            ->whereBetween('date_vente', [$start, $end])
            ->sum('montant_total');
        $depensePeriode = (float) DepenseJournaliere::where('tenant_id', $tenant->id)
            ->whereBetween('date_depense', [$start, $end])
            ->sum('montant');
        $creancesPeriode = max(0, $caPeriode - $encaissePeriode);

        // Encaissements du jour (argent réellement reçu sur les ventes)
        $ventesJour = Vente::where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->sum('montant_paye');

        // Chiffre d'affaires du jour (total des ventes réalisées, payées ou non)
        $caJour = Vente::where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->sum('montant_total');

        // Encaissements du mois (filtré par la date sélectionnée)
        $ventesMois = Vente::where('tenant_id', $tenant->id)
            ->whereMonth('date_vente', \Carbon\Carbon::parse($date)->month)
            ->whereYear('date_vente', \Carbon\Carbon::parse($date)->year)
            ->sum('montant_paye');

        // Chiffre d'affaires du mois (total des ventes réalisées)
        $caMois = Vente::where('tenant_id', $tenant->id)
            ->whereMonth('date_vente', \Carbon\Carbon::parse($date)->month)
            ->whereYear('date_vente', \Carbon\Carbon::parse($date)->year)
            ->sum('montant_total');

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

        // Créances = ventes réalisées mais non encore encaissées
        $creancesJour = max(0, (float) $caJour - (float) $ventesJour);
        $creancesMois = max(0, (float) $caMois - (float) $ventesMois);

        // Résultat provisoire du mois = CA − dépenses − charges fixes mensuelles (loyers + salaires)
        $revenuNetMois = (float) $caMois - (float) $depenseMois - $totalLoyerMois - $totalSalairesMois;

        // Statistiques par personne (CA du jour, ventes réalisées)
        $statsParPersonne = \DB::table('ventes')
            ->where('tenant_id', $tenant->id)
            ->whereDate('date_vente', $date)
            ->selectRaw('user_id, SUM(montant_total) as total_ca')
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

        $isSqlite = \DB::connection()->getDriverName() === 'sqlite';

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
            ->orderByRaw($isSqlite ? 'date_echeance IS NULL ASC, date_echeance ASC' : 'ISNULL(date_echeance) ASC, date_echeance ASC')
            ->limit(10)
            ->get();

        // Produits sous seuil d'alerte (par magasin : alerte si bas dans un magasin quelconque)
        $produits = Produit::where('tenant_id', $tenant->id)->get();
        $magasinIds = $tenant->magasins->pluck('id');

        $mouvementsParMagasin = collect();
        if ($magasinIds->isNotEmpty()) {
            $mouvementsParMagasin = StockMouvement::whereIn('magasin_id', $magasinIds)
                ->select('produit_id', 'magasin_id')
                ->selectRaw("SUM(CASE
                    WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite
                    WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite
                    ELSE 0 END) as total")
                ->groupBy('produit_id', 'magasin_id')
                ->get()
                ->groupBy('produit_id');
        }

        $stockAlertes = [];
        foreach ($produits as $produit) {
            $seuil = (int) ($produit->seuil_alerte ?? 0);
            $minStock = null;
            $enAlerte = false;
            foreach ($magasinIds as $mid) {
                $ligne = optional($mouvementsParMagasin->get($produit->id))->firstWhere('magasin_id', $mid);
                if (!$ligne) continue; // produit non stocké dans ce magasin : on ne compte pas 0
                $s = (int) $ligne->total;
                if ($minStock === null || $s < $minStock) $minStock = $s;
                if ($s <= 5 || $s <= $seuil) $enAlerte = true;
            }
            if ($enAlerte) {
                $stockAlertes[] = ['produit' => $produit, 'stock' => $minStock];
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
            ->where('id', '!=', $user->id)
            ->orderByRaw($isSqlite ? 'last_seen IS NULL ASC, last_seen DESC' : 'ISNULL(last_seen) ASC, last_seen DESC')
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
                ->filter(fn($s) => $s['stock'] <= 5 || $s['stock'] <= (int) ($s['produit']->seuil_alerte ?? 0))
                ->sortBy('stock')
                ->take(10);
        }

        // Stock total par produit (pour le Stimulateur de CA)
        $stockParProduit = $this->stock->getStockTotalParProduit();
        $stockCartouchesParProduit = $this->stock->getStockTotalCartouchesParProduit();

        $data = compact(
            'ventesJour','ventesMois','nbVentesJour',
            'depenseJour','depenseMois','caJour','caMois','creancesJour','creancesMois','statsParPersonne',
            'depensesDuJour',
            'dettePaiementsJour',
            'totalDettes','dettesEnRetard','dettesEnRetardListe',
            'totalDettesSociete',
            'stockAlertes','dernieresVentes','topProduits','date',
            'employes',
            'totalLoyerMois','revenuNetMois','totalSalairesMois',
            'nbLivraisonsEnAttente','livraisonsDuJour',
            'stockApercu','magasinPrincipal',
            'produits','stockParProduit','stockCartouchesParProduit',
            'periode','periodeLabel','encaissePeriode','caPeriode','depensePeriode','creancesPeriode'
        );

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return view('dashboard', array_merge($data, compact('tenant')));
    }

    public function storeDepense(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Aucune société associée à ce compte.'], 403);
            }
            return back()->with('error', 'Aucune société associée.');
        }

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
