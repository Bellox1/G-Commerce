<?php

namespace App\Http\Controllers;

use App\Models\Tresorerie;
use App\Models\DetteSociete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TresorerieController extends Controller
{
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $query = Tresorerie::where('tenant_id', $tenant->id);

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }
        if ($request->filled('date_debut')) {
            $query->where('date', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->where('date', '<=', $request->date_fin);
        }
        if ($request->filled('sens')) {
            $query->where('sens', $request->sens);
        }

        $totalEntrees = (float) (clone $query)->where('sens', 'entree')->sum('montant');
        $totalSorties = (float) (clone $query)->where('sens', 'sortie')->sum('montant');
        $totalCaJour  = (float) (clone $query)->where('sens', 'ca_jour')->sum('montant');

        $items = $query->latest('date')->latest('id')->paginate(30);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $items,
                'total_entrees' => $totalEntrees,
                'total_sorties' => $totalSorties,
                'total_ca_jour' => $totalCaJour,
            ]);
        }

        return view('tresoreries.index', compact('items', 'totalEntrees', 'totalSorties', 'totalCaJour'));
    }

    public function store(Request $request)
    {
        $tenant = Auth::user()->tenant;

        $data = $request->validate([
            'date'           => 'required|date',
            'sens'           => 'required|in:entree,sortie,ca_jour',
            'montant'        => 'required|numeric|min:1',
            'libelle'        => 'nullable|string|max:255',
            'mode_paiement'  => 'nullable|string|max:50',
            'note'           => 'nullable|string',
        ]);

        $data['tenant_id'] = $tenant->id;
        $data['user_id'] = Auth::id();
        if (empty($data['libelle'])) {
            $data['libelle'] = ($data['sens'] === 'sortie' || $data['sens'] === 'ca_jour') ? "Chiffre d'affaire du jour" : "Capital apporté";
        }
        $data['mode_paiement'] = $data['mode_paiement'] ?: 'Espèces';

        $item = Tresorerie::create($data);

        return $this->smartResponse(
            route('tresoreries.index'),
            'Mouvement de trésorerie enregistré.',
            ['data' => $item]
        );
    }

    public function destroy(Request $request, Tresorerie $tresorerie)
    {
        if ($tresorerie->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $tresorerie->delete();

        return $this->smartResponse(route('tresoreries.index'), 'Mouvement supprimé.');
    }
}
