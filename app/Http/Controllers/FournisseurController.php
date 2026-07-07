<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FournisseurController extends Controller
{
    /**
     * Liste tous les fournisseurs du tenant (API).
     */
    public function index()
    {
        $fournisseurs = Fournisseur::where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('nom')
            ->get(['id', 'nom', 'pays', 'ville', 'telephone', 'devise']);

        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json(['success' => true, 'data' => $fournisseurs]);
        }

        // Fallback web : pas de vue dédiée, on redirige
        return redirect()->route('arrivages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'pays'      => 'nullable|string|max:100',
            'ville'     => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:50',
            'devise'    => 'nullable|string|max:10',
        ]);

        $fournisseur = Fournisseur::create([
            'tenant_id' => Auth::user()->tenant_id,
            'nom'       => $request->nom,
            'pays'      => $request->pays ?? 'Nigeria',
            'ville'     => $request->ville,
            'telephone' => $request->telephone,
            'devise'    => $request->devise ?? 'NGN',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur créé avec succès.',
            'id'      => $fournisseur->id,
            'nom'     => $fournisseur->nom,
            'ville'   => $fournisseur->ville,
            'pays'    => $fournisseur->pays,
            'devise'  => $fournisseur->devise,
        ], 201);
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        if ($fournisseur->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }

        $request->validate([
            'nom'       => 'required|string|max:255',
            'pays'      => 'nullable|string|max:100',
            'ville'     => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:50',
            'devise'    => 'nullable|string|max:10',
        ]);

        $fournisseur->update($request->only(['nom', 'pays', 'ville', 'telephone', 'devise']));

        return response()->json([
            'success'     => true,
            'message'     => 'Fournisseur mis à jour.',
            'fournisseur' => $fournisseur->fresh(),
        ]);
    }

    public function destroy(Fournisseur $fournisseur)
    {
        if ($fournisseur->tenant_id !== Auth::user()->tenant_id) {
            abort(403, 'Action non autorisée.');
        }

        $fournisseur->delete();

        return response()->json(['success' => true, 'message' => 'Fournisseur supprimé.']);
    }
}
