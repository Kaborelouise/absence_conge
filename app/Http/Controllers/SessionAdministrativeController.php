<?php

namespace App\Http\Controllers;

use App\Models\SessionAdministrative;
use App\Models\DemandeConge;
use App\Models\DemandeAbsence;
use App\Models\DemandeJouissance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SessionAdministrativeController extends Controller
{

    public function index()
    {
        // Récupération de toutes les sessions administratives,classée par année décroissante
        $sessions = SessionAdministrative::orderByDesc('annee')->get();

        //Retourne ces info vers l'index de Session administrative
        return view('sessions_administratives.index', compact('sessions'));
    }
    

    // methode qu'on va appelé pour la création de la session
    public function create()
        {
            return view('sessions_administratives.create');
        }

    public function store(Request $request)
        {
            // Validation des informations saisies par l'utilisateur
            $request->validate(
                [
                    'date_debut' => 'required|date',
                    'date_fin'   => 'required|date|after:date_debut',
                ],
                [
                    'date_fin.after' => 'La date de fin doit être supérieure à la date de début.',
                ]
            );

            // Conversion des dates en objets Carbon
            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin   = Carbon::parse($request->date_fin);

            // Récupération automatique de l'année à partir de la date de début
            $annee = $dateDebut->year;

            // Vérifie que la date de début est supérieure ou égale au 1er janvier de l'année en cours
            $premierJourAnneeCourante = Carbon::create(now()->year, 1, 1);

            if ($dateDebut->lt($premierJourAnneeCourante)) {

                return back()
                    ->withErrors([
                        'date_debut' => "La date de début doit être supérieure ou égale au {$premierJourAnneeCourante->format('d/m/Y')}."
                    ])
                    ->withInput();
            }

            // Vérifie qu'une session n'existe pas déjà pour cette année
            if (SessionAdministrative::where('annee', $annee)->exists()) {

                return back()
                    ->withErrors([
                        'date_debut' => "Une session administrative existe déjà pour l'année {$annee}."
                    ])
                    ->withInput();
            }

            // Vérifie qu'aucune autre session ne chevauche les dates choisies
            if (SessionAdministrative::chevaucheUneSessionExistante($dateDebut, $dateFin)) {

                return back()
                    ->withErrors([
                        'date_debut' => 'Cette période chevauche une session administrative existante.'
                    ])
                    ->withInput();
            }

            // Vérifie que la session précédente est fermée avant d'autoriser la création
            $sessionPrecedente = SessionAdministrative::where('annee', $annee - 1)->first();

            if (
                $sessionPrecedente &&
                $sessionPrecedente->estOuverte()
            ) {

                return back()
                    ->withErrors([
                        'date_debut' => "Impossible de créer la session {$annee} tant que la session {$sessionPrecedente->annee} est encore ouverte."
                    ])
                    ->withInput();
            }

            // Création de la session (fermée par défaut)
            SessionAdministrative::create([

                // 'libelle' => "Session Administrative {$annee}",
                'annee' => $annee,

                'date_debut' => $dateDebut,

                'date_fin' => $dateFin,

                'ouverte' => false,

                'active_absence' => false,

                'active_conge' => false,

                'active_jouissance' => false,

                'soldes_reinitialises' => false,

                'created_by' => auth()->id(),
            ]);

            return redirect()
                ->route('sessions_administratives.index')
                ->with('success', "La session administrative {$annee} a été créée avec succès.");
        }


    public function ouvrir(SessionAdministrative $session)
        {
            // Vérifie si la session est déjà ouverte
            if ($session->estOuverte()) {

                return back()->with(
                    'error',
                    "La session {$session->annee} est déjà ouverte."
                );
            }

            // Vérifie que la session précédente est fermée
            $sessionPrecedente = SessionAdministrative::where(
                'annee',
                $session->annee - 1
            )->first();

            if (
                $sessionPrecedente &&
                $sessionPrecedente->estOuverte()
            ) {

                return back()->with(
                    'error',
                    "Impossible d'ouvrir la session {$session->annee} tant que la session {$sessionPrecedente->annee} est encore ouverte."
                );
            }

            // Ferme toutes les autres sessions afin qu'il n'y ait qu'une seule session ouverte dans l'application
            SessionAdministrative::query()->update([
                'ouverte' => false,
                'active_absence' => false,
                'active_conge' => false,
                'active_jouissance' => false,
            ]);

            // Ouvre la session sélectionnée
            $session->update([
                'ouverte' => true,
                'active_absence' => true,
                'active_conge' => true,
                'active_jouissance' => true,
            ]);
            $messageSoldes = '';

            if (! $session->soldes_reinitialises) {
                User::query()->update([
                    'solde_absence' => 10,
                    'solde_conge'   => 30,
                ]);

                $session->update(['soldes_reinitialises' => true]);

                $messageSoldes = ' Les soldes de congé (30 j) et d\'absence (10 j) '
                    . 'ont été réinitialisés pour tous les agents.';
            }

            return back()->with(
                'success',
                "La session {$session->annee} a été ouverte avec succès.{$messageSoldes}"
            );
        }

    public function fermer(SessionAdministrative $session)
        {
            // Vérifie si la session est déjà fermée
            if (! $session->estOuverte()) {

                return back()->with(
                    'error',
                    "La session {$session->annee} est déjà fermée."
                );
            }

            // Ferme la session
            $session->update([
                'ouverte' => false,
                'active_absence' => false,
                'active_conge' => false,
                'active_jouissance' => false,
            ]);

            return back()->with(
                'success',
                "La session {$session->annee} a été fermée avec succès."
            );
        }

    //fonction empêchant la supressant d'une session contenant des demandes, pour qu'unre
    public function destroy(SessionAdministrative $session)
        {
            if ($session->estOuverte()) {
                return back()->with(
                    'error',
                    'Fermez la session avant de la supprimer.'
                );
            }

           
            if (
                $session->demandeConges()->exists() ||
                $session->demandeAbsences()->exists() ||
                $session->demandeJouissances()->exists()
            ) {
                return back()->with(
                    'error',
                    'Impossible de supprimer une session contenant des demandes.'
                );
            }

            $session->delete();

            return redirect()
                ->route('sessions_administratives.index')
                ->with(
                    'success',
                    "La session {$session->annee} a été supprimée avec succès."
                );
        }

        
}