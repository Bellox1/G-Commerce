<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FournisseurController extends Controller
{
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
            'id'    => $fournisseur->id,
            'nom'   => $fournisseur->nom,
            'ville' => $fournisseur->ville,
            'pays'  => $fournisseur->pays,
        ]);
    }
}
