<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Notifications\InvitationCompteNotification;
use Illuminate\Support\Facades\Password;
use App\Models\DemandeJouissance;
use App\Notifications\DepartCongeImminent;
use App\Notifications\DepartCongeAnnonce;
use Illuminate\Support\Facades\Notification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $utilisateursNonActives = User::whereNull('last_login_at')
        ->where('created_at', '<=', now()->subDays(3))
        ->get();

    foreach ($utilisateursNonActives as $utilisateur) {
        $token = Password::createToken($utilisateur);
        $utilisateur->notify(new InvitationCompteNotification($token));
    }
})->daily();

Schedule::call(function () {
    $dansSeptJours = now()->addDays(7)->toDateString();

    $demandes = DemandeJouissance::where('statut', 'validee')
        ->whereDate('date_debut', $dansSeptJours)
        ->get();

    foreach ($demandes as $demande) {
        $demande->user->notify(new DepartCongeImminent($demande));
    }
})->daily();

Schedule::call(function () {
    $aujourdhui = now()->toDateString();

    $demandes = DemandeJouissance::where('statut', 'validee')
        ->whereDate('date_debut', $aujourdhui)
        ->get();

    foreach ($demandes as $demande) {
        Notification::send(User::all(), new DepartCongeAnnonce($demande));
    }
})->daily();