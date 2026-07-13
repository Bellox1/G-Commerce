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
        $rules = [
            'nom'      => 'required',
            'prenom'   => 'required',
            'email'    => 'required|email',
            'telephone'=> 'required',
        ];

        // Only validate q5 to q25 if they are present (wizard form)
        $isWizard = $request->has('q5');
        if ($isWizard) {
            for ($i = 5; $i <= 25; $i++) {
                $rules['q' . $i] = 'required';
            }
        }

        $validated = $request->validate($rules);

        $existing = DemandePrestataire::where('email', $validated['email'])
            ->whereIn('statut', ['en_attente', 'approuve'])
            ->first();

        if ($existing) {
            $message = $existing->statut === 'en_attente'
                ? 'Vous avez déjà soumis une candidature. Elle est en cours de traitement, merci de patienter.'
                : 'Votre candidature a déjà été approuvée. Vous pouvez vous connecter avec votre compte.';

            return back()->withErrors(['email' => $message])->withInput();
        }

        $questionnaire = null;
        if ($isWizard) {
            $questionnaire = collect([
                $validated['q5'],  $validated['q6'],  $validated['q7'],
                $validated['q8'],  $validated['q9'],  $validated['q10'],
                $validated['q11'], $validated['q12'], $validated['q13'],
                $validated['q14'], $validated['q15'], $validated['q16'],
                $validated['q17'], $validated['q18'], $validated['q19'],
                $validated['q20'], $validated['q21'], $validated['q22'],
                $validated['q23'], $validated['q24'], $validated['q25'],
            ])->values()->all();
        }

        $partner = DemandePrestataire::create([
            'nom'           => $validated['nom'],
            'prenom'        => $validated['prenom'],
            'email'         => $validated['email'],
            'telephone'     => $validated['telephone'],
            'entreprise'    => $request->input('entreprise'),
            'motivation'    => $isWizard ? $validated['q25'] : $request->input('motivation'),
            'questionnaire' => $questionnaire,
            'statut'        => 'en_attente',
            'user_id'       => null,
        ]);

        $emails = array_filter([
            'belloxdigital@gmail.com',
            config('admin.email'),
        ]);

        foreach ($emails as $email) {
            try {
                \Mail::raw($this->buildMailBody($partner), function ($message) use ($email, $partner) {
                    $message->to($email)
                        ->subject("Nouvelle candidature partenaire — {$partner->nom} {$partner->prenom}")
                        ->from('noreply@pilotix.com', 'Pilotix');
                });
            } catch (\Exception $e) {
                \Log::warning("Email candidature échoué pour {$email}: " . $e->getMessage());
            }
        }

        return redirect()->route('partenaires')
            ->with('success', 'Merci ! Votre candidature a bien été envoyée. Nous vous recontacterons sous 48h.');
    }

    private function buildMailBody(DemandePrestataire $partner): string
    {
        $body = "NOUVELLE CANDIDATURE PARTENAIRE PILOTIX\n";
        $body .= str_repeat('=', 42) . "\n\n";
        $body .= "Nom : {$partner->nom} {$partner->prenom}\n";
        $body .= "Email : {$partner->email}\n";
        $body .= "Téléphone : {$partner->telephone}\n";
        if ($partner->entreprise) {
            $body .= "Entreprise : {$partner->entreprise}\n";
        }
        $body .= "Date : {$partner->created_at->format('d/m/Y à H:i')}\n\n";
        $body .= "Connectez-vous à l'administration pour consulter le questionnaire et valider la demande.\n";

        return $body;
    }
}
