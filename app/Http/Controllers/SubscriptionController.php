<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DemandePrestataire;

class SubscriptionController extends Controller
{
    // Affiche le formulaire pour devenir prestataire
    public function showPrestataireForm()
    {
        return view('devenir-prestataire');
    }

    // Traite la demande prestataire (partenaire)
    public function submitPrestataire(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'entreprise' => 'nullable|string|max:255',
            'motivation' => 'nullable|string'
        ]);

        DemandePrestataire::create($validated);

        return redirect()->route('partenaires')->with('success', 'Votre demande de partenariat a bien été enregistrée. Nous l\'étudions et vous recontactons au plus vite.');
    }
}
