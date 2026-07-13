<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DemandePrestataire;
use App\Models\Tenant;
use App\Models\Commission;
use App\Models\CommissionRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PartnerCommissionSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_registration_approval_auto_creation_and_commission_lifecycle(): void
    {
        // 1. Create a Super Admin for administrative actions
        $superAdmin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('PasswordAdmin123!'),
            'role' => 'super_admin',
            'actif' => true,
            'roles_secondaires' => [],
        ]);

        // 2. Submit partner application (acting as the applicant)
        $submitResponse = $this->post('/devenir-prestataire', [
            'nom' => 'Bello',
            'prenom' => 'Matinou',
            'email' => 'partner@example.com',
            'telephone' => '+22997000000',
            'entreprise' => 'Bellox Digital',
            'motivation' => 'Je souhaite collaborer avec vous.',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $submitResponse->assertStatus(302);
        
        $demande = DemandePrestataire::where('email', 'partner@example.com')->first();
        $this->assertNotNull($demande);
        $this->assertEquals('en_attente', $demande->statut);

        // 3. Super Admin approves the partner application
        $approveResponse = $this->actingAs($superAdmin)
            ->post("/admin/prestataires/{$demande->id}/valider");

        $approveResponse->assertStatus(302);

        // Verify partner user account structure
        $partnerUser = User::where('email', 'partner@example.com')->first();
        $this->assertNotNull($partnerUser);
        $this->assertEquals('prestataire', $partnerUser->role);
        $this->assertTrue(Hash::check('SecurePass123!', $partnerUser->password));

        // 4. Log in as partner and check initial dashboard (empty metrics)
        $partnerDashboardResponse = $this->actingAs($partnerUser)->get('/dashboard');
        $partnerDashboardResponse->assertStatus(200);
        $partnerDashboardResponse->assertSee('Tableau de bord Partenaire');
        $partnerDashboardResponse->assertSee('0'); // 0 created companies, 0 commission, etc.

        // 5. Partner creates a client company (tenant)
        $createTenantResponse = $this->actingAs($partnerUser)->post('/tenants', [
            'nom' => 'Client Company A',
            'marque' => 'CCA',
            'activite' => 'Retail',
            'pays' => 'BJ',
            'ville' => 'Cotonou',
            'telephone' => '+22960606060',
            'email' => 'clienta@example.com',
            'offre_code' => 'locale', // Choose Locale plan (commission matches 3995 F)
            'admin_name' => 'Admin User A',
            'admin_email' => 'admina@example.com',
            'admin_telephone' => '+22961616161',
            'admin_password' => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
        ]);

        $createTenantResponse->assertRedirect('/tenants');

        // Check database for created Tenant with correct partner association
        $tenant = Tenant::where('email', 'clienta@example.com')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals($partnerUser->id, $tenant->partenaire_id);

        // Check if commission record was automatically generated
        $commission = Commission::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($commission);
        $this->assertEquals($partnerUser->id, $commission->partenaire_id);
        $this->assertEquals(3995.00, $commission->montant);
        $this->assertEquals('en_attente', $commission->statut);

        // 6. Partner dashboard check (updated metrics with pending commission)
        $partnerDashboardResponse = $this->actingAs($partnerUser)->get('/dashboard');
        $partnerDashboardResponse->assertStatus(200);
        $partnerDashboardResponse->assertSee('Client Company A');
        $partnerDashboardResponse->assertSee('3 995 F');

        // 7. Super Admin updates commission status to paid (reglee)
        $payCommissionResponse = $this->actingAs($superAdmin)
            ->post("/admin/commissions/{$commission->id}/statut", [
                'statut' => 'reglee',
            ]);

        $payCommissionResponse->assertStatus(302);
        
        // Assert status was updated
        $this->assertEquals('reglee', $commission->fresh()->statut);

        // 8. Partner dashboard check (updated metrics with paid commission and history)
        $partnerDashboardResponse = $this->actingAs($partnerUser)->get('/dashboard');
        $partnerDashboardResponse->assertStatus(200);
        $partnerDashboardResponse->assertSee('Client Company A');
        $partnerDashboardResponse->assertSee('Réglé le');
    }
}
