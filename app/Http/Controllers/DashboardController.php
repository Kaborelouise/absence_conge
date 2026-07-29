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
    /**
     * ================================================================
     * NOTE IMPORTANTE SUR LES HYPOTHÈSES PRISES (à valider) :
     *
     * 1) Section "Agent" du PDF : le texte dit littéralement "Demandes de
     *    congé" (pas "jouissance"), contrairement aux sections 2/3/4 qui
     *    parlent explicitement de "jouissance de congé". On respecte donc
     *    le PDF au pied de la lettre : le bloc congé de l'Agent utilise
     *    DemandeConge, pas DemandeJouissance.
     *
     * 2) DemandeConge n'a pas de circuit d'avis (juste une compilation par
     *    l'Agent RH, cf. workflow slide 6 : pas de "rejet" prévu). Donc :
     *      - "validées"  = estCompilee() === true
     *      - "en cours"  = !estCompilee() && !abandonnee
     *      - "rejetées"  = statut === 'rejetee' (0 dans la pratique tant que
     *                       personne ne fixe ce statut ; gardé pour rester
     *                       fidèle au PDF sans casser si un rejet est ajouté
     *                       plus tard)
     *
     * 3) "Clôturées" pour les absences : ajouté via la nouvelle colonne
     *    cloturee_at (cf. migration add_cloturee_at_to_demande_absences_table
     *    et DemandeAbsence::estCloturee()), confirmé par le workflow PPTX
     *    (étape "Clôture" existe bien pour les absences).
     *
     * 4) "Calendrier ou diagramme de Gantt" est rendu comme un diagramme de
     *    Gantt (barres horizontales par date, via Chart.js) pour tous les
     *    rôles concernés (Chef Dpt, Resp. Direction, RH/SG/DG/PCA) — c'est
     *    la lecture la plus fidèle de "calendrier" pour des demandes ayant
     *    une date de début et une date de fin.
     *
     * 5) Administrateur / "statut confirmé" : faute de précision dans le
     *    PDF, on utilise : jamais connecté = last_login_at === null,
     *    confirmé = last_login_at !== null (l'utilisateur s'est déjà
     *    authentifié au moins une fois).
     * ================================================================
     */

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

    /**
     * ================================================================
     * 1. TABLEAU DE BORD AGENT
     * ================================================================
     */
    private function dashboardAgent(User $user)
    {
        $role = $user->role->libelle;

        // Solde de jours d'absence : on retire les jours déjà validés
        // cette année du quota annuel (10 jours, valeur métier existante).
        $joursAbsenceUtilises = DemandeAbsence::where('user_id', $user->id)
            ->where('statut', 'validee')
            ->whereYear('date_debut', now()->year)
            ->get()
            ->sum(fn($d) => $d->nombreJours());

        $soldeAbsence = max(0, 10 - $joursAbsenceUtilises);
        $soldeConge   = $user->solde_conge ?? 30;

        // Demandes de congé de l'agent (DemandeConge — voir note 1 en haut
        // du fichier sur le choix du modèle).
        $mesConges = DemandeConge::where('user_id', $user->id)->get();

        $congeTotal    = $mesConges->count();
        $congeRejetees = $mesConges->where('statut', 'rejetee')->count();
        $congeValidees = $mesConges->filter(fn($d) => $d->estCompilee())->count();
        $congeEnCours  = $mesConges->filter(fn($d) =>
            !$d->estCompilee() && !$d->abandonnee && $d->statut !== 'rejetee'
        )->count();

        // Demandes d'autorisation d'absence de l'agent.
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

    /**
     * ================================================================
     * 2. TABLEAU DE BORD CHEF DE DÉPARTEMENT
     * ================================================================
     */
    private function dashboardChefDepartement(User $user)
    {
        $role          = $user->role->libelle;
        $departementId = $user->departement_id;

        $nbAgents = User::where('departement_id', $departementId)->count();

        // --- Congé (DemandeJouissance) -----------------------------------
        $congesDept = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId))
            ->with(['user', 'avis'])
            ->get();

        $congeStats = $this->statsGenerales($congesDept);

        // Suivi : agents actuellement en congé (comptage d'agents distincts,
        // pas de demandes, au cas où un agent aurait plusieurs lignes).
        $agentsEnCongeListe = $congesDept->filter(fn($d) =>
            $d->statut === 'validee'
            && Carbon::parse($d->date_debut)->lte(now())
            && Carbon::parse($d->date_fin)->gte(now())
        );
        $nbAgentsEnConge = $agentsEnCongeListe->pluck('user_id')->unique()->count();

        // Alerte : demandes en attente d'avis PAR LE CHEF DE DÉPARTEMENT
        // précisément (pas juste "en attente" au sens large).
        $alerteConge = $congesDept->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'Chef de Département'
        )->count();

        $congeCalendrier   = $this->timelineData($congesDept, fn($d) => $d->nombre_jour);
        $congeParAgent     = $this->parAgent($congesDept);
        $congeParAnnee     = $this->parAnnee($congesDept);

        // --- Absence (DemandeAbsence) ------------------------------------
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

    /**
     * ================================================================
     * 3. TABLEAU DE BORD RESPONSABLE DE DIRECTION
     * (même structure que Chef de Département, mais à l'échelle direction)
     * ================================================================
     */
    private function dashboardResponsableDirection(User $user)
    {
        $role       = $user->role->libelle;
        $directionId = $user->departement->direction_id;

        $nbAgents = User::whereHas('departement', fn($q) =>
            $q->where('direction_id', $directionId))->count();

        // --- Congé ---------------------------------------------------------
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

        $alerteConge = $congesDir->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'Responsable Direction'
        )->count();

        $congeCalendrier = $this->timelineData($congesDir, fn($d) => $d->nombre_jour);
        $congeParAgent   = $this->parAgent($congesDir);
        $congeParAnnee   = $this->parAnnee($congesDir);

        // --- Absence ---------------------------------------------------------
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

    /**
     * ================================================================
     * 4. TABLEAU DE BORD AGENT RH / SG / DG / PCA
     * ================================================================
     */
    private function dashboardDirectionGenerale(User $user)
    {
        $role = $user->role->libelle;

        // --- Globale ---------------------------------------------------------
        $nbAgents = User::whereHas('role', fn($q) =>
            $q->where('libelle', '!=', 'Administrateur'))->count();

        $agentsParDirection = Direction::withCount(['departements as nb_agents' => fn($q) =>
            $q->join('users', 'departements.id', '=', 'users.departement_id')
        ])->get()->map(fn($d) => [
            'label' => $d->libelle_court,
            'count' => $d->nb_agents,
        ]);

        // --- Congé (DemandeJouissance) ---------------------------------------
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
        $alertesCongeRH = $tousConges->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'Agent RH'
        )->count();

        $alertesCongeSG = $tousConges->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'SG'
        )->count();

        $alertesCongeDG = $tousConges->filter(fn($d) =>
            !in_array($d->statut, ['validee', 'rejetee'])
            && $d->prochainActeur() === 'DG'
        )->count();

        $congeStats = $this->statsGenerales($tousConges);

        $directions = Direction::with('departements')->get();

        // Générale par direction : Total / Rejetées / En cours / Validées /
        // Clôturées — les 5 diagrammes circulaires exigés par le PDF.
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

        // --- Absence (DemandeAbsence) -----------------------------------------
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

        // AVANT : nbAbsencesCloturees était figé à 0 avec un commentaire
        // disant que les absences n'ont pas de clôture. C'était inexact
        // (cf. workflow PPTX, étape "Clôture") — corrigé grâce à
        // DemandeAbsence::estCloturee().
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

    /**
     * ================================================================
     * 5. TABLEAU DE BORD ADMINISTRATEUR
     * ================================================================
     */
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

        // ------------------------------------------------------------------
        // Liste des utilisateurs + statut + dernière authentification +
        // filtre par direction (le filtre est appliqué côté JS dans la vue,
        // pour éviter un aller-retour serveur ; on transmet tous les
        // utilisateurs avec leur direction en attribut).
        // ------------------------------------------------------------------
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

        // ------------------------------------------------------------------
        // Agents sans demande de congé, PAR ANNÉE.
        // AVANT : filtré uniquement sur l'année en cours. Le PDF demande une
        // vraie ventilation par année, donc on accepte un paramètre ?annee=
        // (menu déroulant dans la vue qui recharge la page avec ce filtre),
        // par défaut l'année en cours.
        // ------------------------------------------------------------------
        $anneeSelectionnee = (int) $request->query('annee', now()->year);

        // Bornes raisonnables pour le sélecteur d'année : depuis la première
        // demande de congé enregistrée jusqu'à l'année en cours.
        $premiereAnnee = DemandeConge::min('created_at');
        $anneeDebut    = $premiereAnnee ? Carbon::parse($premiereAnnee)->year : now()->year;
        $anneesDisponibles = range(now()->year, $anneeDebut);

        $agentsSansConge = User::whereHas('role', fn($q) =>
                $q->whereNotIn('libelle', ['Administrateur']))
            ->whereDoesntHave('demandeConges', fn($q) =>
                $q->whereYear('created_at', $anneeSelectionnee))
            ->with('departement.direction')
            ->get();

        // Journal d'audit : identifie déjà utilisateur / modèle / action CRUD.
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

    // ====================================================================
    // MÉTHODES PRIVÉES RÉUTILISABLES
    // ====================================================================

    /**
     * Calcule les 5 compteurs "Générale" exigés par le PDF pour n'importe
     * quelle collection de demandes (congé ou absence) : total, rejetées,
     * en cours, validées, clôturées.
     *
     * Fonctionne aussi bien avec DemandeJouissance qu'avec DemandeAbsence
     * car les deux exposent désormais estCloturee().
     */
    private function statsGenerales(Collection $demandes): array
    {
        return [
            'total'     => $demandes->count(),
            'rejetees'  => $demandes->where('statut', 'rejetee')->count(),
            'en_cours'  => $demandes->whereIn('statut', ['en_attente', 'en_cours'])->count(),
            'validees'  => $demandes->where('statut', 'validee')->count(),
            'cloturees' => $demandes->filter(fn($d) => $d->estCloturee())->count(),
        ];
    }

    /**
     * Regroupe une collection de demandes par agent (nom complet) → nombre.
     */
    private function parAgent(Collection $demandes): Collection
    {
        return $demandes->groupBy(fn($d) => $d->user->nom . ' ' . $d->user->prenom)->map->count();
    }

    /**
     * Regroupe une collection de demandes par année de création → nombre.
     */
    private function parAnnee(Collection $demandes): Collection
    {
        return $demandes->groupBy(fn($d) => Carbon::parse($d->created_at)->year)
            ->sortKeysDesc()
            ->map->count();
    }

    /**
     * Construit les données d'un diagramme de Gantt / calendrier :
     * une ligne par demande (sauf rejetées, comme demandé par le PDF),
     * avec date de début, date de fin, agent, direction et statut —
     * exploitable directement par Chart.js (barres flottantes) côté vue.
     *
     * $dureeCallback n'est pas utilisé pour le tracé (les dates suffisent)
     * mais est conservé pour un affichage de la durée en info-bulle.
     */
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