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
            'nom_societe'        => 'required|string|max:255',
            'localisation'       => 'required|string|max:50', // Pays
            'ville'              => 'required|string|max:100',
            'telephone'          => 'required|string|max:50',
            'secteurs_activite'  => 'required|string|max:255',
            'email'              => 'required|email|max:255',
        ]);

        // Récupérer les super admins
        $superAdmins = User::where('role', 'super_admin')->get();
        $recipientEmails = $superAdmins->pluck('email')->toArray();

        // Toujours inclure l'email configuré comme destinataire principal
        $adminEmail = config('mail.from.address', 'belloxdigital@gmail.com');
        $recipientEmails = array_unique(array_merge([$adminEmail], $recipientEmails));

        // Préparation du message
        $subject = "Nouvelle demande de création de société — G-STOCK";
        $societe = e($data['nom_societe']);
        $localisation = e($data['localisation']);
        $ville = e($data['ville']);
        $telephone = e($data['telephone']);
        $secteursActivite = e($data['secteurs_activite']);
        $emailContact = e($data['email']);

        try {
            Mail::send([], [], function ($message) use ($recipientEmails, $subject, $societe, $localisation, $ville, $telephone, $secteursActivite, $emailContact) {
                $message->to($recipientEmails)
                    ->subject($subject)
                    ->html("
                        <div style=\"font-family: 'Inter', sans-serif; max-width: 550px; margin: 0 auto; padding: 30px; border: 1px solid #e5e7eb; border-radius: 12px; background-color: #ffffff;\">
                            <div style=\"text-align: center; margin-bottom: 24px;\">
                                <h2 style=\"color: #105e49; font-weight: 800; font-size: 24px; margin: 0 0 8px 0;\">G-STOCK</h2>
                                <p style=\"color: #6b7280; font-size: 14px; margin: 0;\">Gestion commerciale & stock</p>
                            </div>
                            <div style=\"border-bottom: 1px solid #f3f4f6; margin-bottom: 24px;\"></div>
                            <h3 style=\"color: #1f2937; font-weight: 700; font-size: 18px; margin: 0 0 12px 0;\">Nouvelle demande de création de société</h3>
                            <p style=\"color: #4b5563; font-size: 15px; line-height: 1.5; margin: 0 0 20px 0;\">
                                Une demande a été soumise depuis la page d'accueil d'G-STOCK.
                            </p>
                            <table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;\">
                                <tr>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 120px; font-weight: 600;\">Société</td>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937; font-weight: 600;\">{$societe}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 120px; font-weight: 600;\">Localisation</td>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$localisation}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 120px; font-weight: 600;\">Ville</td>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$ville}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 120px; font-weight: 600;\">Téléphone</td>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$telephone}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 120px; font-weight: 600;\">Secteurs</td>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$secteursActivite}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 120px; font-weight: 600;\">Email</td>
                                    <td style=\"padding: 10px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$emailContact}</td>
                                </tr>
                            </table>
                            <div style=\"text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f3f4f6;\">
                                <p style=\"color: #9ca3af; font-size: 12px; margin: 0;\">
                                    G-STOCK — Gestion commerciale &amp; stock<br>
                                    Cet email est un accusé de réception automatique.
                                </p>
                            </div>
                        </div>
                    ");
            });
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi de mail de contact : " . $e->getMessage());
        }

        return redirect()->to('/#contact')->with('success', 'Votre demande de création de société a bien été envoyée. Nos administrateurs vous contacteront sous peu.');
    }
}
