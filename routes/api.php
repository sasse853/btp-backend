<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvenantController;
use App\Http\Controllers\Api\ChantierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EquipementController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\MateriauController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UtilisateurController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\PresenceController;
use App\Http\Controllers\Api\RapportController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Authentification de broadcasting (Pusher) protegee par Sanctum.
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::prefix('v1')->group(function () {

    // --- Routes publiques ---
    Route::post('login', [AuthController::class, 'login']);

    // --- Routes protegees (token Sanctum) ---
    Route::middleware('auth:sanctum')->group(function () {
        // Gestion des utilisateurs (admin)
        Route::get('utilisateurs', [UtilisateurController::class, 'index'])->middleware('role:admin');
        Route::post('utilisateurs', [UtilisateurController::class, 'store'])->middleware('role:admin');
        Route::patch('utilisateurs/{utilisateur}/toggle-actif', [UtilisateurController::class, 'toggleActif'])->middleware('role:admin');
        Route::patch('utilisateurs/{utilisateur}/reinitialiser-mot-de-passe', [UtilisateurController::class, 'reinitialiserMotDePasse'])->middleware('role:admin');
        
        // Changement de mot de passe (tous les utilisateurs connectés)
        Route::post('mon-profil/changer-mot-de-passe', [UtilisateurController::class, 'changerMotDePasse']);

        // Compte courant
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Tableau de bord (KPIs admin / chef)
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Liste des chefs de chantier (pour l'affectation cote admin)
        Route::get('chefs', [ChantierController::class, 'chefs'])->middleware('role:admin');

        // Chantiers
        Route::apiResource('chantiers', ChantierController::class);

        // Personnel
        Route::apiResource('personnel', PersonnelController::class);

        // Presences (+ saisie en lot)
        Route::post('presences/batch', [PresenceController::class, 'batch']);
        Route::apiResource('presences', PresenceController::class);

        // Materiaux & equipements
        Route::apiResource('materiaux', MateriauController::class);
        Route::apiResource('equipements', EquipementController::class);

        // Finances (+ validation admin)
        Route::patch('finances/{finance}/valider', [FinanceController::class, 'valider'])
            ->middleware('role:admin');
        Route::apiResource('finances', FinanceController::class);

        // Avenants (+ validation admin)
        Route::patch('avenants/{avenant}/valider', [AvenantController::class, 'valider'])
            ->middleware('role:admin');
        Route::apiResource('avenants', AvenantController::class);

        // Documents (+ validation admin)
        Route::patch('documents/{document}/valider', [DocumentController::class, 'valider'])
            ->middleware('role:admin');
        Route::get('documents/{document}/download', [DocumentController::class, 'download']);
        Route::apiResource('documents', DocumentController::class);

        // Messagerie (fil par chantier)
        Route::get('chantiers/{chantier}/messages', [MessageController::class, 'index']);
        Route::post('chantiers/{chantier}/messages', [MessageController::class, 'store']);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/non-lues', [NotificationController::class, 'nonLues']);
        Route::patch('notifications/{notification}/lue', [NotificationController::class, 'marquerLue']);
        Route::patch('notifications/tout-lu', [NotificationController::class, 'toutMarquerLu']);

        // Rapport PDF client
        Route::post('chantiers/{chantier}/rapport', [RapportController::class, 'generer']);
    });
});
