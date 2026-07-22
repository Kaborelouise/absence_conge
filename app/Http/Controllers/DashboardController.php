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
        // TABLEAU DE BORD AGENT
        // ============================================================
        if ($role === 'Agent') {
            $joursAbsenceUtilises = DemandeAbsence::where('user_id', $user->id)
                ->where('statut', 'validee')
                ->whereYear('date_debut', now()->year)
                ->get()
                ->sum(fn($d) => Carbon::parse($d->date_debut)
                    ->diffInDays(Carbon::parse($d->date_fin)) + 1);

            $soldeAbsence   = max(0, 10 - $joursAbsenceUtilises);
            $soldeConge     = $user->solde_conge ?? 30;
            $mesConges      = DemandeConge::where('user_id', $user->id)->get();
            $mesAbsences    = DemandeAbsence::where('user_id', $user->id)->get();
            $mesJouissances = DemandeJouissance::where('user_id', $user->id)->get();
            $dernieresDemandes = $this->dernieresDemandes($user->id);
            $evolutionMois  = $this->evolutionMoisAgent($user->id);

            return view('dashboard', compact(
                'role', 'user',
                'soldeAbsence', 'soldeConge',
                'mesConges', 'mesAbsences', 'mesJouissances',
                'dernieresDemandes', 'evolutionMois'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD RESPONSABLE DÉPARTEMENT
        // ============================================================
        if ($role === 'Responsable Département' || $user->est_responsable_departement) {
            $departementId = $user->departement_id;

            $nbAgents = User::where('departement_id', $departementId)->count();

            $congesDept = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->get();

            $absencesDept = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->get();

            $alertesConge = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'en_attente')->count();

            $alertesAbsence = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'en_attente')->count();

            $agentsEnConge = DemandeJouissance::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user')->get();

            $agentsEnAbsence = DemandeAbsence::whereHas('user', fn($q) =>
                $q->where('departement_id', $departementId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user')->get();

            $evolutionMois    = $this->evolutionMoisDept($departementId);
            $demandesParAgent = User::where('departement_id', $departementId)
                ->with(['demandeAbsences', 'demandeJouissances'])->get();

            return view('dashboard', compact(
                'role', 'user',
                'nbAgents', 'congesDept', 'absencesDept',
                'alertesConge', 'alertesAbsence',
                'agentsEnConge', 'agentsEnAbsence',
                'evolutionMois', 'demandesParAgent'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD RESPONSABLE DIRECTION
        // ============================================================
        if ($role === 'Responsable Direction') {
            $directionId = $user->departement->direction_id;

            $nbAgents = User::whereHas('departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->count();

            $congesDir = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->get();

            $absencesDir = DemandeAbsence::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->get();

            $alertesConge = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'en_cours')->count();

            $alertesAbsence = DemandeAbsence::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'en_cours')->count();

            $agentsEnConge = DemandeJouissance::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user.departement')->get();

            // AJOUT : agents en absence dans la direction
            $agentsEnAbsence = DemandeAbsence::whereHas('user.departement', fn($q) =>
                $q->where('direction_id', $directionId)
            )->where('statut', 'validee')
             ->where('date_debut', '<=', now())
             ->where('date_fin', '>=', now())
             ->with('user.departement')->get();

            $repartitionDepts = $this->repartitionParDept($directionId);
            $evolutionMois    = $this->evolutionMoisDirection($directionId);

            return view('dashboard', compact(
                'role', 'user',
                'nbAgents', 'congesDir', 'absencesDir',
                'alertesConge', 'alertesAbsence',
                'agentsEnConge', 'agentsEnAbsence',
                'repartitionDepts', 'evolutionMois'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD AGENT RH / SG / DG / PCA
        // ============================================================
        if (in_array($role, ['Agent RH', 'SG', 'DG', 'PCA'])) {
            $nbAgents     = User::count();
            $nbDirections = Direction::count();

            $tousConges   = DemandeJouissance::all();
            $tousAbsences = DemandeAbsence::all();

            $nbEnConge = DemandeJouissance::where('statut', 'validee')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->count();

            $nbEnAbsence = DemandeAbsence::where('statut', 'validee')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->count();

            // Alertes par étape — on cherche 'agent_rh', 'sg', 'dg' en minuscule
            // car c'est la valeur stockée dans la colonne 'type' des avis
            $alertesRH = DemandeAbsence::where('statut', 'en_cours')
                ->whereDoesntHave('avisAbsence', fn($q) =>
                    $q->where('type', 'agent_rh')
                )->count()
                + DemandeJouissance::where('statut', 'en_cours')
                ->whereDoesntHave('avis', fn($q) =>
                    $q->where('type', 'agent_rh')
                )->count();

            $alertesSG = DemandeAbsence::where('statut', 'en_cours')
                ->whereHas('avisAbsence', fn($q) =>
                    $q->where('type', 'agent_rh')->where('avis', 'favorable')
                )
                ->whereDoesntHave('avisAbsence', fn($q) =>
                    $q->where('type', 'sg')
                )->count();

            $alertesDG = DemandeAbsence::where('statut', 'en_cours')
                ->whereHas('avisAbsence', fn($q) =>
                    $q->where('type', 'sg')->where('avis', 'favorable')
                )
                ->whereDoesntHave('avisAbsence', fn($q) =>
                    $q->where('type', 'dg')
                )->count();

            $totalAlertes = $alertesRH + $alertesSG + $alertesDG;

            $agentsEnConge = DemandeJouissance::with('user.departement.direction')
                ->where('statut', 'validee')
                ->where('date_debut', '<=', now())
                ->where('date_fin', '>=', now())
                ->latest()->take(5)->get();

            $dernieresDemandes = $this->dernieresDemandes();
            $evolutionConges   = $this->evolutionMoisGlobal('conge');
            $evolutionAbsences = $this->evolutionMoisGlobal('absence');

            return view('dashboard', compact(
                'role', 'user',
                'nbAgents', 'nbDirections',
                'tousConges', 'tousAbsences',
                'nbEnConge', 'nbEnAbsence',
                'alertesRH', 'alertesSG', 'alertesDG', 'totalAlertes',
                'agentsEnConge', 'dernieresDemandes',
                'evolutionConges', 'evolutionAbsences'
            ));
        }

        // ============================================================
        // TABLEAU DE BORD ADMINISTRATEUR
        // ============================================================
        if ($role === 'Administrateur') {
            $totalUsers           = User::count();
            $totalAdministrateurs = User::whereHas('role', fn($q) =>
                $q->where('libelle', 'Administrateur')
            )->count();

            $connectesAujourdhui = User::whereDate('updated_at', today())->count();

            // On utilise remember_token comme proxy pour "jamais connecté"
            $jamaisConnectes = User::whereNull('remember_token')->count();

            $userParRole = User::with('role')
                ->get()
                ->groupBy('role.libelle')
                ->map->count();

            // Agents sans demande de congé cette année
            // On exclut tous les rôles non-agents
            $agentsSansConge = User::whereHas('role', fn($q) =>
                $q->whereNotIn('libelle', [
                    'Administrateur', 'Agent RH', 'SG', 'DG', 'PCA',
                    'Responsable Direction', 'Responsable Département'
                ])
            )->whereDoesntHave('demandeConges', fn($q) =>
                $q->whereYear('created_at', now()->year)
            )->with('departement.direction')->get();

            $dernieresDemandes = $this->dernieresDemandes();

            return view('dashboard', compact(
                'role', 'user',
                'totalUsers', 'totalAdministrateurs',
                'connectesAujourdhui', 'jamaisConnectes',
                'userParRole', 'agentsSansConge',
                'dernieresDemandes'
            ));
        }

        // Fallback si aucun rôle ne correspond
        return view('dashboard', compact('role', 'user'));
    }

    // ================================================================
    // MÉTHODES PRIVÉES
    // ================================================================

    private function dernieresDemandes(?int $userId = null): array
    {
        $absences = DemandeAbsence::with('user.departement.direction')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->latest()->take(5)->get()
            ->map(fn($d) => [
                'type'    => 'Absence',
                'agent'   => $d->user->nom . ' ' . $d->user->prenom,
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
                'agent'   => $d->user->nom . ' ' . $d->user->prenom,
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

    private function evolutionMoisAgent(int $userId): array
    {
        $mois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $mois[] = [
                'label'    => $date->locale('fr')->isoFormat('MMM'),
                'validees' => DemandeAbsence::where('user_id', $userId)
                    ->where('statut', 'validee')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'en_cours' => DemandeAbsence::where('user_id', $userId)
                    ->where('statut', 'en_cours')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'rejetees' => DemandeAbsence::where('user_id', $userId)
                    ->where('statut', 'rejetee')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
            ];
        }
        return $mois;
    }

    private function evolutionMoisDept(int $deptId): array
    {
        $mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $mois[] = [
                'label'    => $date->locale('fr')->isoFormat('MMM'),
                'conges'   => DemandeJouissance::whereHas('user', fn($q) =>
                    $q->where('departement_id', $deptId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'absences' => DemandeAbsence::whereHas('user', fn($q) =>
                    $q->where('departement_id', $deptId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
            ];
        }
        return $mois;
    }

    private function evolutionMoisDirection(int $dirId): array
    {
        $mois = [];
        for ($i = 5; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $mois[] = [
                'label'    => $date->locale('fr')->isoFormat('MMM'),
                'conges'   => DemandeJouissance::whereHas('user.departement', fn($q) =>
                    $q->where('direction_id', $dirId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'absences' => DemandeAbsence::whereHas('user.departement', fn($q) =>
                    $q->where('direction_id', $dirId))
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
            ];
        }
        return $mois;
    }

    private function evolutionMoisGlobal(string $type): array
    {
        $mois = [];
        for ($i = 11; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $mois[] = [
                'label' => $date->locale('fr')->isoFormat('MMM'),
                'count' => $type === 'conge'
                    ? DemandeJouissance::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)->count()
                    : DemandeAbsence::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)->count(),
            ];
        }
        return $mois;
    }

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