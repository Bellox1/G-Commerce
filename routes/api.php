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
use Illuminate\Support\Facades\Route;

// ─── Traitements Auth Publics ────────────────────────────────────────────
Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'apiSendResetCode']);
Route::post('/reset-password', [PasswordResetController::class, 'apiResetPassword']);
Route::post('/contact', [WelcomeController::class, 'submitContact'])->name('contact.submit');

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
        Route::post('tenants/{tenant}/magasins', [TenantController::class, 'storeMagasin'])->name('tenants.magasins.store');
        Route::delete('tenants/{tenant}/magasins/{magasin}', [TenantController::class, 'destroyMagasin'])->name('tenants.magasins.destroy');
    });

    // ─── Traitements Métier (Utilisateurs dans un tenant) ─────────────────
    Route::middleware('ensure_tenant')->group(function () {

        // ── Données (GET) — lecture pour web et mobile ──────────────────
        Route::get('/analytique',              [AnalytiqueController::class,  'index']);
        Route::get('/fournisseurs',            [FournisseurController::class, 'index']);
        Route::get('/produits',                [ProduitController::class,     'index']);
        Route::get('/clients',                 [ClientController::class,      'index']);
        Route::get('/magasins',                [MagasinController::class,     'index']);
        Route::get('/arrivages',               [ArrivageController::class,    'index']);
        Route::get('/arrivages/{arrivage}',    [ArrivageController::class,    'show']);
        Route::get('/ventes',                  [VenteController::class,       'index']);
        Route::get('/ventes/{vente}',          [VenteController::class,       'show']);
        Route::get('/dettes',                  [DetteController::class,       'index']);
        Route::get('/dettes/{dette}',          [DetteController::class,       'show']);
        Route::get('/stock',                   [StockController::class,       'index']);
        Route::get('/stock/mouvements',        [StockController::class,       'mouvements']);
        Route::get('/transferts',              [TransfertController::class,   'index']);
        Route::get('/transferts/{transfert}',  [TransfertController::class,   'show']);
        Route::get('/livraisons',              [LivraisonController::class,   'index']);
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

        // ── Arrivages ────────────────────────────────────────────────────
        Route::post('arrivages', [ArrivageController::class, 'store']);
        Route::put('arrivages/{arrivage}', [ArrivageController::class, 'update']);
        Route::delete('arrivages/{arrivage}', [ArrivageController::class, 'destroy']);
        Route::post('arrivages/{arrivage}/valider', [ArrivageController::class, 'valider'])->name('arrivages.valider');
        Route::put('arrivages/produit/{arrivageProduit}/prix-suggere', [ArrivageController::class, 'updatePrixSuggere'])->name('arrivages.produit.prix-suggere');

        // ── Stock ─────────────────────────────────────────────────────────
        Route::post('stock/ajuster', [StockController::class, 'ajuster']);

        // ── Transferts ───────────────────────────────────────────────────
        Route::post('transferts', [TransfertController::class, 'store']);

        // ── Ventes ───────────────────────────────────────────────────────
        Route::post('ventes', [VenteController::class, 'store']);
        Route::put('ventes/{vente}', [VenteController::class, 'update']);
        Route::delete('ventes/{vente}', [VenteController::class, 'destroy']);
        Route::post('ventes/{vente}/convertir-dette', [VenteController::class, 'convertirDette']);

        // ── Livraisons ───────────────────────────────────────────────────
        Route::put('livraisons/{vente}/statut', [LivraisonController::class, 'updateStatut'])->name('livraisons.update-statut');

        // ── Clients ───────────────────────────────────────────────────────
        Route::post('clients', [ClientController::class, 'store']);
        Route::put('clients/{client}', [ClientController::class, 'update']);
        Route::delete('clients/{client}', [ClientController::class, 'destroy']);

        // ── Dettes ────────────────────────────────────────────────────────
        Route::post('dettes', [DetteController::class, 'store']);
        Route::put('dettes/{dette}', [DetteController::class, 'update']);
        Route::delete('dettes/{dette}', [DetteController::class, 'destroy']);
        Route::post('dettes/{dette}/payer', [DetteController::class, 'enregistrerPaiement'])->name('dettes.payer');
        Route::put('dettes/{dette}/echeance', [DetteController::class, 'updateEcheance'])->name('dettes.echeance');

        // ── Employés ─────────────────────────────────────────────────────
        Route::post('employes', [EmployeController::class, 'store']);
        Route::put('employes/{employe}', [EmployeController::class, 'update']);
        Route::delete('employes/{employe}', [EmployeController::class, 'destroy']);
    });

    // ── Profil (accessible à tous les utilisateurs authentifiés, y compris super_admin) ──
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'password']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});
