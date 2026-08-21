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
        $user = Auth::user();
        $tenant = $user->tenant;

        $annee = $request->annee ?: date('Y');
        $mois  = $request->mois  ?: date('m');

        $isSqlite = \DB::connection()->getDriverName() === 'sqlite';
        $monthSql = fn($col) => $isSqlite ? "strftime('%m', {$col})" : "DATE_FORMAT({$col}, '%m')";
        $daySql   = fn($col) => $isSqlite ? "strftime('%d', {$col})" : "DATE_FORMAT({$col}, '%d')";

        // ─── Ventes mensuelles (12 mois) — CA = montant_total (ventes réalisées) ───
        $ventesMensuelles = Vente::where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->selectRaw("{$monthSql('date_vente')} as mois, SUM(montant_total) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $moisData = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $moisData[] = (float) ($ventesMensuelles[$key] ?? 0);
        }

        // ─── Ventes quotidiennes du mois — CA = montant_total ───
        $joursDansMois = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
        $ventesQuotidiennes = Vente::where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->whereMonth('date_vente', $mois)
            ->selectRaw("{$daySql('date_vente')} as jour, SUM(montant_total) as total")
            ->groupBy('jour')
            ->orderBy('jour')
            ->pluck('total', 'jour');

        $joursLabels = [];
        $ventesJourData = [];
        for ($j = 1; $j <= $joursDansMois; $j++) {
            $key = str_pad($j, 2, '0', STR_PAD_LEFT);
            $joursLabels[] = (string) $j;
            $ventesJourData[] = (float) ($ventesQuotidiennes[$key] ?? 0);
        }

        // ─── Top produits ───
        $topProduits = \DB::table('vente_lignes')
            ->join('ventes', 'ventes.id', '=', 'vente_lignes.vente_id')
            ->join('produits', 'produits.id', '=', 'vente_lignes.produit_id')
            ->where('ventes.tenant_id', $tenant->id)
            ->whereYear('ventes.date_vente', $annee)
            ->select('produits.nom', \DB::raw('SUM(vente_lignes.quantite) as total_vendu'))
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('total_vendu')
            ->limit(10)
            ->get();

        // ─── Statut paiement ───
        $statutPaiementPluck = Vente::where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->selectRaw("statut_paiement, SUM(montant_total) as total")
            ->groupBy('statut_paiement')
            ->pluck('total', 'statut_paiement');
        $statutLabels = [];
        $statutData = [];
        $statutColors = [];
        $statutMap = ['paye' => 'Payé', 'partiel' => 'Partiel', 'impaye' => 'Impayé'];
        $colorMap = ['paye' => '#16a34a', 'partiel' => '#d97706', 'impaye' => '#dc2626'];
        foreach ($statutMap as $key => $label) {
            if (isset($statutPaiementPluck[$key])) {
                $statutLabels[] = $label;
                $statutData[] = (float) $statutPaiementPluck[$key];
                $statutColors[] = $colorMap[$key];
            }
        }

        // ─── Dépenses mensuelles ───
        $depensesMensuelles = DepenseJournaliere::where('tenant_id', $tenant->id)
            ->whereYear('date_depense', $annee)
            ->selectRaw("{$monthSql('date_depense')} as mois, SUM(montant) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $depensesData = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $depensesData[] = (float) ($depensesMensuelles[$key] ?? 0);
        }

        // Loyers mensuels fixes
        $loyerMensuel = (float) Magasin::where('tenant_id', $tenant->id)->sum('loyer');

        // Loyers cumulés à date (on ne compare pas à une année complète)
        $moisEcoules = ($annee == date('Y')) ? (int) date('n') : 12;
        $loyersCumules = $loyerMensuel * $moisEcoules;
        $dateDuJour = \Carbon\Carbon::now()->fr('d F Y');

        // Revenu net mensuel
        $revenuNetData = [];
        for ($i = 0; $i < 12; $i++) {
            $revenuNetData[] = $moisData[$i] - $depensesData[$i] - $loyerMensuel;
        }

        // ─── Ventes par vendeur (année) — CA = montant_total ───
        $ventesParVendeur = \DB::table('ventes')
            ->where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->selectRaw('user_id, SUM(montant_total) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $item->user = User::find($item->user_id);
                return $item;
            });

        // ─── Dettes: total par mois ───
        $dettesCrees = Dette::where('tenant_id', $tenant->id)
            ->whereYear('created_at', $annee)
            ->selectRaw("{$monthSql('created_at')} as mois, SUM(montant_restant) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $dettesData = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $dettesData[] = (float) ($dettesCrees[$key] ?? 0);
        }

        // ─── Labels mois ───
        $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Jui','Jui','Aoû','Sep','Oct','Nov','Déc'];

        // ─── Nombre de ventes par mois ───
        $nbVentesParMois = Vente::where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->selectRaw("{$monthSql('date_vente')} as mois, COUNT(*) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $nbVentesData = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $nbVentesData[] = (int) ($nbVentesParMois[$key] ?? 0);
        }

        // ─── Produits en alerte stock (par magasin : alerte si bas dans un magasin quelconque) ───
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
                $stockAlertes[] = [
                    'id'            => $produit->id,
                    'nom'           => $produit->nom,
                    'seuil_alerte'  => $produit->seuil_alerte,
                    'stock'         => $minStock,
                ];
            }
        }

        // ─── Réponse ───────────────────────────────────────────────────────
        $data = compact(
            'moisLabels', 'moisData', 'depensesData', 'revenuNetData',
            'joursLabels', 'ventesJourData',
            'topProduits', 'statutLabels', 'statutData', 'statutColors',
            'ventesParVendeur', 'dettesData', 'nbVentesData',
            'stockAlertes', 'loyerMensuel', 'annee', 'mois',
            'loyersCumules', 'dateDuJour'
        );

        // API / mobile → JSON
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        // Web → Vue Blade
        return view('analytique', array_merge($data, compact('tenant')));
    }
}
