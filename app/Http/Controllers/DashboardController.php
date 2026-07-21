<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DemandeAbsence;
use App\Models\DemandeConge;
use App\Models\DemandeJouissance;
use App\Models\Direction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        // ============================================================
        // TABLEAU DE BORD Agent
        // Statistiques personnelles de l'Agent connecté
        // ============================================================
        if ($role === 'Agent') {
            // Solde absence calculé dynamiquement (Option A)
            $joursAbsenceUtilises = DemandeAbsence::where('user_id', $user->id)
                ->where('statut', 'validee')
                ->whereYear('date_debut', now()->year)
                ->get()
                ->sum(fn($d) => Carbon::parse($d->date_debut)
                    ->diffInDays(Carbon::parse($d->date_fin)) + 1);

            $soldeAbsence = max(0, 10 - $joursAbsenceUtilises);
            $soldeConge   = $user->solde_conge ?? 30;

            // Demandes de congé de l'Agent
            $mesConges = DemandeConge::where('user_id', $user->id)->get();

            // Demandes d'absence de l'Agent
            $mesAbsences = DemandeAbsence::where('user_id', $user->id)->get();

            // Demandes de jouissance de l'Agent
            $mesJouissances = DemandeJouissance::where('user_id', $user->id)->get();

            // Dernières demandes toutes catégories confondues
            $dernieresDemandes = $this->dernieresDemandes($user->id);

            // Évolution des demandes sur 12 mois (pour le graphique)
            $evolutionMois = $this->evolutionMoisAgent($user->id);

            return view('dashboard', compact(
                'role', 'user',
                'soldeAbsence', 'soldeConge',
                'mesConges', 'mesAbsences', 'mesJouissances',
                'dernieresDemandes', 'evolutionMois'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD CHEF DE DÉPARTEMENT
        // Statistiques du département de l'Agent connecté
        // ============================================================
        if ($role === 'Chef de Département' || $user->est_responsable_departement) {
            $departementId = $user->departement_id;

            // Nombre d'Agents dans le département
            $nbAgents = User::where('departement_id', $departementId)->count();

            // Demandes de congé du département
            $congesDept = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->get();

            // Demandes d'absence du département
            $absencesDept = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->get();

            // Alertes : demandes en attente de l'avis du chef
            $alertesConge   = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'en_attente')->count();

            $alertesAbsence = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'en_attente')->count();

            // Agents actuellement en congé (jouissance validée et en cours)
            $AgentsEnConge = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user')
             ->get();

            // Agents en absence
            $AgentsEnAbsence = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user')
             ->get();

            // Évolution des demandes sur 6 mois
            $evolutionMois = $this->evolutionMoisDept($departementId);

            // Liste détaillée des demandes par Agent
            $demandesParAgent = User::where('departement_id', $departementId)
                ->with(['demandeAbsences', 'demandeJouissances'])
                ->get();

            return view('dashboard', compact(
                'role', 'user',
                'nbAgents', 'congesDept', 'absencesDept',
                'alertesConge', 'alertesAbsence',
                'AgentsEnConge', 'AgentsEnAbsence',
                'evolutionMois', 'demandesParAgent'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD RESPONSABLE DE DIRECTION
        // Statistiques de toute la direction
        // ============================================================
        if ($role === 'Responsable Direction') {
            $directionId = $user->departement->direction_id;

            // Nombre d'Agents dans la direction
            $nbAgents = User::whereHas('departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->count();

            // Demandes de congé de la direction
            $congesDir = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->get();

            // Demandes d'absence de la direction
            $absencesDir = DemandeAbsence::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->get();

            // Alertes : en attente d'avis du responsable
            $alertesConge   = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'en_cours')->count();

            $alertesAbsence = DemandeAbsence::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'en_cours')->count();

            // Agents en congé dans la direction
            $AgentsEnConge = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user.departement')
             ->get();

            // Répartition par département
            $repartitionDepts = $this->repartitionParDept($directionId);

            // Évolution sur 6 mois
            $evolutionMois = $this->evolutionMoisDirection($directionId);

            return view('dashboard', compact(
                'role', 'user',
                'nbAgents', 'congesDir', 'absencesDir',
                'alertesConge', 'alertesAbsence',
                'AgentsEnConge', 'repartitionDepts', 'evolutionMois'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD RH / SG / DG / PCA
        // Vue globale de toute la structure
        // ============================================================
        if (in_array($role, ['Agent RH', 'SG', 'DG', 'PCA'])) {
            $nbAgents     = User::count();
            $nbDirections = Direction::count();

            // Toutes les demandes de jouissance (congé)
            $tousConges   = DemandeJouissance::all();
            $tousAbsences = DemandeAbsence::all();

            // Agents actuellement en congé
            $nbEnConge   = DemandeJouissance::where('statut', 'validee')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->count();

            $nbEnAbsence = DemandeAbsence::where('statut', 'validee')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->count();

            // Alertes par étape du circuit
            $alertesRH = DemandeAbsence::where('statut', 'en_cours')
                ->whereDoesntHave('avisAbsence', fn($q) =>
                    $q->where('type', 'Agent RH')
                )->count()
                + DemandeJouissance::where('statut', 'en_cours')
                ->whereDoesntHave('avis', fn($q) =>
                    $q->where('type', 'Agent RH')
                )->count();

            $alertesSG = DemandeAbsence::where('statut', 'en_cours')
                ->whereHas('avisAbsence', fn($q) =>
                    $q->where('type', 'Agent RH')->where('avis', 'favorable')
                )
                ->whereDoesntHave('avisAbsence', fn($q) =>
                    $q->where('type', 'SG')
                )->count();

            $alertesDG = DemandeAbsence::where('statut', 'en_cours')
                ->whereHas('avisAbsence', fn($q) =>
                    $q->where('type', 'SG')->where('avis', 'favorable')
                )
                ->whereDoesntHave('avisAbsence', fn($q) =>
                    $q->where('type', 'DG')
                )->count();

            // Total alertes
            $totalAlertes = $alertesRH + $alertesSG + $alertesDG;

            // Demandes par direction
            $demandesParDirection = Direction::withCount([
                'departements as nb_conges' => fn($q) =>
                    $q->join('users', 'departements.id', '=', 'users.departement_id')
                      ->join('demande_jouissances', 'users.id', '=', 'demande_jouissances.user_id'),
                'departements as nb_absences' => fn($q) =>
                    $q->join('users', 'departements.id', '=', 'users.departement_id')
                      ->join('demande_absences', 'users.id', '=', 'demande_absences.user_id'),
            ])->get();

            // Agents en congé avec info de retour
            $AgentsEnConge = DemandeJouissance::with('user.departement.direction')
                ->where('statut', 'validee')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->latest()
                ->take(5)
                ->get();

            // Dernières demandes toutes catégories
            $dernieresDemandes = $this->dernieresDemandes();

            // Évolution sur 12 mois
            $evolutionConges   = $this->evolutionMoiSGlobal('conge');
            $evolutionAbsences = $this->evolutionMoiSGlobal('absence');

            return view('dashboard', compact(
                'role', 'user',
                'nbAgents', 'nbDirections',
                'tousConges', 'tousAbsences',
                'nbEnConge', 'nbEnAbsence',
                'alertesRH', 'alertesSG', 'alertesDG', 'totalAlertes',
                'demandesParDirection', 'AgentsEnConge',
                'dernieresDemandes',
                'evolutionConges', 'evolutionAbsences'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD AdministrateurISTRATEUR
        // Gestion complète + journal d'audit
        // ============================================================
        if ($role === 'Administrateur') {
            $totalUsers        = User::count();
            $totalAdministrateurs       = User::whereHas('role', fn($q) => $q->where('libelle', 'Administrateur'))->count();

            // Utilisateurs connectés aujourd'hui
            $connectesAujourdhui = User::whereDate('updated_at', today())->count();

            // Jamais connectés
            $jamaisConnectes = User::whereNull('remember_token')->count();

            // Utilisateurs par rôle (pour le diagramme)
            $userParRole = User::with('role')
                ->get()
                ->groupBy('role.libelle')
                ->map->count();

            // Liste des Agents n'ayant pas soumis de demande de congé cette année
            $AgentsSansConge = User::whereHas('role', fn($q) =>
                $q->whereNotIn('libelle', ['Administrateur', 'Agent RH', 'SG', 'DG', 'PCA', 'Chef de Département','Responsable Direction'])
            )->whereDoesntHave('demandeConges', fn($q) =>
                $q->whereYear('created_at', now()->year)
            )->with('departement.direction')
             ->get();

            // Dernières demandes pour l'aperçu global
            $dernieresDemandes = $this->dernieresDemandes();

            return view('dashboard', compact(
                'role', 'user',
                'totalUsers', 'totalAdministrateurs',
                'connectesAujourdhui', 'jamaisConnectes',
                'userParRole', 'AgentsSansConge',
                'dernieresDemandes'
            ));
        }

        // Fallback : vue vide
        return view('dashboard', compact('role', 'user'));
    }

    // ================================================================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ================================================================

    // Dernières demandes toutes catégories (optionnel : filtré par user)
    private function dernieresDemandes(?int $userId = null): array
    {
        $absences = DemandeAbsence::with('user.departement.direction')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->latest()->take(5)->get()
            ->map(fn($d) => [
                'type'    => 'Absence',
                'Agent'   => $d->user->nom . ' ' . $d->user->prenom,
                'dir'     => $d->user->departement->direction->libelle_court ?? '—',
                'periode' => Carbon::parse($d->date_debut)->format('d/m/Y')
                           . ' - ' . Carbon::parse($d->date_fin)->format('d/m/Y'),
                'duree'   => Carbon::parse($d->date_debut)->diffInDays($d->date_fin) + 1 . 'j',
                'statut'  => $d->statut,
            ]);

        $jouissances = DemandeJouissance::with('user.departement.direction')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->latest()->take(5)->get()
            ->map(fn($d) => [
                'type'    => 'Congé',
                'Agent'   => $d->user->nom . ' ' . $d->user->prenom,
                'dir'     => $d->user->departement->direction->libelle_court ?? '—',
                'periode' => Carbon::parse($d->date_debut)->format('d/m/Y')
                           . ' - ' . Carbon::parse($d->date_fin)->format('d/m/Y'),
                'duree'   => $d->nombre_jour . 'j',
                'statut'  => $d->statut,
            ]);

        return $absences->concat($jouissances)
            ->sortByDesc('periode')
            ->take(6)
            ->values()
            ->toArray();
    }

    // Évolution mensuelle pour un Agent (12 derniers mois)
    private function evolutionMoisAgent(int $userId): array
    {
        $mois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date    = now()->subMonths($i);
            $mois[]  = [
                'label'    => $date->locale('fr')->isoFormat('MMM'),
                'validees' => DemandeAbsence::where('user_id', $userId)
                    ->where('statut', 'validee')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'en_cours' => DemandeAbsence::where('user_id', $userId)
                    ->where('statut', 'en_cours')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'rejetees' => DemandeAbsence::where('user_id', $userId)
                    ->where('statut', 'rejetee')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $mois;
    }

    // Évolution mensuelle pour un département (6 derniers mois)
    private function evolutionMoisDept(int $deptId): array
    {
        $mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $mois[] = [
                'label'   => $date->locale('fr')->isoFormat('MMM'),
                'conges'  => DemandeJouissance::whereHas('user', fn($q) =>
                    $q->where('departement_id', $deptId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'absences' => DemandeAbsence::whereHas('user', fn($q) =>
                    $q->where('departement_id', $deptId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $mois;
    }

    // Évolution mensuelle pour une direction (6 derniers mois)
    private function evolutionMoisDirection(int $dirId): array
    {
        $mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $mois[] = [
                'label'   => $date->locale('fr')->isoFormat('MMM'),
                'conges'  => DemandeJouissance::whereHas('user.departement', fn($q) =>
                    $q->where('direction_id', $dirId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'absences' => DemandeAbsence::whereHas('user.departement', fn($q) =>
                    $q->where('direction_id', $dirId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }
        return $mois;
    }

    // Évolution mensuelle globale (12 derniers mois)
    private function evolutionMoiSGlobal(string $type): array
    {
        $mois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            if ($type === 'conge') {
                $mois[] = [
                    'label' => $date->locale('fr')->isoFormat('MMM'),
                    'count' => DemandeJouissance::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)->count(),
                ];
            } else {
                $mois[] = [
                    'label' => $date->locale('fr')->isoFormat('MMM'),
                    'count' => DemandeAbsence::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)->count(),
                ];
            }
        }
        return $mois;
    }

    // Répartition par département dans une direction
    private function repartitionParDept(int $dirId): array
    {
        return \App\Models\Departement::where('direction_id', $dirId)
            ->withCount('users')
            ->get()
            ->map(fn($d) => [
                'nom'   => $d->libelle_court,
                'count' => $d->users_count,
            ])
            ->toArray();
    }
}