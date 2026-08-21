<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OffreController extends Controller
{
    /**
     * Données d'abonnement du tenant de l'utilisateur connecté.
     */
    protected function donneesOffre(?Tenant $tenant): array
    {
        // Compte sans espace client (ex : super-admin) : aucun abonnement à afficher.
        if (!$tenant) {
            return [
                'offre_code'     => 'admin',
                'offre_nom'      => 'Administrateur',
                'prix'           => null,
                'commission'     => null,
                'expires_at'     => null,
                'expires_iso'    => null,
                'jours_restants' => null,
                'actif'          => true,
                'est_vie'        => false,
                'est_admin'      => true,
                'features'       => [],
                'contact'        => [
                    'email'     => 'pilotixcontact@gmail.com',
                    'telephone' => '+229 01 46 86 25 36',
                    'whatsapp'  => '2290146862536',
                ],
            ];
        }

        $offre = DB::table('commission_rules')->where('code', $tenant->offre_code)->first();

        $joursRestants = $tenant->offre_expires_at
            ? (int) round(now()->diffInDays($tenant->offre_expires_at, false))
            : null;

        $noms = [
            'essentiel'     => 'Essentiel',
            'professionnel' => 'Professionnel',
            'entreprise'    => 'Entreprise',
            'locale'        => 'Locale',
        ];

        $features = [
            'Gestion des produits & stocks',
            'Ventes en gros & détail + factures',
            'Dépenses, clients & inventaires',
            'Mode Hors-Connexion & Sync auto',
        ];

        if ($tenant->hasCapability('import')) {
            $features[] = 'Gestion des importations & arrivages';
            $features[] = 'Calcul des coûts de revient & marges';
        }
        if ($tenant->hasCapability('multi_magasin')) {
            $features[] = 'Multi-magasins & dépôts';
        }
        if ($tenant->hasCapability('advanced_stats')) {
            $features[] = 'Statistiques avancées & suivi d’activité';
        }
        if ($tenant->hasCapability('multi_user')) {
            $features[] = 'Multi-postes & utilisateurs';
        }

        $contact = [
            'email'     => 'pilotixcontact@gmail.com',
            'telephone' => '+229 01 46 86 25 36',
            'whatsapp'  => '2290146862536',
        ];

        return [
            'offre_code'     => $tenant->offre_code,
            'offre_nom'      => $noms[$tenant->offre_code] ?? (string) $tenant->offre_code,
            'prix'           => $offre?->prix,
            'commission'     => $offre?->commission,
            'expires_at'     => $tenant->offre_expires_at?->format('d/m/Y'),
            'expires_iso'    => $tenant->offre_expires_at?->toDateString(),
            'jours_restants' => $joursRestants,
            'actif'          => $tenant->isOffreActive(),
            'est_vie'        => $tenant->offre_code === 'locale',
            'est_admin'      => false,
            'features'       => $features,
            'contact'        => $contact,
        ];
    }

    /**
     * Vue web "Mon offre".
     */
    public function show()
    {
        $tenant = Auth::user()->tenant;
        $data = $this->donneesOffre($tenant);

        return view('offre', compact('data'));
    }

    /**
     * API : détails de l'offre souscrite.
     */
    public function apiShow()
    {
        $tenant = Auth::user()->tenant;
        $data = $this->donneesOffre($tenant);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
