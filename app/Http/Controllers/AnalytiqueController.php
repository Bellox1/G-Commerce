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

        // ─── Ventes mensuelles (12 mois) ───
        $ventesMensuelles = Vente::where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->selectRaw("strftime('%m', date_vente) as mois, SUM(montant_paye) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $moisData = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $moisData[] = (float) ($ventesMensuelles[$key] ?? 0);
        }

        // ─── Ventes quotidiennes du mois ───
        $joursDansMois = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
        $ventesQuotidiennes = Vente::where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->whereMonth('date_vente', $mois)
            ->selectRaw("strftime('%d', date_vente) as jour, SUM(montant_paye) as total")
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
            ->selectRaw("strftime('%m', date_depense) as mois, SUM(montant) as total")
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

        // Revenu net mensuel
        $revenuNetData = [];
        for ($i = 0; $i < 12; $i++) {
            $revenuNetData[] = $moisData[$i] - $depensesData[$i] - $loyerMensuel;
        }

        // ─── Ventes par vendeur (année) ───
        $ventesParVendeur = \DB::table('ventes')
            ->where('tenant_id', $tenant->id)
            ->whereYear('date_vente', $annee)
            ->selectRaw('user_id, SUM(montant_paye) as total')
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
            ->selectRaw("strftime('%m', created_at) as mois, SUM(montant_restant) as total")
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
            ->selectRaw("strftime('%m', date_vente) as mois, COUNT(*) as total")
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $nbVentesData = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $nbVentesData[] = (int) ($nbVentesParMois[$key] ?? 0);
        }

        // ─── Produits en alerte stock ───
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
                $stockAlertes[] = ['nom' => $produit->nom, 'stock' => $stockTotal];
            }
        }

        return view('analytique', compact(
            'moisLabels', 'moisData', 'depensesData', 'revenuNetData',
            'joursLabels', 'ventesJourData',
            'topProduits', 'statutLabels', 'statutData', 'statutColors',
            'ventesParVendeur', 'dettesData', 'nbVentesData',
            'stockAlertes', 'loyerMensuel', 'annee', 'mois', 'tenant'
        ));
    }
}
