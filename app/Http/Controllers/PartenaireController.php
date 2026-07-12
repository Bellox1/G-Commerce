<?php

namespace App\Http\Controllers;

use App\Models\DemandePrestataire;
use Illuminate\Http\Request;

class PartenaireController extends Controller
{
    public function index()
    {
        return view('partenaires');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'nom'      => 'required',
            'prenom'   => 'required',
            'email'    => 'required|email',
            'telephone'=> 'required',
            'q5'       => 'required',
            'q6'       => 'required',
            'q7'       => 'required',
            'q8'       => 'required',
            'q9'       => 'required',
            'q10'      => 'required',
            'q11'      => 'required',
            'q12'      => 'required',
            'q13'      => 'required',
            'q14'      => 'required',
            'q15'      => 'required',
            'q16'      => 'required',
            'q17'      => 'required',
            'q18'      => 'required',
            'q19'      => 'required',
            'q20'      => 'required',
            'q21'      => 'required',
            'q22'      => 'required',
            'q23'      => 'required',
            'q24'      => 'required',
            'q25'      => 'required',
        ]);

        $questionnaire = collect([
            $validated['q5'],  $validated['q6'],  $validated['q7'],
            $validated['q8'],  $validated['q9'],  $validated['q10'],
            $validated['q11'], $validated['q12'], $validated['q13'],
            $validated['q14'], $validated['q15'], $validated['q16'],
            $validated['q17'], $validated['q18'], $validated['q19'],
            $validated['q20'], $validated['q21'], $validated['q22'],
            $validated['q23'], $validated['q24'], $validated['q25'],
        ])->values()->all();

        $partner = DemandePrestataire::create([
            'nom'         => $validated['nom'],
            'prenom'      => $validated['prenom'],
            'email'       => $validated['email'],
            'telephone'   => $validated['telephone'],
            'entreprise'  => null,
            'motivation'  => $validated['q25'],
            'questionnaire' => $questionnaire,
            'statut'      => 'en_attente',
            'user_id'     => null,
        ]);

        $emails = array_filter([
            'belloxdigital@gmail.com',
            config('admin.email'),
        ]);

        foreach ($emails as $email) {
            \Mail::raw($this->buildMailBody($partner), function ($message) use ($email, $partner) {
                $message->to($email)
                    ->subject("Nouvelle candidature partenaire — {$partner->nom} {$partner->prenom}")
                    ->from($partner->email, "{$partner->nom} {$partner->prenom}");
            });
        }

        return redirect()->route('partenaires')
            ->with('success', 'Merci ! Votre candidature a bien été envoyée. Nous vous recontacterons sous 48h.');
    }

    private function buildMailBody(DemandePrestataire $partner): string
    {
        $labels = [
            'Ville / Zone',
            'Activité principale',
            'Nombre de commerces dans le réseau',
            'Secteurs ciblés',
            'Type de commerçants ciblés',
            'Nb commerçants contactés / mois',
            'Canaux de vente',
            'Expérience vente logiciel',
            'Atout principal pour convaincre',
            'Les commerçants utilisent-ils un logiciel',
            'Problème principal des commerçants',
            'Aisance à expliquer les avantages',
            'Confort démonstration client',
            'Suivi post-vente',
            'Temps dédié / semaine',
            'Véhicule pour terrain',
            'Commerçants dans d\'autres villes',
            'Formation acceptée ?',
            'Objectif vente / mois',
            'Préférence commissions (récurrentes / ponctuelles)',
            'Motivation',
        ];

        $body = "CANDIDATURE PARTENAIRE PILOTIX\n";
        $body .= str_repeat('=', 45) . "\n\n";
        $body .= "Nom : {$partner->nom} {$partner->prenom}\n";
        $body .= "Email : {$partner->email}\n";
        $body .= "Téléphone : {$partner->telephone}\n\n";
        $body .= "─── Questionnaire (25 questions) ───\n\n";

        $answers = $partner->questionnaire ?? [];
        foreach ($labels as $i => $label) {
            $val = $answers[$i] ?? '—';
            $num = $i + 1;
            $body .= "{$num}. {$label}\n   → {$val}\n\n";
        }

        return $body;
    }
}
