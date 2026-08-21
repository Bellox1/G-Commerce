<?php

namespace App\Http\Controllers;

use App\Models\DepenseJournaliere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepenseController extends Controller
{
    /**
     * Liste complète des dépenses du tenant, filtrable par période.
     * Par défaut : aujourd'hui. Période personnalisable (date_debut / date_fin).
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $tenant = $user->tenant;

        $debut = $request->date_debut ?: today()->format('Y-m-d');
        $fin   = $request->date_fin;

        if (!$fin) {
            // Si seule la date de début est fournie → jusqu'à aujourd'hui
            $fin = $request->date_debut ?: today()->format('Y-m-d');
        }
        if (!$request->date_debut && $request->date_fin) {
            // Si seule la date de fin est fournie → cette journée uniquement
            $debut = $fin;
        }

        $query = DepenseJournaliere::where('tenant_id', $tenant->id)
            ->whereDate('date_depense', '>=', $debut)
            ->whereDate('date_depense', '<=', $fin);

        $depenses = (clone $query)
            ->with('user')
            ->orderByDesc('date_depense')
            ->orderByDesc('id')
            ->get();

        $total = (clone $query)->sum('montant');

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success'   => true,
                'data'      => $depenses,
                'total'     => (float) $total,
                'date_debut'=> $debut,
                'date_fin'  => $fin,
            ]);
        }

        return view('depenses.index', compact('depenses', 'total', 'debut', 'fin'));
    }

    /**
     * Suppression d'une dépense.
     */
    public function destroy(Request $request, DepenseJournaliere $depense)
    {
        $user   = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant || $depense->tenant_id !== $tenant->id) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => 'Action non autorisée.'], 403);
            }
            abort(403);
        }

        if ($depense->audio_path) {
            $path = storage_path('app/public/' . $depense->audio_path);
            if (is_file($path)) @unlink($path);
        }

        $depense->delete();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['success' => true, 'message' => 'Dépense supprimée.']);
        }

        return redirect()->route('depenses.index', [
            'date_debut' => $request->date_debut,
            'date_fin'   => $request->date_fin,
        ])->with('success', 'Dépense supprimée.');
    }
}
