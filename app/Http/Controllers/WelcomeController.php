<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();
        $produits = Produit::where('actif', true)->with('tenant')->get()->groupBy('tenant_id');

        return view('welcome', compact('tenants', 'produits'));
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'nom_societe'  => 'required|string|max:255',
            'localisation' => 'required|string|max:50', // Pays
            'ville'        => 'required|string|max:100',
            'telephone'    => 'required|string|max:50',
            'email'        => 'required|email|max:255',
        ]);

        // Récupérer les super admins
        $superAdmins = User::where('role', 'super_admin')->get();
        $recipientEmails = $superAdmins->pluck('email')->toArray();

        // Si aucun super admin dans la DB, on utilise l'email par défaut configuré
        if (empty($recipientEmails)) {
            $recipientEmails[] = config('mail.from.address', 'belloxdigital@gmail.com');
        }

        // Préparation du message
        $subject = "Nouvelle demande de création de société sur OdjaMi";
        $emailBody = "Bonjour,\n\n"
            . "Une nouvelle demande de création de société a été soumise depuis la page d'accueil d'OdjaMi.\n\n"
            . "Détails de la demande :\n"
            . "---------------------------------\n"
            . "🏢 Société      : " . $data['nom_societe'] . "\n"
            . "📍 Localisation  : " . $data['localisation'] . "\n"
            . "🏙️ Ville        : " . $data['ville'] . "\n"
            . "📞 Téléphone    : " . $data['telephone'] . "\n"
            . "✉️ Email        : " . $data['email'] . "\n"
            . "---------------------------------\n\n"
            . "Cordialement,\n"
            . "Système de notification OdjaMi";

        try {
            Mail::raw($emailBody, function ($message) use ($recipientEmails, $subject) {
                $message->to($recipientEmails)
                        ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi de mail de contact : " . $e->getMessage());
            // Même si le mail échoue, on continue pour ne pas bloquer l'expérience utilisateur, mais on note l'erreur
        }

        return redirect()->to('/#contact')->with('success', 'Votre demande de création de société a bien été envoyée. Nos administrateurs vous contacteront sous peu.');
    }
}
