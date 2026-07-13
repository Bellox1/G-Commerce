<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\DemandeController;
use Illuminate\Support\Facades\Route;

// ─── Traitements Auth Web (Avec Sessions & Cookies) ────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode'])->name('password.email');

Route::get('/reset-password', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Page d'accueil publique
Route::get('/', [WelcomeController::class, 'index']);

// Contact (public)
Route::post('/contact', [WelcomeController::class, 'submitContact'])->name('contact.submit');

// Partenaires (public)
Route::get('/partenaires', [PartenaireController::class, 'index'])->name('partenaires');

// Pages légales (public)
Route::get('/conditions', function() { return view('conditions'); })->name('conditions');
Route::get('/confidentialite', function() { return view('confidentialite'); })->name('confidentialite');

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
        Route::post('/tenants/{tenant}/renouveler', [TenantController::class, 'renewOffer'])->name('tenants.renew');
    });

    // ─── Routes métier (utilisateurs avec un tenant associé) ───────────
    Route::middleware(['ensure_tenant', 'offer_active'])->group(function () {

        // Produits
        Route::resource('produits', ProduitController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);

        // Magasins
        Route::get('magasins', [MagasinController::class, 'index'])->name('magasins.index');
        Route::post('magasins', [MagasinController::class, 'store'])->name('magasins.store');
        Route::put('magasins/{magasin}', [MagasinController::class, 'update'])->name('magasins.update');

        // Arrivages
        Route::resource('arrivages', ArrivageController::class)->only(['index', 'create', 'show', 'edit', 'store', 'update', 'destroy']);
        Route::post('arrivages/{arrivage}/valider', [ArrivageController::class, 'valider'])->name('arrivages.valider');
        Route::put('arrivages/produit/{arrivageProduit}/prix-suggere', [ArrivageController::class, 'updatePrixSuggere'])->name('arrivages.produit.prix-suggere');

        // Stock
        Route::get('stock',            [StockController::class, 'index'])->name('stock.index');
        Route::get('stock/mouvements', [StockController::class, 'mouvements'])->name('stock.mouvements');
        Route::post('stock/ajuster', [StockController::class, 'ajuster'])->name('stock.ajuster');

        // Transferts
        Route::resource('transferts', TransfertController::class)->only(['index', 'create', 'show', 'store']);

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

        // Analytique / Analyse avancée (GET uniquement)
        Route::get('analytique', [AnalytiqueController::class, 'index'])->name('analytique');

        // Employés
        Route::resource('employes', EmployeController::class)->only(['index', 'create', 'edit', 'store', 'update', 'destroy']);

        // Dépense du dashboard
        Route::post('/dashboard/depense', [DashboardController::class, 'storeDepense'])->name('dashboard.depense.store');
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
});
