<?php

namespace App\Http\Controllers;

use App\Models\DepenseJournaliere;
use App\Models\Dette;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\StockMouvement;
use App\Models\Vente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalytiqueController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $tenant = $user->tenant;

        $annee         = (int) ($request->annee ?: date('Y'));
        // mois est OPTIONNEL — null = vue annuelle
        $moisFiltre    = $request->filled('mois') ? str_pad($request->mois, 2, '0', STR_PAD_LEFT) : null;
        $anneeActuelle = (int) date('Y');

        $isSqlite = \DB::connection()->getDriverName() === 'sqlite';
        $monthSql = fn($col) => $isSqlite ? "strftime('%m', {$col})" : "DATE_FORMAT({$col}, '%m')";
        $daySql   = fn($col) => $isSqlite ? "strftime('%d', {$col})" : "DATE_FORMAT({$col}, '%d')";

        // ─── Loyer mensuel de base ────────────────────────────────────────────
        $loyerMensuel = (float) Magasin::where('tenant_id', $tenant->id)->sum('loyer');

        // Nombre de mois réellement écoulés dans l'année choisie
        // (année en cours → mois courant ; année passée → 12)
        $moisEcoules = ($annee === $anneeActuelle) ? (int) date('n') : 12;

        // ─── MODE MOIS : un mois précis sélectionné ──────────────────────────
        if ($moisFiltre) {
            $moisNum = (int) $moisFiltre;

            // Ventes du mois agrégées par jour (graphe quotidien)
            $joursDansMois = cal_days_in_month(CAL_GREGORIAN, $moisNum, $annee);
            $ventesQuotidiennes = Vente::where('tenant_id', $tenant->id)
                ->whereYear('date_vente', $annee)
                ->whereMonth('date_vente', $moisNum)
                ->selectRaw("{$daySql('date_vente')} as jour, SUM(montant_total) as total")
                ->groupBy('jour')->orderBy('jour')
                ->pluck('total', 'jour');

            $joursLabels    = [];
            $ventesJourData = [];
            for ($j = 1; $j <= $joursDansMois; $j++) {
                $key = str_pad($j, 2, '0', STR_PAD_LEFT);
                $joursLabels[]    = (string) $j;
                $ventesJourData[] = (float) ($ventesQuotidiennes[$key] ?? 0);
            }

            $totalVentesMois   = array_sum($ventesJourData);
            $totalDepensesMois = (float) DepenseJournaliere::where('tenant_id', $tenant->id)
                ->whereYear('date_depense', $annee)->whereMonth('date_depense', $moisNum)
                ->sum('montant');

            // Loyer = 1 seul mois
            $loyersCumules = $loyerMensuel;
            $revenuNetMois = $totalVentesMois - $totalDepensesMois - $loyerMensuel;

            $nbVentesMois = (int) Vente::where('tenant_id', $tenant->id)
                ->whereYear('date_vente', $annee)->whereMonth('date_vente', $moisNum)->count();

            $dettesMois = (float) Dette::where('tenant_id', $tenant->id)
                ->whereYear('created_at', $annee)->whereMonth('created_at', $moisNum)
                ->sum('montant_restant');

            $nomsMois  = ['01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Jui',
                          '07'=>'Jul','08'=>'Aoû','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'];
            // Tableaux à 1 entrée → les graphes restent cohérents côté JS/mobile
            $moisLabels    = [$nomsMois[$moisFiltre]];
            $moisData      = [$totalVentesMois];
            $depensesData  = [$totalDepensesMois];
            $revenuNetData = [$revenuNetMois];
            $nbVentesData  = [$nbVentesMois];
            $dettesData    = [$dettesMois];

        } else {
            // ─── MODE ANNUEL : pas de filtre mois ─────────────────────────────

            // Ventes mensuelles (12 mois)
            $ventesMensuelles = Vente::where('tenant_id', $tenant->id)
                ->whereYear('date_vente', $annee)
                ->selectRaw("{$monthSql('date_vente')} as mois, SUM(montant_total) as total")
                ->groupBy('mois')->orderBy('mois')
                ->pluck('total', 'mois');

            $moisData = [];
            for ($m = 1; $m <= 12; $m++) {
                $key = str_pad($m, 2, '0', STR_PAD_LEFT);
                $moisData[] = (float) ($ventesMensuelles[$key] ?? 0);
            }

            // Dépenses mensuelles
            $depensesMensuelles = DepenseJournaliere::where('tenant_id', $tenant->id)
                ->whereYear('date_depense', $annee)
                ->selectRaw("{$monthSql('date_depense')} as mois, SUM(montant) as total")
                ->groupBy('mois')->orderBy('mois')
                ->pluck('total', 'mois');

            $depensesData = [];
            for ($m = 1; $m <= 12; $m++) {
                $key = str_pad($m, 2, '0', STR_PAD_LEFT);
                $depensesData[] = (float) ($depensesMensuelles[$key] ?? 0);
            }

            // Loyers cumulés : moisEcoules mois seulement (pas 12 fixe)
            $loyersCumules = $loyerMensuel * $moisEcoules;

            // Revenu net par mois
            $revenuNetData = [];
            for ($i = 0; $i < 12; $i++) {
                $revenuNetData[] = $moisData[$i] - $depensesData[$i] - $loyerMensuel;
            }

            // Nb ventes par mois
            $nbVentesParMois = Vente::where('tenant_id', $tenant->id)
                ->whereYear('date_vente', $annee)
                ->selectRaw("{$monthSql('date_vente')} as mois, COUNT(*) as total")
                ->groupBy('mois')->orderBy('mois')
                ->pluck('total', 'mois');

            $nbVentesData = [];
            for ($m = 1; $m <= 12; $m++) {
                $key = str_pad($m, 2, '0', STR_PAD_LEFT);
                $nbVentesData[] = (int) ($nbVentesParMois[$key] ?? 0);
            }

            // Dettes par mois
            $dettesCrees = Dette::where('tenant_id', $tenant->id)
                ->whereYear('created_at', $annee)
                ->selectRaw("{$monthSql('created_at')} as mois, SUM(montant_restant) as total")
                ->groupBy('mois')->orderBy('mois')
                ->pluck('total', 'mois');

            $dettesData = [];
            for ($m = 1; $m <= 12; $m++) {
                $key = str_pad($m, 2, '0', STR_PAD_LEFT);
                $dettesData[] = (float) ($dettesCrees[$key] ?? 0);
            }

            // Ventes quotidiennes du mois de référence (mois courant ou dernier mois de l'année)
            $moisRef = ($annee === $anneeActuelle) ? (int) date('n') : 12;
            $joursDansMois = cal_days_in_month(CAL_GREGORIAN, $moisRef, $annee);
            $ventesQuotidiennes = Vente::where('tenant_id', $tenant->id)
                ->whereYear('date_vente', $annee)->whereMonth('date_vente', $moisRef)
                ->selectRaw("{$daySql('date_vente')} as jour, SUM(montant_total) as total")
                ->groupBy('jour')->orderBy('jour')
                ->pluck('total', 'jour');

            $joursLabels    = [];
            $ventesJourData = [];
            for ($j = 1; $j <= $joursDansMois; $j++) {
                $key = str_pad($j, 2, '0', STR_PAD_LEFT);
                $joursLabels[]    = (string) $j;
                $ventesJourData[] = (float) ($ventesQuotidiennes[$key] ?? 0);
            }

            $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jui','Jul','Aoû','Sep','Oct','Nov','Déc'];
        }

        // ─── Top produits (filtrés par mois si sélectionné) ──────────────────
        $topQuery = \DB::table('vente_lignes')
            ->join('ventes', 'ventes.id', '=', 'vente_lignes.vente_id')
            ->join('produits', 'produits.id', '=', 'vente_lignes.produit_id')
            ->where('ventes.tenant_id', $tenant->id)
            ->whereYear('ventes.date_vente', $annee);
        if ($moisFiltre) {
            $topQuery->whereMonth('ventes.date_vente', (int) $moisFiltre);
        }
        $topProduits = $topQuery
            ->select('produits.nom', \DB::raw('SUM(vente_lignes.quantite) as total_vendu'))
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('total_vendu')->get();

        // ─── Statut paiement ──────────────────────────────────────────────────
        $spQuery = Vente::where('tenant_id', $tenant->id)->whereYear('date_vente', $annee);
        if ($moisFiltre) {
            $spQuery->whereMonth('date_vente', (int) $moisFiltre);
        }
        $statutPaiementPluck = $spQuery
            ->selectRaw("statut_paiement, SUM(montant_total) as total")
            ->groupBy('statut_paiement')->pluck('total', 'statut_paiement');

        $statutLabels = [];
        $statutData   = [];
        $statutColors = [];
        $statutMap    = ['paye' => 'Payé', 'partiel' => 'Partiel', 'impaye' => 'Impayé'];
        $colorMap     = ['paye' => '#16a34a', 'partiel' => '#d97706', 'impaye' => '#dc2626'];
        foreach ($statutMap as $key => $label) {
            if (isset($statutPaiementPluck[$key])) {
                $statutLabels[] = $label;
                $statutData[]   = (float) $statutPaiementPluck[$key];
                $statutColors[] = $colorMap[$key];
            }
        }

        // ─── Ventes par vendeur ───────────────────────────────────────────────
        $vpvQuery = \DB::table('ventes')
            ->where('tenant_id', $tenant->id)->whereYear('date_vente', $annee);
        if ($moisFiltre) {
            $vpvQuery->whereMonth('date_vente', (int) $moisFiltre);
        }
        $ventesParVendeur = $vpvQuery
            ->selectRaw('user_id, SUM(montant_total) as total')
            ->groupBy('user_id')->orderByDesc('total')->get()
            ->map(function ($item) {
                $item->user = User::find($item->user_id);
                return $item;
            });

        // ─── Alertes stock (indépendant du filtre période) ────────────────────
        $produits   = Produit::where('tenant_id', $tenant->id)->get();
        $magasinIds = $tenant->magasins->pluck('id');

        $mouvementsParMagasin = collect();
        if ($magasinIds->isNotEmpty()) {
            $mouvementsParMagasin = StockMouvement::whereIn('magasin_id', $magasinIds)
                ->select('produit_id', 'magasin_id')
                ->selectRaw("SUM(CASE
                    WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite
                    WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite
                    ELSE 0 END) as total")
                ->groupBy('produit_id', 'magasin_id')->get()->groupBy('produit_id');
        }

        $stockAlertes = [];
        foreach ($produits as $produit) {
            $seuil    = (int) ($produit->seuil_alerte ?? 0);
            $minStock = null;
            $enAlerte = false;
            foreach ($magasinIds as $mid) {
                $ligne = optional($mouvementsParMagasin->get($produit->id))->firstWhere('magasin_id', $mid);
                if (!$ligne) continue;
                $s = (int) $ligne->total;
                if ($minStock === null || $s < $minStock) $minStock = $s;
                if ($s <= 5 || $s <= $seuil) $enAlerte = true;
            }
            if ($enAlerte) {
                $stockAlertes[] = [
                    'id'           => $produit->id,
                    'nom'          => $produit->nom,
                    'seuil_alerte' => $produit->seuil_alerte,
                    'stock'        => $minStock,
                ];
            }
        }

        $dateDuJour = \Carbon\Carbon::now()->fr('d F Y');
        $mois       = $moisFiltre; // alias pour la vue blade

        // ─── Réponse ─────────────────────────────────────────────────────────
        $data = compact(
            'moisLabels', 'moisData', 'depensesData', 'revenuNetData',
            'joursLabels', 'ventesJourData',
            'topProduits', 'statutLabels', 'statutData', 'statutColors',
            'ventesParVendeur', 'dettesData', 'nbVentesData',
            'stockAlertes', 'loyerMensuel', 'annee', 'moisEcoules',
            'loyersCumules', 'dateDuJour', 'mois'
        );

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('analytique', array_merge($data, compact('tenant')));
    }
}
