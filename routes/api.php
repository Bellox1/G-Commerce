<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AnalytiqueController;
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
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DetteSocieteController;
use App\Http\Controllers\TresorerieController;
use App\Http\Controllers\Admin\DemandeController;
use Illuminate\Support\Facades\Route;

// ─── Traitements Auth Publics ────────────────────────────────────────────
Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
        Route::post('/contact', [WelcomeController::class, 'submitContact']);

// Taux de change en temps réel (public : info non sensible)
Route::get('/taux-change', [ArrivageController::class, 'tauxLive'])->name('taux.change');

// ─── Traitements de l'App (Protégés) ──────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    
    // Déconnexion
    Route::post('/logout', [LoginController::class, 'logout']);

    // Profil utilisateur connecté
    Route::get('/profile', [ProfileController::class, 'show']);

    // Module Dépense Dashboard
    Route::post('/dashboard/depense', [DashboardController::class, 'storeDepense']);

    // Sociétés / Tenants (Super Admin)
    Route::middleware('super_admin')->group(function () {
        Route::get('tenants', [TenantController::class, 'index']);
        Route::get('tenants/{tenant}', [TenantController::class, 'show']);
        Route::post('tenants', [TenantController::class, 'store']);
        Route::put('tenants/{tenant}', [TenantController::class, 'update']);
        Route::delete('tenants/{tenant}', [TenantController::class, 'destroy']);
        Route::post('tenants/{tenant}/magasins', [TenantController::class, 'storeMagasin']);
        Route::delete('tenants/{tenant}/magasins/{magasin}', [TenantController::class, 'destroyMagasin']);

        // ─── Administration : prestataires & commissions ─────────────
        Route::get('prestataires', [DemandeController::class, 'indexPrestataires']);
        Route::get('prestataires/{id}', [DemandeController::class, 'showPrestataire']);
        Route::post('prestataires/{id}/valider', [DemandeController::class, 'validerPrestataire']);
        Route::post('prestataires/{id}/rejeter', [DemandeController::class, 'rejeterPrestataire']);
        Route::get('commissions', [DemandeController::class, 'indexCommissions']);
        Route::post('commissions/{id}/statut', [DemandeController::class, 'updateCommissionStatut']);
    });

    // ─── Traitements Métier (Utilisateurs dans un tenant) ─────────────────
    Route::middleware('ensure_tenant')->group(function () {

        // ── Données (GET) — lecture pour web et mobile ──────────────────
        Route::get('/dashboard',               [DashboardController::class,   'index']);
        // ── Analytique (Stats avancées — Offre Professionnel+) ───────────
        Route::middleware('plan:advanced_stats')->group(function () {
            Route::get('/analytique',              [AnalytiqueController::class,  'index']);
        });
        Route::get('/fournisseurs',            [FournisseurController::class, 'index']);
        Route::get('/produits',                [ProduitController::class,     'index']);
        Route::get('/produits/{produit}/mouvements', [ProduitController::class, 'mouvements']);
        Route::get('/produits/{produit}',      [ProduitController::class,     'show']);
        Route::get('/clients',                 [ClientController::class,      'index']);
        Route::get('/clients/{client}',        [ClientController::class,      'show']);
        Route::get('/magasins',                [MagasinController::class,     'index']);
        Route::get('/arrivages',               [ArrivageController::class,    'index']);
        Route::get('/arrivages/{arrivage}',    [ArrivageController::class,    'show']);
        Route::get('/ventes',                  [VenteController::class,       'index']);
        Route::get('/ventes/{vente}',          [VenteController::class,       'show']);
        Route::get('/dettes',                  [DetteController::class,       'index']);
        Route::get('/dettes/{dette}',          [DetteController::class,       'show']);
        Route::get('/stock',                   [StockController::class,       'index']);
        Route::get('/stock/mouvements',        [StockController::class,       'mouvements']);
        Route::get('/transferts/form',          [TransfertController::class,   'form']);
        Route::get('/transferts',              [TransfertController::class,   'index']);
        Route::get('/transferts/{transfert}',  [TransfertController::class,   'show']);
        Route::get('/transferts/{transfert}/edit', [TransfertController::class, 'edit']);
        Route::put('/transferts/{transfert}',  [TransfertController::class,   'update']);
        Route::post('/transferts/{transfert}/reception', [TransfertController::class, 'receptionner']);
        Route::get('/livraisons',              [LivraisonController::class,   'index']);
        Route::get('/livraisons/{livraison}',  [LivraisonController::class,   'show']);
        Route::get('/employes',                [EmployeController::class,     'index']);

        // ── Produits ─────────────────────────────────────────────────────
        Route::post('produits', [ProduitController::class, 'store']);
        Route::put('produits/{produit}', [ProduitController::class, 'update']);
        Route::delete('produits/{produit}', [ProduitController::class, 'destroy']);

        // ── Fournisseurs ─────────────────────────────────────────────────
        Route::post('fournisseurs', [FournisseurController::class, 'store']);
        Route::put('fournisseurs/{fournisseur}', [FournisseurController::class, 'update']);
        Route::delete('fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy']);

        // ── Magasins ─────────────────────────────────────────────────────
        Route::post('magasins', [MagasinController::class, 'store']);
        Route::put('magasins/{magasin}', [MagasinController::class, 'update']);

        // ── Arrivages (Importation — Offre Professionnel+) ─────────────────
        Route::middleware('plan:import')->group(function () {
            Route::post('arrivages', [ArrivageController::class, 'store']);
            Route::put('arrivages/{arrivage}', [ArrivageController::class, 'update']);
            Route::delete('arrivages/{arrivage}', [ArrivageController::class, 'destroy']);
            Route::post('arrivages/{arrivage}/valider', [ArrivageController::class, 'valider']);
            Route::put('arrivages/produit/{arrivageProduit}/prix-suggere', [ArrivageController::class, 'updatePrixSuggere']);
        });

        // ── Stock ─────────────────────────────────────────────────────────
        Route::post('stock/ajuster', [StockController::class, 'ajuster']);

        // ── Transferts (Multi-magasins — Offre Professionnel+) ────────────
        Route::middleware('plan:multi_magasin')->group(function () {
            Route::post('transferts', [TransfertController::class, 'store']);
            Route::post('transferts/{transfert}/reception', [TransfertController::class, 'receptionner']);
        });

        // ── Ventes ───────────────────────────────────────────────────────
        Route::post('ventes', [VenteController::class, 'store']);
        Route::put('ventes/{vente}', [VenteController::class, 'update']);
        Route::delete('ventes/{vente}', [VenteController::class, 'destroy']);

        // ── Livraisons ───────────────────────────────────────────────────
        Route::put('livraisons/{vente}/statut', [LivraisonController::class, 'updateStatut']);

        // ── Clients ───────────────────────────────────────────────────────
        Route::post('clients', [ClientController::class, 'store']);
        Route::put('clients/{client}', [ClientController::class, 'update']);
        Route::delete('clients/{client}', [ClientController::class, 'destroy']);

        // ── Dettes ────────────────────────────────────────────────────────
        Route::post('dettes', [DetteController::class, 'store']);
        Route::put('dettes/{dette}', [DetteController::class, 'update']);
        Route::delete('dettes/{dette}', [DetteController::class, 'destroy']);
        Route::post('dettes/{dette}/payer', [DetteController::class, 'enregistrerPaiement'])->name('api.dettes.payer');
        Route::put('dettes/{dette}/echeance', [DetteController::class, 'updateEcheance'])->name('api.dettes.echeance');

        // ── Dettes Société ────────────────────────────────────────────────
        Route::get('dettes-societe', [DetteSocieteController::class, 'index']);
        Route::get('dettes-societe/{dette}', [DetteSocieteController::class, 'show']);
        Route::post('dettes-societe', [DetteSocieteController::class, 'store']);
        Route::post('dettes-societe/{dette}/payer', [DetteSocieteController::class, 'enregistrerPaiement']);
        Route::delete('dettes-societe/{dette}', [DetteSocieteController::class, 'destroy']);

        // ── Trésorerie (CA réel + mouvements d'argent) ──────────────────────
        Route::get('tresoreries', [TresorerieController::class, 'index'])->name('api.tresoreries.index');
        Route::post('tresoreries', [TresorerieController::class, 'store']);
        Route::delete('tresoreries/{tresorerie}', [TresorerieController::class, 'destroy']);

        // ── Employés ─────────────────────────────────────────────────────
        Route::post('employes', [EmployeController::class, 'store']);
        Route::put('employes/{employe}', [EmployeController::class, 'update']);
        Route::post('employes/{employe}/toggle-active', [EmployeController::class, 'toggleActive']);
        Route::delete('employes/{employe}', [EmployeController::class, 'destroy']);

        // ── Offre & Notifications (accessibles même si l'offre est expirée) ──
        Route::get('offre', [OffreController::class, 'apiShow']);
        Route::get('notifications', [NotificationController::class, 'apiIndex']);
        Route::post('notifications/mark-all', [NotificationController::class, 'apiMarkAllRead']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'apiMarkRead']);

        // ── Dépenses (liste + suppression) ──
        Route::get('depenses', [DepenseController::class, 'index']);
        Route::delete('depenses/{depense}', [DepenseController::class, 'destroy']);
    });

    // ── Profil (accessible à tous les utilisateurs authentifiés, y compris super_admin) ──
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'password']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});
