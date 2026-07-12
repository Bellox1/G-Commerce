<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DemandePrestataire;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    public function indexPrestataires()
    {
        $demandes = DemandePrestataire::latest()->get();
        return view('admin.prestataires.index', compact('demandes'));
    }

    public function validerPrestataire($id)
    {
        $demande = DemandePrestataire::findOrFail($id);
        
        if ($demande->statut !== 'en_attente') {
            return back()->with('error', 'Demande déjà traitée.');
        }

        $existingUser = User::withoutGlobalScopes()->where('email', $demande->email)->first();

        if ($existingUser) {
            $roles = $existingUser->roles_secondaires ?? [];
            if (!in_array('prestataire', $roles)) {
                $roles[] = 'prestataire';
            }
            $existingUser->update(['roles_secondaires' => $roles]);

            $demande->update([
                'statut' => 'approuve',
                'user_id' => $existingUser->id
            ]);

            try {
                Mail::raw(
                    "Bonjour {$demande->prenom},\n\n" .
                    "Votre demande de partenariat a été approuvée !\n" .
                    "Vous avez désormais le rôle prestataire avec votre compte existant.\n" .
                    "Connectez-vous sur " . url('/login') . " avec vos identifiants habituels.\n\n" .
                    "Bienvenue dans l'équipe Pilotix !",
                    function ($message) use ($demande) {
                        $message->to($demande->email)
                            ->subject('Vous êtes maintenant partenaire Pilotix !')
                            ->from('pilotrix@gmail.com', 'Pilotix');
                    }
                );
            } catch (\Exception $e) {}

            return back()->with('success', "Compte existant {$existingUser->email} mis à jour — rôle prestataire ajouté en secondaire.");
        }

        $password = Str::random(10);
        $user = User::create([
            'name' => $demande->nom . ' ' . $demande->prenom,
            'email' => $demande->email,
            'telephone' => $demande->telephone,
            'password' => Hash::make($password),
            'role' => 'prestataire',
            'actif' => true
        ]);

        $demande->update([
            'statut' => 'approuve',
            'user_id' => $user->id
        ]);

        try {
            Mail::raw(
                "Bonjour {$demande->prenom},\n\n" .
                "Votre demande de partenariat a été approuvée !\n\n" .
                "Voici vos identifiants :\n" .
                "Email : {$demande->email}\n" .
                "Mot de passe temporaire : {$password}\n\n" .
                "Connectez-vous sur " . url('/login') . " puis changez votre mot de passe depuis votre profil.\n\n" .
                "Bienvenue dans l'équipe Pilotix !",
                function ($message) use ($demande) {
                    $message->to($demande->email)
                        ->subject('Votre compte Pilotix est prêt !')
                        ->from('pilotrix@gmail.com', 'Pilotix');
                }
            );
        } catch (\Exception $e) {}

        return back()->with('success', 'Nouveau compte créé pour ' . $user->email . ' — mot de passe : ' . $password . '.');
    }
}
