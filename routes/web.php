<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\ArrivageController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\DetteController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\MagasinController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\DetteSocieteController;
use App\Http\Controllers\AnalytiqueController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PartenaireController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TresorerieController;
use App\Http\Controllers\Admin\DemandeController;
use App\Http\Controllers\Admin\OffreController as AdminOffreController;
use Illuminate\Support\Facades\Route;

// ─── Traitements Auth Web (Avec Sessions & Cookies) ────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode'])->name('password.email');

Route::get('/reset-password', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyCode'])->name('password.verify');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Page d'accueil publique
Route::get('/', [WelcomeController::class, 'index']);

// Contact (public)
Route::post('/contact', [WelcomeController::class, 'submitContact'])->name('contact.submit');

// Partenaires (public)
Route::get('/partenaires', [PartenaireController::class, 'index'])->name('partenaires');

// Page légales (public)
Route::get('/conditions', function() { return view('conditions'); })->name('conditions');
Route::get('/confidentialite', function() { return view('confidentialite'); })->name('confidentialite');

// Page de téléchargement (public)
Route::get('/download', function() { return view('download'); })->name('download');

// Onboarding mobile (public — redirige vers dashboard si connecté)
Route::get('/onboarding', function() {
    if (Auth::check()) return redirect()->route('dashboard');
    return view('onboarding');
})->name('onboarding');

// Systèmes de Souscription et Partenariat (Public)
        Route::get('/devenir-partenaire', [SubscriptionController::class, 'showPrestataireForm'])->name('prestataire.form');

        Route::post('/partenaires/candidature', [PartenaireController::class, 'submit'])->name('prestataire.submit');

// Page de test avec identifiants
Route::get('/test', function() {
    return view('test');
});

// ─── App (protégé par auth) ───────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/faq', [FaqController::class, 'index'])->name('faq');

    // Sociétés / Tenants (Super Admin uniquement)
    Route::middleware('super_admin')->group(function () {
        Route::resource('tenants', TenantController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);
        Route::post('tenants/{tenant}/magasins', [TenantController::class, 'storeMagasin'])->name('tenants.magasins.store');
        Route::delete('tenants/{tenant}/magasins/{magasin}', [TenantController::class, 'destroyMagasin'])->name('tenants.magasins.destroy');
        
        // Validation des prestataires
        Route::get('/admin/prestataires', [DemandeController::class, 'indexPrestataires'])->name('admin.prestataires');
        Route::get('/admin/prestataires/{id}', [DemandeController::class, 'showPrestataire'])->name('admin.prestataires.show');
        Route::post('/admin/prestataires/{id}/valider', [DemandeController::class, 'validerPrestataire'])->name('admin.prestataires.valider');
        Route::post('/admin/prestataires/{id}/rejeter', [DemandeController::class, 'rejeterPrestataire'])->name('admin.prestataires.rejeter');
        
        // Gestion des commissions
        Route::get('/admin/commissions', [DemandeController::class, 'indexCommissions'])->name('admin.commissions');
        Route::post('/admin/commissions/{id}/statut', [DemandeController::class, 'updateCommissionStatut'])->name('admin.commissions.statut');

        // Gestion des prix d'abonnement & commissions des offres
        Route::get('/admin/offres', [AdminOffreController::class, 'index'])->name('admin.offres');
        Route::post('/admin/offres/paliers', [AdminOffreController::class, 'updatePaliers'])->name('admin.offres.paliers.update');
        Route::post('/admin/offres/{code}', [AdminOffreController::class, 'update'])->name('admin.offres.update');
        Route::post('/tenants/{tenant}/renouveler', [TenantController::class, 'renewOffer'])->name('tenants.renew');
        Route::post('/tenants/{tenant}/changer-offre', [TenantController::class, 'changeOffer'])->name('tenants.changeOffer');
        Route::post('/tenants/{tenant}/prolonger', [TenantController::class, 'extendOffer'])->name('tenants.extendOffer');
        Route::post('/tenants/{tenant}/pause', [TenantController::class, 'pauseOffer'])->name('tenants.pauseOffer');
        Route::post('/tenants/{tenant}/reprendre', [TenantController::class, 'resumeOffer'])->name('tenants.resumeOffer');
    });

    // ─── Routes métier (utilisateurs avec un tenant associé) ───────────
    Route::middleware(['ensure_tenant', 'offer_active'])->group(function () {

        // Produits
        Route::resource('produits', ProduitController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);
        Route::get('produits/{produit}/stocks', [ProduitController::class, 'stockEdit'])->name('produits.stocks.edit');
        Route::put('produits/{produit}/stocks', [ProduitController::class, 'stockUpdate'])->name('produits.stocks.update');

        // Magasins
        Route::get('magasins', [MagasinController::class, 'index'])->name('magasins.index');
        Route::post('magasins', [MagasinController::class, 'store'])->name('magasins.store');
        Route::put('magasins/{magasin}', [MagasinController::class, 'update'])->name('magasins.update');

        // Arrivages (Importation — Offre Professionnel+)
        Route::middleware('plan:import')->group(function () {
            Route::resource('arrivages', ArrivageController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);
            Route::post('arrivages/{arrivage}/valider', [ArrivageController::class, 'valider'])->name('arrivages.valider');
            Route::put('arrivages/produit/{arrivageProduit}/prix-suggere', [ArrivageController::class, 'updatePrixSuggere'])->name('arrivages.produit.prix-suggere');
        });

        // Stock
        Route::get('stock',            [StockController::class, 'index'])->name('stock.index');
        Route::get('stock/mouvements', [StockController::class, 'mouvements'])->name('stock.mouvements');
        Route::post('stock/ajuster', [StockController::class, 'ajuster'])->name('stock.ajuster');

        // Transferts (Multi-magasins — Offre Professionnel+)
        Route::resource('transferts', TransfertController::class)->only(['index', 'create', 'show', 'edit', 'update']);
        Route::middleware('plan:multi_magasin')->group(function () {
            Route::post('transferts', [TransfertController::class, 'store'])->name('transferts.store');
            Route::post('transferts/{transfert}/reception', [TransfertController::class, 'receptionner'])->name('transferts.reception');
        });

        // Ventes
        Route::resource('ventes', VenteController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);

        // Livraisons
        Route::get('livraisons', [LivraisonController::class, 'index'])->name('livraisons.index');
        Route::get('livraisons/{vente}', [LivraisonController::class, 'show'])->name('livraisons.show');
        Route::put('livraisons/{vente}/statut', [LivraisonController::class, 'updateStatut'])->name('livraisons.update-statut');

        // Clients
        Route::resource('clients', ClientController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);

        // Dettes
        Route::resource('dettes', DetteController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::post('dettes/{dette}/payer', [DetteController::class, 'enregistrerPaiement'])->name('dettes.payer');
        Route::put('dettes/{dette}/echeance', [DetteController::class, 'updateEcheance'])->name('dettes.echeance');

        // Fournisseurs
        Route::resource('fournisseurs', FournisseurController::class)->only(['store', 'update', 'destroy']);

        // Dettes Société (dettes que la société doit aux fournisseurs)
        Route::get('dettes-societe', [DetteSocieteController::class, 'index'])->name('dettes-societe.index');
        Route::get('dettes-societe/{dette}', [DetteSocieteController::class, 'show'])->name('dettes-societe.show');
        Route::post('dettes-societe', [DetteSocieteController::class, 'store'])->name('dettes-societe.store');
        Route::post('dettes-societe/{dette}/payer', [DetteSocieteController::class, 'enregistrerPaiement'])->name('dettes-societe.payer');
        Route::delete('dettes-societe/{dette}', [DetteSocieteController::class, 'destroy'])->name('dettes-societe.destroy');

        // Trésorerie (Caisse — mouvements entrées / sorties / CA du jour)
        Route::get('tresoreries', [TresorerieController::class, 'index'])->name('tresoreries.index');
        Route::post('tresoreries', [TresorerieController::class, 'store'])->name('tresoreries.store');
        Route::delete('tresoreries/{tresorerie}', [TresorerieController::class, 'destroy'])->name('tresoreries.destroy');

        // Analytique / Analyse avancée (GET uniquement — Offre Professionnel+)
        Route::middleware('plan:advanced_stats')->group(function () {
            Route::get('analytique', [AnalytiqueController::class, 'index'])->name('analytique');
        });

        // Employés
        Route::resource('employes', EmployeController::class)->only(['index', 'create', 'edit', 'store', 'update', 'destroy']);
        Route::post('employes/{employe}/toggle-active', [EmployeController::class, 'toggleActive'])->name('employes.toggle-active');

        // Dépense du dashboard
        Route::post('/dashboard/depense', [DashboardController::class, 'storeDepense'])->name('dashboard.depense.store');
    });

    // Offre & Notifications (accessibles même si l'offre est expirée)
    Route::middleware('ensure_tenant')->group(function () {
        Route::get('/offre', [OffreController::class, 'show'])->name('offre');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::post('/notifications/mark-all', [NotificationController::class, 'markAllRead'])->name('notifications.markAll');

        // Dépenses (liste complète + suppression)
        Route::get('/depenses', [DepenseController::class, 'index'])->name('depenses.index');
        Route::delete('/depenses/{depense}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
    });

    // Profil (accessible à tous les utilisateurs authifiés, y compris super_admin)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Espace partenaire (accessible à tous les users ayant le rôle prestataire)
    Route::get('/prestataire/dashboard', [\App\Http\Controllers\PrestataireSpaceController::class, 'dashboard'])->name('prestataire.dashboard');
    Route::get('/prestataire/mes-societes', [\App\Http\Controllers\PrestataireSpaceController::class, 'mesSocietes'])->name('prestataire.mes-societes');
    Route::get('/prestataire/societes/creer', [\App\Http\Controllers\PrestataireSpaceController::class, 'createTenant'])->name('prestataire.tenants.create');
    Route::post('/prestataire/societes', [\App\Http\Controllers\PrestataireSpaceController::class, 'storeTenant'])->name('prestataire.tenants.store');
    Route::get('/prestataire/societes/{tenant}/modifier', [\App\Http\Controllers\PrestataireSpaceController::class, 'editTenant'])->name('prestataire.tenants.edit');
    Route::put('/prestataire/societes/{tenant}', [\App\Http\Controllers\PrestataireSpaceController::class, 'updateTenant'])->name('prestataire.tenants.update');
    Route::post('/prestataire/societes/{tenant}/renouveler', [\App\Http\Controllers\PrestataireSpaceController::class, 'renewOffer'])->name('prestataire.tenants.renew');
    Route::post('/prestataire/societes/{tenant}/changer-offre', [\App\Http\Controllers\PrestataireSpaceController::class, 'changeOffer'])->name('prestataire.tenants.changeOffer');
    Route::post('/prestataire/societes/{tenant}/prolonger', [\App\Http\Controllers\PrestataireSpaceController::class, 'extendOffer'])->name('prestataire.tenants.extendOffer');
    Route::post('/prestataire/societes/{tenant}/pause', [\App\Http\Controllers\PrestataireSpaceController::class, 'pauseOffer'])->name('prestataire.tenants.pauseOffer');
    Route::post('/prestataire/societes/{tenant}/reprendre', [\App\Http\Controllers\PrestataireSpaceController::class, 'resumeOffer'])->name('prestataire.tenants.resumeOffer');
});
