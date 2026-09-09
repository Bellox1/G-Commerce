<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Produit;
use App\Models\CommissionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    public function index()
    {
        $tenants = Tenant::all();
        $produits = Produit::where('actif', true)->with('tenant')->get()->groupBy('tenant_id');
        $rules = CommissionRule::where('code', '!=', 'locale')->get()->keyBy('code');

        return view('welcome', compact('tenants', 'produits', 'rules'));
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'nom_societe'        => 'required|string|max:255',
            'localisation'       => 'required|string|max:50',
            'ville'              => 'required|string|max:100',
            'telephone'          => 'required|string|max:50',
            'secteurs_activite'  => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'type_souscription'  => 'required|string|in:essentiel,professionnel,entreprise,local,cloud',
            'paiement_3x'        => 'nullable',
        ]);

        $superAdmins = User::where('role', 'super_admin')->get();
        $adminEmails = $superAdmins->pluck('email')->toArray();
        $fromEmail = config('mail.from.address', 'pilotixcontact@gmail.com');
        $adminEmails = array_unique(array_merge([$fromEmail], $adminEmails));

        $societe = e($data['nom_societe']);
        $localisation = e($data['localisation']);
        $ville = e($data['ville']);
        $telephone = e($data['telephone']);
        $secteursActivite = e($data['secteurs_activite']);
        $emailContact = e($data['email']);
        
        $typeRules = CommissionRule::get()->keyBy('code');
        $fmt = fn($c) => number_format($typeRules->get($c)?->prix ?? 0, 0, ' ', ' ');
        $typeMap = [
            'essentiel'     => 'Offre Essentiel (' . $fmt('essentiel') . ' FCFA / an)',
            'professionnel' => 'Offre Professionnel (' . $fmt('professionnel') . ' FCFA / an)',
            'entreprise'    => 'Offre Entreprise (' . $fmt('entreprise') . ' FCFA — Sur-Mesure)',
            'cloud'         => 'Cloud Sync (3 500 FCFA/mois)',
            'local'         => 'Locale (79 900 FCFA)',
        ];
        $typeSouscription = $typeMap[$data['type_souscription']] ?? $data['type_souscription'];
        $is3x = $request->has('paiement_3x');
        $modePaiement = $is3x ? 'Oui (Paiement échelonné en 3 tranches)' : 'Comptant / Intégral';

        // 1. Email aux Super Administrateurs
        try {
            Mail::send([], [], function ($message) use ($adminEmails, $societe, $localisation, $ville, $telephone, $secteursActivite, $emailContact, $typeSouscription, $modePaiement) {
                $message->to($adminEmails)
                    ->subject("Nouvelle demande de création de société — {$societe}")
                    ->html("
                        <div style=\"font-family: 'Plus Jakarta Sans', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background-color: #ffffff;\">
                            <div style=\"text-align: center; margin-bottom: 20px;\">
                                <h2 style=\"color: #105e49; font-weight: 800; font-size: 24px; margin: 0;\">PILOTIX</h2>
                                <p style=\"color: #6b7280; font-size: 13px; margin: 4px 0 0 0;\">Gestion commerciale &amp; stock</p>
                            </div>
                            <div style=\"border-bottom: 1px solid #f3f4f6; margin-bottom: 20px;\"></div>
                            <h3 style=\"color: #1f2937; font-size: 16px; font-weight: 700; margin: 0 0 12px 0;\">🚨 Nouvelle demande de souscription d'entreprise</h3>
                            <table style=\"width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;\">
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280; width: 140px;\">Société</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #1f2937; font-weight: 700;\">{$societe}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280;\">Email Contact</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #105e49; font-weight: 600;\"><a href=\"mailto:{$emailContact}\">{$emailContact}</a></td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280;\">Téléphone</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #1f2937; font-weight: 600;\">{$telephone}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280;\">Pays &amp; Ville</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$localisation} ({$ville})</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280;\">Secteurs</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #1f2937;\">{$secteursActivite}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280;\">Offre souscrite</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #105e49; font-weight: 700;\">{$typeSouscription}</td>
                                </tr>
                                <tr>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #6b7280;\">Mode de paiement</td>
                                    <td style=\"padding: 8px 10px; border-bottom: 1px solid #f3f4f6; color: #ea8d22; font-weight: 700;\">{$modePaiement}</td>
                                </tr>
                            </table>
                        </div>
                    ");
            });
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi de mail admin contact : " . $e->getMessage());
        }

        // 2. Email de confirmation envoyé directement au client
        try {
            Mail::send([], [], function ($message) use ($emailContact, $societe, $typeSouscription, $modePaiement) {
                $message->to($emailContact)
                    ->subject("Confirmation de votre demande de souscription — PILOTIX")
                    ->html("
                        <div style=\"font-family: 'Plus Jakarta Sans', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background-color: #ffffff;\">
                            <div style=\"text-align: center; margin-bottom: 20px;\">
                                <h2 style=\"color: #105e49; font-weight: 800; font-size: 26px; margin: 0;\">PILOTIX</h2>
                                <p style=\"color: #6b7280; font-size: 14px; margin: 4px 0 0 0;\">Gestion commerciale &amp; stock multi-dépôt</p>
                            </div>
                            <div style=\"border-bottom: 1px solid #f3f4f6; margin-bottom: 20px;\"></div>
                            
                            <h3 style=\"color: #1f2937; font-size: 18px; font-weight: 700; margin: 0 0 12px 0;\">Bonjour {$societe},</h3>
                            <p style=\"color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 16px 0;\">
                                Nous avons bien reçu votre demande de création de compte pour la formule <strong>{$typeSouscription}</strong> ({$modePaiement}).
                            </p>
                            <p style=\"color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;\">
                                Notre équipe technique prépare votre espace de travail. Un conseiller PILOTIX vous recontactera sous <strong>24 heures</strong> pour finaliser vos accès et vous accompagner lors de la prise en main.
                            </p>

                            <!-- NOTICE ACCÈS WEB IPHONE (iOS) -->
                            <div style=\"background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; padding: 18px; margin: 24px 0;\">
                                <div style=\"display: flex; align-items: center; gap: 8px; margin-bottom: 8px;\">
                                    <strong style=\"color: #166534; font-size: 15px;\">📱 Information importante pour les utilisateurs d'iPhone (iOS) :</strong>
                                </div>
                                <p style=\"color: #15803d; font-size: 14px; line-height: 1.6; margin: 0;\">
                                    Si vous ou vos collaborateurs utilisez un <strong>iPhone ou un iPad</strong>, vous n'avez besoin d'installer aucune application complexe : <strong>PILOTIX est intégralement disponible et optimisé sur le Web</strong> !<br><br>
                                    Depuis Safari ou Chrome sur <strong>iPhone, iPad, PC Windows, Mac ou Tablette</strong>, vous avez accès à <strong>100% des fonctionnalités</strong> (ventes, gestion des stocks, livraisons, dettes et rapports) sans aucune restriction.
                                </p>
                            </div>

                            <p style=\"color: #4b5563; font-size: 14px; line-height: 1.5; margin: 20px 0 0 0;\">
                                Besoin d'assistance immédiate ? Contactez notre support :<br>
                                📞 <strong>+229 01 46 86 25 36</strong> | ✉️ <strong>pilotixcontact@gmail.com</strong>
                            </p>

                            <div style=\"text-align: center; margin-top: 28px; padding-top: 20px; border-top: 1px solid #f3f4f6;\">
                                <p style=\"color: #9ca3af; font-size: 12px; margin: 0;\">
                                    &copy; " . date('Y') . " PILOTIX — Solution de gestion commerciale multi-Magasins &amp; Dépôts
                                </p>
                            </div>
                        </div>
                    ");
            });
        } catch (\Throwable $e) {
            Log::error("Erreur lors de l'envoi de mail confirmation client : " . $e->getMessage());
        }

        return $this->smartResponse('/#contact', 'Votre demande de création de société a bien été envoyée ! Un email de confirmation vous a été adressé. Note : Si vous utilisez un iPhone, PILOTIX est également 100% accessible sur le Web (sur iPhone, Mac, PC, tablette) avec toutes ses fonctionnalités !');
    }
}
