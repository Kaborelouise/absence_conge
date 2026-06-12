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

    // ADMINISTRATION
    Route::resource('roles', RoleController::class);
    Route::resource('directions', DirectionController::class);
    Route::resource('departements', DepartementController::class);
    Route::resource('utilisateurs', UserController::class);

    // DEMANDES
    Route::resource('demande_absences', DemandeAbsenceController::class);
    Route::resource('justificatifabsence', JustificatifAbsenceController::class)
        ->only(['create', 'store', 'destroy']);
    Route::resource('avis_absences', AvisAbsenceController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('demande_conges', DemandeCongeController::class);
    Route::resource('avis_conges', AvisCongeController::class)
        ->only(['index', 'create','store', 'edit', 'update', 'destroy']);
    Route::resource('demande_jouissances', DemandeJouissanceController::class);
    Route::resource('avis_jouissances', AvisJouissanceController::class)
        ->only(['create', 'index', 'store', 'edit', 'update', 'destroy']);
        
});