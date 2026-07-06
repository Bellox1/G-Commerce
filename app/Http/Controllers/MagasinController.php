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

        return view('magasins.index', compact('magasins'));
    }

    public function store(Request $request)
    {
        $this->authorizeModule('magasins');
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

        if ($request->wantsJson()) {
            return response()->json([
                'id'  => $magasin->id,
                'nom' => $magasin->nom,
            ]);
        }

        return redirect()->route('magasins.index')->with('success', 'Dépôt créé avec succès.');
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

        return redirect()->route('magasins.index')->with('success', 'Dépôt modifié avec succès.');
    }
}
