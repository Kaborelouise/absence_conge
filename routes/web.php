<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DemandeAbsenceController;
use App\Http\Controllers\JustificatifAbsenceController;
use App\Http\Controllers\AvisAbsenceController;
use App\Http\Controllers\DemandeCongeController;
use App\Http\Controllers\AvisCongeController;
use App\Http\Controllers\DemandeJouissanceController;
use App\Http\Controllers\AvisJouissanceController;
use App\Http\Controllers\SessionAdministrativeController;
use App\Http\Controllers\AdminExportController;
use App\Http\Controllers\PasswordSetupController;
use App\Http\Controllers\Auth\MotDePasseObligatoireController;



// Auth routes générées par Breeze
// NE PAS TOUCHER
require __DIR__.'/auth.php';

// Définition du mot de passe suite à une invitation — accessible SANS authentification
Route::get('password-setup/{token}', [PasswordSetupController::class, 'create'])
    ->name('password.setup');

Route::post('password-setup', [PasswordSetupController::class, 'store'])
    ->name('password.setup.store');

Route::middleware('auth')->group(function () {

    // ACCUEIL
    Route::get('/', function () {
        return view('accueil');
    })->name('accueil');

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Administrateur
    Route::resource('roles', RoleController::class);
    Route::resource('directions', DirectionController::class);
    Route::resource('departements', DepartementController::class);
    Route::resource('utilisateurs', UserController::class);
    Route::resource('sessions_Administratives', \App\Http\Controllers\SessionAdministrativeController::class)
    ->only(['index', 'create', 'store', 'show']);

    // Demandes
    Route::resource('demande_absences', DemandeAbsenceController::class);
    Route::post('demande_absences/{id}/abandonner', [DemandeAbsenceController::class, 'abandonner'])
    ->name('demande_absences.abandonner');
    Route::resource('justificatifabsence', JustificatifAbsenceController::class)
     ->only(['create', 'store', 'destroy']);
    Route::get('demande_absences/{id}/telecharger', [DemandeAbsenceController::class, 'telecharger'])
        ->name('demande_absences.telecharger');

    Route::get('demande_absences/{id}/note_interim', [DemandeAbsenceController::class, 'telechargerNoteInterim'])
        ->name('demande_absences.note_interim');

    Route::resource('avis_absences', AvisAbsenceController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::post('demande_conges/compiler', [DemandeCongeController::class, 'compiler'])
    ->name('demande_conges.compiler');

    Route::post('demande_conges/decompiler', [DemandeCongeController::class, 'decompiler'])
        ->name('demande_conges.decompiler');

    Route::get('demande_conges/telecharger-decision', [DemandeCongeController::class, 'telechargerDecision'])
        ->name('demande_conges.telecharger_decision');

    Route::resource('demande_conges', DemandeCongeController::class);
    Route::resource('avis_conges', AvisCongeController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('demande_jouissances', DemandeJouissanceController::class);
    Route::resource('avis_jouissances', AvisJouissanceController::class)
        ->  only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::post('demande_jouissances/{id}/abandonner', [DemandeJouissanceController::class, 'abandonner'])
        ->name('demande_jouissances.abandonner');
 
    Route::resource('sessions_administratives', \App\Http\Controllers\SessionAdministrativeController::class)
            ->only(['index', 'create', 'store', 'show']);
    Route::patch(
        'sessions_administratives/{session}/ouvrir',
        [SessionAdministrativeController::class, 'ouvrir']
    )->name('sessions_administratives.ouvrir');

    Route::patch(
        'sessions_administratives/{session}/fermer',
        [SessionAdministrativeController::class, 'fermer']
    )->name('sessions_administratives.fermer');

    Route::post('sessions_Administratives/{id}/toggle-absence', 
        [SessionAdministrativeController::class, 'toggleAbsence'])
        ->name('sessions_Administratives.toggle_absence');

    Route::post('sessions_Administratives/{id}/toggle-conge',
        [SessionAdministrativeController::class, 'toggleConge'])
        ->name('sessions_Administratives.toggle_conge');

    Route::post('sessions_Administratives/{id}/toggle-jouissance',
        [SessionAdministrativeController::class, 'toggleJouissance'])
        ->name('sessions_Administratives.toggle_jouissance');

    //  routes pour la cloture de demande jouissance
    Route::post('demande_jouissances/{id}/upload-cessation', [DemandeJouissanceController::class, 'uploadCessation'])
    ->name('demande_jouissances.upload_cessation');

    Route::post('demande_jouissances/{id}/upload-prise-service', [DemandeJouissanceController::class, 'uploadPriseService'])
    ->name('demande_jouissances.upload_prise_service');

    Route::post('demande_jouissances/{id}/cloturer', [DemandeJouissanceController::class, 'cloturer'])
    ->name('demande_jouissances.cloturer');

    Route::get('demande_jouissances/{id}/telecharger-cessation', [DemandeJouissanceController::class, 'telechargerCessation'])
    ->name('demande_jouissances.telecharger_cessation');

     Route::get('demande_jouissances/{id}/telecharger-reprise', [DemandeJouissanceController::class, 'telechargerReprise'])
    ->name('demande_jouissances.telecharger_reprise');

    Route::get('demande_jouissances/{id}/telecharger-interim', [DemandeJouissanceController::class, 'telechargerInterim'])
    ->name('demande_jouissances.telecharger_interim');

    Route::post('demande_conges/{id}/abandonner', [DemandeCongeController::class, 'abandonner'])
    ->name('demande_conges.abandonner');

    Route::delete(
        '/sessions_Administratives/{session}',
        [SessionAdministrativeController::class, 'destroy']
    )->name('sessions_Administratives.destroy');

    Route::prefix('admin/export')->name('admin.export.')->group(function () {
        Route::get('users', [AdminExportController::class, 'users'])->name('users');
        Route::get('conges', [AdminExportController::class, 'conges'])->name('conges');
        Route::get('jouissances', [AdminExportController::class, 'jouissances'])->name('jouissances');
        Route::get('absences', [AdminExportController::class, 'absences'])->name('absences');
    });

    Route::get('notifications/{id}/lire', [\App\Http\Controllers\NotificationController::class, 'lire'])
    ->name('notifications.lire');

    Route::post('utilisateurs/{utilisateur}/renvoyer-invitation', [UserController::class, 'renvoyerInvitation'])
    ->name('utilisateurs.renvoyer_invitation');

    // Mot de passe temporairechangement obligatoire
    Route::get('/mot-de-passe/changer-obligatoire', [MotDePasseObligatoireController::class, 'create'])
        ->name('mot_de_passe.changer_obligatoire');

    Route::put('/mot-de-passe/changer-obligatoire', [MotDePasseObligatoireController::class, 'update'])
        ->name('mot_de_passe.changer_obligatoire.update');

    Route::get('parametres-documents', [\App\Http\Controllers\ParametreDocumentController::class, 'edit'])
    ->name('parametres_documents.edit');

    Route::put('parametres-documents', [\App\Http\Controllers\ParametreDocumentController::class, 'update'])
    ->name('parametres_documents.update');

});