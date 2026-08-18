<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DemandeAbsence;
use App\Models\DemandeConge;
use App\Models\DemandeJouissance;
use App\Models\Direction;
use App\Models\Departement;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        if ($role === 'Agent') {
            return $this->dashboardAgent($user);
        }

        if ($role === 'Chef de Département') {
            return $this->dashboardChefDepartement($user);
        }

        if ($role === 'Responsable Direction') {
            return $this->dashboardResponsableDirection($user);
        }

        if (in_array($role, ['Agent RH', 'SG', 'DG', 'PCA'])) {
            return $this->dashboardDirectionGenerale($user);
        }

        if ($role === 'Administrateur') {
            return $this->dashboardAdministrateur($request);
        }

        return view('dashboard', compact('role', 'user'));
    }

    // tableau de bord d'un agent
    private function dashboardAgent(User $user)
    {
        $role = $user->role->libelle;

        // Solde de jours d'absence : on retire les jours déjà validés pour l'année en cours
        $joursAbsenceUtilises = DemandeAbsence::where('user_id', $user->id)
            ->where('statut', 'validee')
            ->whereYear('date_debut', now()->year)
            ->get()
            ->sum(fn($d) => $d->nombreJours());

        $soldeAbsence = max(0, 10 - $joursAbsenceUtilises);
        $soldeConge   = $user->solde_conge ?? 30;

        // Demandes de jouissance de congé de l'agent
 
        $mesJouissances = DemandeJouissance::where('user_id', $user->id)->get();

        $congeTotal    = $mesJouissances->count();
        $congeRejetees = $mesJouissances->where('statut', 'rejetee')->count();
        $congeValidees = $mesJouissances->where('statut', 'validee')->count();
        $congeEnCours  = $mesJouissances->where('statut', 'en_attente')
            ->where('abandonnee', false)->count();

        // Demandes d'autorisation d'absence de l'agent
        $mesAbsences = DemandeAbsence::where('user_id', $user->id)->get();

        $absenceTotal    = $mesAbsences->count();
        $absenceRejetees = $mesAbsences->where('statut', 'rejetee')->count();
        $absenceValidees = $mesAbsences->where('statut', 'validee')->count();
        $absenceEnCours  = $mesAbsences->whereIn('statut', ['en_attente', 'en_cours'])->count();

        return view('dashboard', compact(
            'role', 'user', 'soldeAbsence', 'soldeConge',
            'congeTotal', 'congeRejetees', 'congeValidees', 'congeEnCours',
            'absenceTotal', 'absenceRejetees', 'absenceValidees', 'absenceEnCours'
        ));
    }

    //  tableau de bord chef de département
    private function dashboardChefDepartement(User $user)
    {
        $role          = $user->role->libelle;
        $departementId = $user->departement_id;

        $nbAgents = User::where('departement_id', $departementId)->count();

        //  DemandeJouissance
        $congesDept = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId))
            ->with(['user', 'avis'])
            ->get();

        $congeStats = $this->statsGenerales($congesDept);

        // Suivi agents actuellement en congé 
        $agentsEnCongeListe = $congesDept->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        );
        $nbAgentsEnConge = $agentsEnCongeListe->pluck('user_id')->unique()->count();

        // Alerte demandes en attente d'avis PAR LE chef de département
    
        $alerteConge = $congesDept->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'chef_departement'
        )->count();

        $congeCalendrier   = $this->timelineData($congesDept, fn($d) => $d->nombre_jour);
        $congeParAgent     = $this->parAgent($congesDept);
        $congeParAnnee     = $this->parAnnee($congesDept);

        //  Absence (DemandeAbsence) 
        $absencesDept = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId))
            ->with(['user', 'avisAbsence'])
            ->get();

        $absenceStats = $this->statsGenerales($absencesDept);

        $agentsEnAbsenceListe = $absencesDept->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        );
        $nbAgentsEnAbsence = $agentsEnAbsenceListe->pluck('user_id')->unique()->count();

        $alerteAbsence = $absencesDept->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'chef_departement'
        )->count();

        $absenceCalendrier = $this->timelineData($absencesDept, fn($d) => $d->nombreJours());
        $absenceParAgent   = $this->parAgent($absencesDept);
        $absenceParAnnee   = $this->parAnnee($absencesDept);

        return view('dashboard', compact(
            'role', 'user', 'nbAgents',
            'congeStats', 'nbAgentsEnConge', 'alerteConge',
            'congeCalendrier', 'congeParAgent', 'congeParAnnee',
            'absenceStats', 'nbAgentsEnAbsence', 'alerteAbsence',
            'absenceCalendrier', 'absenceParAgent', 'absenceParAnnee'
        ));
    }


    // Tableau de bord d'un responsable de direction
    private function dashboardResponsableDirection(User $user)
    {
        $role       = $user->role->libelle;
        $directionId = $user->departement->direction_id;

        $nbAgents = User::whereHas('departement', fn($q) =>
            $q->where('direction_id', $directionId))->count();

        //  Congé 
        $congesDir = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId))
            ->with(['user', 'avis'])
            ->get();

        $congeStats = $this->statsGenerales($congesDir);

        $nbAgentsEnConge = $congesDir->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        )->pluck('user_id')->unique()->count();

        // (CORRIGÉ : 'Responsable Direction' -> 'responsable_direction')
        $alerteConge = $congesDir->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'responsable_direction'
        )->count();

        $congeCalendrier = $this->timelineData($congesDir, fn($d) => $d->nombre_jour);
        $congeParAgent   = $this->parAgent($congesDir);
        $congeParAnnee   = $this->parAnnee($congesDir);

        //  Absence 
        $absencesDir = DemandeAbsence::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId))
            ->with(['user', 'avisAbsence'])
            ->get();

        $absenceStats = $this->statsGenerales($absencesDir);

        $nbAgentsEnAbsence = $absencesDir->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        )->pluck('user_id')->unique()->count();

        $alerteAbsence = $absencesDir->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'responsable_direction'
        )->count();

        $absenceCalendrier = $this->timelineData($absencesDir, fn($d) => $d->nombreJours());
        $absenceParAgent   = $this->parAgent($absencesDir);
        $absenceParAnnee   = $this->parAnnee($absencesDir);

        return view('dashboard', compact(
            'role', 'user', 'nbAgents',
            'congeStats', 'nbAgentsEnConge', 'alerteConge',
            'congeCalendrier', 'congeParAgent', 'congeParAnnee',
            'absenceStats', 'nbAgentsEnAbsence', 'alerteAbsence',
            'absenceCalendrier', 'absenceParAgent', 'absenceParAnnee'
        ));
    }

    // TABLEAU DE BORD AGENT RH / SG / DG / PCA


    private function dashboardDirectionGenerale(User $user)
    {
        $role = $user->role->libelle;

        // Globale 
        $nbAgents = User::whereHas('role', fn($q) =>
            $q->where('libelle', '!=', 'Administrateur'))->count();

        $agentsParDirection = Direction::withCount(['departements as nb_agents' => fn($q) =>
            $q->join('users', 'departements.id', '=', 'users.departement_id')
        ])->get()->map(fn($d) => [
            'label' => $d->libelle_court,
            'count' => $d->nb_agents,
        ]);

        //  DemandeJouissance 
        $tousConges = DemandeJouissance::with('user.departement.direction', 'avis')->get();

        $agentsEnConge = $tousConges->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        );
        $nbEnConge = $agentsEnConge->pluck('user_id')->unique()->count();

        $congesParDirection = $agentsEnConge
            ->groupBy(fn($d) => $d->user->departement->direction->libelle_court ?? '—')
            ->map->count();

        // Alertes par étape du circuit (vérification RH, avis SG, avis DG)
        // (CORRIGÉ : comparaisons alignées sur le snake_case renvoyé par prochainActeur())
        $alertesCongeRH = $tousConges->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'agent_rh'
        )->count();

        $alertesCongeSG = $tousConges->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'sg'
        )->count();

        $alertesCongeDG = $tousConges->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'dg'
        )->count();

        $congeStats = $this->statsGenerales($tousConges);

        $directions = Direction::with('departements')->get();

        // Générale par direction  Total / Rejetées / En cours / Validées 
        $congesStatParDirection = $directions->map(function ($dir) use ($tousConges) {
            $sousEnsemble = $tousConges->filter(fn($d) =>
                ($d->user->departement->direction_id ?? null) === $dir->id
            );
            $s = $this->statsGenerales($sousEnsemble);
            return [
                'label'    => $dir->libelle_court,
                'total'    => $s['total'],
                'rejetes'  => $s['rejetees'],
                'en_cours' => $s['en_cours'],
                'valides'  => $s['validees'],
                'clotures' => $s['cloturees'],
            ];
        });

        $congeCalendrier      = $this->timelineData($tousConges, fn($d) => $d->nombre_jour);
        $congeParAgent        = $this->parAgent($tousConges);
        $congeParAnnee        = $this->parAnnee($tousConges);
        $congeParDirectionListe = $tousConges->groupBy(fn($d) =>
            $d->user->departement->direction->libelle_court ?? '—')->map->count();

        $tousAbsences = DemandeAbsence::with('user.departement.direction', 'avisAbsence')->get();

        $agentsEnAbsence = $tousAbsences->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        );
        $nbEnAbsence = $agentsEnAbsence->pluck('user_id')->unique()->count();

        $alertesAbsenceRH = $tousAbsences->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'agent_rh'
        )->count();

        $alertesAbsenceSG = $tousAbsences->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'sg'
        )->count();

        $alertesAbsenceDG = $tousAbsences->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'dg'
        )->count();

        $absenceStats = $this->statsGenerales($tousAbsences);

        $absenceCalendrier = $this->timelineData($tousAbsences, fn($d) => $d->nombreJours());
        $absenceParAgent   = $this->parAgent($tousAbsences);
        $absenceParAnnee   = $this->parAnnee($tousAbsences);

        return view('dashboard', compact(
            'role', 'user',
            'nbAgents', 'agentsParDirection',
            'nbEnConge', 'agentsEnConge', 'congesParDirection',
            'alertesCongeRH', 'alertesCongeSG', 'alertesCongeDG',
            'congeStats', 'congesStatParDirection', 'directions',
            'congeCalendrier', 'congeParAgent', 'congeParAnnee', 'congeParDirectionListe',
            'nbEnAbsence', 'agentsEnAbsence',
            'alertesAbsenceRH', 'alertesAbsenceSG', 'alertesAbsenceDG',
            'absenceStats',
            'absenceCalendrier', 'absenceParAgent', 'absenceParAnnee'
        ));
    }

    //  Tableau de bord de l'administrateur
    // (INCHANGÉ : ici DemandeConge est le bon modèle — "congé administratif",
    // prérequis distinct de la jouissance de congé, voir DemandeJouissanceController::store())

    private function dashboardAdministrateur(Request $request)
    {
        $role = 'Administrateur';
        $user = auth()->user();

        $totalUsers           = User::count();
        $totalAdministrateurs = User::whereHas('role', fn($q) =>
            $q->where('libelle', 'Administrateur'))->count();
        $connectesAujourdhui  = User::whereDate('updated_at', today())->count();
        $jamaisConnectes      = User::whereNull('last_login_at')->count();

        $userParRole = User::with('role')->get()
            ->groupBy('role.libelle')->map->count();

        $listeUtilisateurs = User::with('role', 'departement.direction')
            ->get()
            ->map(fn($u) => [
                'nom'       => $u->nom . ' ' . $u->prenom,
                'role'      => $u->role->libelle ?? '—',
                'direction' => $u->departement->direction->libelle_court ?? '—',
                'statut'    => $u->last_login_at ? 'Confirmé' : 'Jamais connecté',
                'derniere_connexion' => $u->last_login_at
                    ? $u->last_login_at->format('d/m/Y H:i')
                    : '—',
            ]);

        $toutesDirections = Direction::orderBy('libelle_court')->pluck('libelle_court');
        $anneeSelectionnee = (int) $request->query('annee', now()->year);

        $premiereAnnee = DemandeConge::min('created_at');
        $anneeDebut    = $premiereAnnee ? Carbon::parse($premiereAnnee)->year : now()->year;
        $anneesDisponibles = range(now()->year, $anneeDebut);

        $agentsSansConge = User::whereHas('role', fn($q) =>
                $q->whereNotIn('libelle', ['Administrateur']))
            ->whereDoesntHave('demandeConges', fn($q) =>
                $q->whereYear('created_at', $anneeSelectionnee))
            ->with('departement.direction')
            ->get();

        // Journal d'audit identifie déjà utilisateur 
        $journalActions = ActivityLog::with('user')->latest()->take(20)->get();

        return view('dashboard', compact(
            'role', 'user',
            'totalUsers', 'totalAdministrateurs',
            'connectesAujourdhui', 'jamaisConnectes',
            'userParRole',
            'listeUtilisateurs', 'toutesDirections',
            'agentsSansConge', 'anneeSelectionnee', 'anneesDisponibles',
            'journalActions'
        ));
    }


    private function statsGenerales(Collection $demandes): array
    {
        return [
            'total'     => $demandes->count(),
            'rejetees'  => $demandes->where('statut', 'rejetee')->count(),
            // (CORRIGÉ : on exclut désormais les demandes abandonnées du compteur "en cours",
            // pour rester cohérent avec la logique appliquée côté Agent)
            'en_cours'  => $demandes->whereIn('statut', ['en_attente', 'en_cours'])
                                     ->where('abandonnee', false)->count(),
            'validees'  => $demandes->where('statut', 'validee')->count(),
            'cloturees' => $demandes->filter(fn($d) => $d->estCloturee())->count(),
        ];
    }
    private function parAgent(Collection $demandes): Collection
    {
        return $demandes->groupBy(fn($d) => $d->user->nom . ' ' . $d->user->prenom)->map->count();
    }

   
    private function parAnnee(Collection $demandes): Collection
    {
        return $demandes->groupBy(fn($d) => Carbon::parse($d->created_at)->year)
            ->sortKeysDesc()
            ->map->count();
    }

    private function timelineData(Collection $demandes, ?callable $dureeCallback = null): array
    {
        return $demandes
            ->whereNotIn('statut', ['rejetee'])
            ->map(fn($d) => [
                'agent'     => $d->user->nom . ' ' . $d->user->prenom,
                'direction' => $d->user->departement->direction->libelle_court ?? '—',
                'debut'     => Carbon::parse($d->date_debut)->format('Y-m-d'),
                'fin'       => Carbon::parse($d->date_fin)->format('Y-m-d'),
                'statut'    => $d->statut,
                'duree'     => $dureeCallback ? $dureeCallback($d) : null,
            ])
            ->values()
            ->toArray();
    }
}