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
use App\Http\Controllers\SessionAdministrateuristrativeController;


// Auth routes générées par Breeze
// NE PAS TOUCHER
require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {

    // ACCUEIL
    Route::get('/', function () {
        return view('accueil');
    })->name('accueil');
    // ->name('accueil') : donne le nom 'accueil' à cette route
    // Maintenant route('accueil') fonctionnera

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // AdministrateurISTRATION
    Route::resource('roles', RoleController::class);
    Route::resource('directions', DirectionController::class);
    Route::resource('departements', DepartementController::class);
    Route::resource('utilisateurs', UserController::class);
    Route::resource('sessions_Administrateuristratives', \App\Http\Controllers\SessionAdministrateuristrativeController::class)
    ->only(['index', 'create', 'store', 'show']);

    // DEMANDES
    Route::resource('demande_absences', DemandeAbsenceController::class);
    Route::post('demande_absences/{id}/abandonner', [DemandeAbsenceController::class, 'abandonner'])
    ->name('demande_absences.abandonner');
    Route::resource('justificatifabsence', JustificatifAbsenceController::class)
     ->only(['create', 'store', 'destroy']);
    Route::get('demande_absences/{id}/telecharger', [DemandeAbsenceController::class, 'telecharger'])
        ->name('demande_absences.telecharger');
 
    
       
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
 
    Route::resource('sessions_Administrateuristratives', \App\Http\Controllers\SessionAdministrateuristrativeController::class)
        ->only(['index', 'create', 'store', 'show']);

    Route::post('sessions_Administrateuristratives/{id}/toggle-absence', 
        [SessionAdministrateuristrativeController::class, 'toggleAbsence'])
        ->name('sessions_Administrateuristratives.toggle_absence');

    Route::post('sessions_Administrateuristratives/{id}/toggle-conge',
        [SessionAdministrateuristrativeController::class, 'toggleConge'])
        ->name('sessions_Administrateuristratives.toggle_conge');

    Route::post('sessions_Administrateuristratives/{id}/toggle-jouissance',
        [SessionAdministrateuristrativeController::class, 'toggleJouissance'])
        ->name('sessions_Administrateuristratives.toggle_jouissance');






    // Ajout routes pour la clôture de demande jouissance
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

    Route::post('demande_conges/{id}/abandonner', [DemandeCongeController::class, 'abandonner'])
    ->name('demande_conges.abandonner');

    
});