<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DemandePrestataire;
use App\Models\User;
use App\Models\Commission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DemandeController extends Controller
{
    private function isApi(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    public function indexPrestataires(Request $request)
    {
        $demandes = DemandePrestataire::latest()->get();

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'data' => $demandes]);
        }

        return view('admin.prestataires.index', compact('demandes'));
    }

    public function showPrestataire(Request $request, $id)
    {
        $demande = DemandePrestataire::findOrFail($id);

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'data' => $demande]);
        }

        return view('admin.prestataires.show', compact('demande'));
    }

    public function validerPrestataire(Request $request, $id)
    {
        $demande = DemandePrestataire::findOrFail($id);

        if ($demande->statut !== 'en_attente') {
            if ($this->isApi($request)) {
                return response()->json(['success' => false, 'message' => 'Demande déjà traitée.'], 422);
            }
            return back()->with('error', 'Demande déjà traitée.');
        }

        $plainPassword = null;
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

            $message = "Compte existant {$existingUser->email} mis à jour — rôle prestataire ajouté.";
        } else {
            $plainPassword = $demande->password ? null : Str::random(10);
            $hashedPassword = $demande->password ? $demande->password : Hash::make($plainPassword);

            $user = User::create([
                'name' => $demande->nom . ' ' . $demande->prenom,
                'email' => $demande->email,
                'telephone' => $demande->telephone,
                'password' => $hashedPassword,
                'role' => 'prestataire',
                'actif' => true
            ]);

            $demande->update([
                'statut' => 'approuve',
                'user_id' => $user->id
            ]);

            $message = 'Nouveau compte créé pour ' . $user->email . ' — mot de passe temporaire : ' . $plainPassword . '.';
        }

        try {
            $credentials = $plainPassword
                ? "Un compte a été créé pour vous. Voici vos identifiants :\nEmail : {$demande->email}\nMot de passe : {$plainPassword}\n\nConnectez-vous sur " . url('/login') . " puis changez votre mot de passe depuis votre profil.\n\n"
                : "Vous avez désormais le rôle prestataire avec votre compte existant.\nConnectez-vous sur " . url('/login') . " avec vos identifiants habituels.\n\n";

            Mail::raw(
                "Bonjour {$demande->prenom},\n\n" .
                "Votre demande de partenariat a été approuvée !\n\n" .
                $credentials .
                "Bienvenue dans l'équipe Pilotix !",
                function ($mail) use ($demande) {
                    $mail->to($demande->email)
                        ->subject('Vous êtes maintenant partenaire Pilotix !')
                        ->from('pilotixcontact@gmail.com', 'Pilotix');
                }
            );
        } catch (\Exception $e) {}

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'message' => $message, 'data' => $demande->fresh()]);
        }

        return back()->with('success', $message);
    }

    public function rejeterPrestataire(Request $request, $id)
    {
        $demande = DemandePrestataire::findOrFail($id);

        if ($demande->statut !== 'en_attente') {
            if ($this->isApi($request)) {
                return response()->json(['success' => false, 'message' => 'Demande déjà traitée.'], 422);
            }
            return back()->with('error', 'Demande déjà traitée.');
        }

        $demande->update([
            'statut' => 'rejete'
        ]);

        try {
            Mail::raw(
                "Bonjour {$demande->prenom},\n\n" .
                "Nous avons bien étudié votre demande de partenariat pour Pilotix.\n" .
                "Malheureusement, nous ne pouvons pas y donner une suite favorable pour le moment.\n\n" .
                "Cordialement,\nL'équipe Pilotix",
                function ($mail) use ($demande) {
                    $mail->to($demande->email)
                        ->subject('Votre demande de partenariat Pilotix')
                        ->from('pilotixcontact@gmail.com', 'Pilotix');
                }
            );
        } catch (\Exception $e) {}

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'message' => 'La demande a été rejetée.', 'data' => $demande->fresh()]);
        }

        return back()->with('success', 'La demande a été rejetée.');
    }

    public function indexCommissions(Request $request)
    {
        $commissions = Commission::with(['partenaire', 'tenant'])->latest()->get();

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'data' => $commissions]);
        }

        return view('admin.commissions.index', compact('commissions'));
    }

    public function updateCommissionStatut(Request $request, $id)
    {
        $commission = Commission::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:en_attente,reglee'
        ]);

        $commission->update([
            'statut' => $request->statut
        ]);

        if ($this->isApi($request)) {
            return response()->json(['success' => true, 'message' => 'Statut de la commission mis à jour.', 'data' => $commission]);
        }

        return back()->with('success', 'Statut de la commission mis à jour.');
    }
}
