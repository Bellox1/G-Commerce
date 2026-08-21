<?php

namespace App\Http\Controllers;

use App\Models\Magasin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagasinController extends Controller
{
    public function index()
    {
        $this->authorizeModule('magasins');
        $magasins = Magasin::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('nom')
            ->get();

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $magasins]);
        }

        return view('magasins.index', compact('magasins'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('magasins');

        $tenant = Auth::user()->tenant;
        if ($tenant->magasinLimitReached()) {
            $max = $tenant->maxMagasins();
            $msg = "Votre offre limite le nombre de dépôts/magasins à {$max}. Passez à une offre supérieure (Professionnel+) pour en ajouter.";
            if ($request->wantsJson() || $request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:255|unique:magasins,nom,NULL,id,tenant_id,' . Auth::user()->tenant_id,
            'adresse'   => 'nullable|string|max:255',
            'ville'     => 'nullable|string|max:100',
            'loyer'     => 'nullable|numeric|min:0',
        ]);

        $magasin = Magasin::create([
            'tenant_id' => Auth::user()->tenant_id,
            'nom'       => $validated['nom'],
            'adresse'   => $validated['adresse'],
            'ville'     => $validated['ville'],
            'loyer'     => $validated['loyer'] ?: null,
        ]);

        if ($request->wantsJson() || $request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Dépôt créé avec succès.',
                'redirect' => route('magasins.index'),
                'id'  => $magasin->id,
                'nom' => $magasin->nom,
            ]);
        }

        return $this->smartResponse('magasins.index', 'Dépôt créé avec succès.');
    }

    public function update(Request $request, Magasin $magasin)
    {
        if ($magasin->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:255|unique:magasins,nom,' . $magasin->id . ',id,tenant_id,' . Auth::user()->tenant_id,
            'adresse'   => 'nullable|string|max:255',
            'ville'     => 'nullable|string|max:100',
            'loyer'     => 'nullable|numeric|min:0',
        ]);

        $validated['loyer'] = $validated['loyer'] ?: null;
        $magasin->update($validated);

        return $this->smartResponse('magasins.index', 'Dépôt modifié avec succès.');
    }
}
