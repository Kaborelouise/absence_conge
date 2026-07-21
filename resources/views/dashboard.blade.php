@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')

<!--TABLEAU DE BORD Agent-->
@if($role === 'Agent')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Bonjour, {{ $user->prenom }} {{ $user->nom }}</h5>
        <small class="text-muted">{{ $user->poste }} — {{ $user->departement->libelle_court ?? '' }}</small>
    </div>
    <!-- <span class="baDGe bg-secondary">Agent</span> -->
</div>

<!-- Soldes -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:50px;height:50px;background:#e8f5e9;">
                    <i class="bi bi-calendar-check text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px;">Solde congé</div>
                    <div class="fw-bold fs-4">{{ $soldeConge }}</div>
                    <div class="text-muted" style="font-size:11px;">jours restants</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:50px;height:50px;background:#e3f2fd;">
                    <i class="bi bi-clock-history text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px;">Solde absence</div>
                    <div class="fw-bold fs-4">{{ $soldeAbsence }}</div>
                    <div class="text-muted" style="font-size:11px;">jours restants</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:50px;height:50px;background:#fce4ec;">
                    <i class="bi bi-file-earmark-text text-danger fs-4"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px;">Total demandes</div>
                    <div class="fw-bold fs-4">
                        {{ $mesConges->count() + $mesAbsences->count() + $mesJouissances->count() }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">demandes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:50px;height:50px;background:#fff8e1;">
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px;">En attente</div>
                    <div class="fw-bold fs-4">
                        {{ $mesAbsences->whereIn('statut', ['en_attente','en_cours'])->count()
                         + $mesJouissances->whereIn('statut', ['en_attente','en_cours'])->count() }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">demandes</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Stats congés -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-bookmark-check me-2"></i> Mes demandes de jouissance
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="fw-bold fs-4">{{ $mesJouissances->count() }}</div>
                        <div style="font-size:11px;color:#666;">Total</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-success">{{ $mesJouissances->where('statut','validee')->count() }}</div>
                        <div style="font-size:11px;color:#666;">Validées</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-primary">{{ $mesJouissances->whereIn('statut',['en_attente','en_cours'])->count() }}</div>
                        <div style="font-size:11px;color:#666;">En cours</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-danger">{{ $mesJouissances->where('statut','rejetee')->count() }}</div>
                        <div style="font-size:11px;color:#666;">Rejetées</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats absences -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-person-x me-2"></i> Mes autorisations d'absence
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-3">
                        <div class="fw-bold fs-4">{{ $mesAbsences->count() }}</div>
                        <div style="font-size:11px;color:#666;">Total</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-success">{{ $mesAbsences->where('statut','validee')->count() }}</div>
                        <div style="font-size:11px;color:#666;">Validées</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-primary">{{ $mesAbsences->whereIn('statut',['en_attente','en_cours'])->count() }}</div>
                        <div style="font-size:11px;color:#666;">En cours</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-4 text-danger">{{ $mesAbsences->where('statut','rejetee')->count() }}</div>
                        <div style="font-size:11px;color:#666;">Rejetées</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Graphique évolution -->
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-graph-up me-2"></i> Évolution de mes demandes (12 derniers mois)
            </div>
            <div class="card-body">
                <canvas id="chartEvolution" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Dernières demandes -->
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-list-ul me-2"></i> Mes dernières demandes
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark">
                        <tr>
                            <th class="ps-3">Type</th>
                            <th>Période</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dernieresDemandes as $d)
                        <tr>
                            <td class="ps-3">{{ $d['type'] }}</td>
                            <td style="font-size:11px;">{{ $d['periode'] }}</td>
                            <td>
                                <span class="baDGe-statut baDGe-{{ $d['statut'] }}">
                                    {{ ucfirst(str_replace('_',' ',$d['statut'])) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Aucune demande</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!--TABLEAU DE BORD CHEF DE DÉPARTEMENT / RESPONSABLE DIRECTION-->

@elseif(in_array($role, ['Chef de Département', 'Responsable Direction']) || $user->est_responsable_departement)

@php
    $estChef    = $role === 'Chef de Département' || $user->est_responsable_departement;
    $scope      = $estChef ? 'département' : 'direction';
    $conges     = $estChef ? $congesDept  : $congesDir;
    $absences   = $estChef ? $absencesDept : $absencesDir;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Tableau de bord — {{ ucfirst($scope) }}</h5>
        <small class="text-muted">
            {{ $estChef
                ? ($user->departement->libelle_court ?? '')
                : ($user->departement->direction->libelle_court ?? '') }}
        </small>
    </div>
</div>

{{-- Cartes globales --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="fw-bold fs-2 text-primary">{{ $nbAgents }}</div>
            <div style="font-size:12px;color:#666;">Agents du {{ $scope }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="fw-bold fs-2 text-success">{{ $AgentsEnConge->count() }}</div>
            <div style="font-size:12px;color:#666;">En congé actuellement</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <div class="fw-bold fs-2 text-info">{{ isset($AgentsEnAbsence) ? $AgentsEnAbsence->count() : 0 }}</div>
            <div style="font-size:12px;color:#666;">En absence actuellement</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3" style="background:#fff3cd;">
            <div class="fw-bold fs-2 text-warning">{{ $alertesConge + $alertesAbsence }}</div>
            <div style="font-size:12px;color:#856404;">Demandes à valider</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Stats congés --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-bookmark-check me-2"></i> Demandes de jouissance — {{ ucfirst($scope) }}
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col">
                        <div class="fw-bold fs-4">{{ $conges->count() }}</div>
                        <div style="font-size:11px;">Total</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-success">{{ $conges->where('statut','validee')->count() }}</div>
                        <div style="font-size:11px;">Validées</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-primary">{{ $conges->whereIn('statut',['en_attente','en_cours'])->count() }}</div>
                        <div style="font-size:11px;">En cours</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-danger">{{ $conges->where('statut','rejetee')->count() }}</div>
                        <div style="font-size:11px;">Rejetées</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-secondary">{{ $conges->filter(fn($d) => $d->estCloturee())->count() }}</div>
                        <div style="font-size:11px;">Clôturées</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats absences --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-person-x me-2"></i> Autorisations d'absence — {{ ucfirst($scope) }}
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col">
                        <div class="fw-bold fs-4">{{ $absences->count() }}</div>
                        <div style="font-size:11px;">Total</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-success">{{ $absences->where('statut','validee')->count() }}</div>
                        <div style="font-size:11px;">Validées</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-primary">{{ $absences->whereIn('statut',['en_attente','en_cours'])->count() }}</div>
                        <div style="font-size:11px;">En cours</div>
                    </div>
                    <div class="col">
                        <div class="fw-bold fs-4 text-danger">{{ $absences->where('statut','rejetee')->count() }}</div>
                        <div style="font-size:11px;">Rejetées</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Graphique évolution --}}
<div class="card shadow-sm mb-3">
    <div class="card-header card-header-anptic">
        <i class="bi bi-graph-up me-2"></i> Évolution des demandes (6 derniers mois)
    </div>
    <div class="card-body">
        <canvas id="chartEvolution" height="80"></canvas>
    </div>
</div>

<!--Tableau de bord RH / SG / DG / PCA -->
@elseif(in_array($role, ['Agent RH', 'SG', 'DG', 'PCA']))

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tableau de bord global ANPTIC</h5>
    <span class="baDGe-statut baDGe-en_cours">{{ now()->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
</div>

{{-- Cartes globales --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-people fs-3 text-primary mb-1"></i>
            <div class="fw-bold fs-3">{{ $nbAgents }}</div>
            <div style="font-size:12px;">Agents total ANPTIC</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-bookmark-check fs-3 text-success mb-1"></i>
            <div class="fw-bold fs-3">{{ $nbEnConge }}</div>
            <div style="font-size:12px;">En congé actuellement</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-person-x fs-3 text-info mb-1"></i>
            <div class="fw-bold fs-3">{{ $nbEnAbsence }}</div>
            <div style="font-size:12px;">En absence actuellement</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3" style="background:#fff3cd;">
            <i class="bi bi-exclamation-triangle fs-3 text-warning mb-1"></i>
            <div class="fw-bold fs-3">{{ $totalAlertes }}</div>
            <div style="font-size:12px;color:#856404;">Alertes à traiter</div>
        </div>
    </div>
</div>

{{-- Alertes détaillées --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-warning border-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:12px;color:#666;">En attente vérification RH</div>
                        <div class="fw-bold fs-3">{{ $alertesRH }}</div>
                    </div>
                    <i class="bi bi-person-check fs-2 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-info border-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:12px;color:#666;">En attente avis SG</div>
                        <div class="fw-bold fs-3">{{ $alertesSG }}</div>
                    </div>
                    <i class="bi bi-person-baDGe fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-primary border-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:12px;color:#666;">En attente avis DG</div>
                        <div class="fw-bold fs-3">{{ $alertesDG }}</div>
                    </div>
                    <i class="bi bi-person-baDGe fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Graphique congés --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-graph-up me-2"></i> Évolution congés (12 mois)
            </div>
            <div class="card-body">
                <canvas id="chartConges" height="120"></canvas>
            </div>
        </div>
    </div>
    {{-- Graphique absences --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-graph-up me-2"></i> Évolution absences (12 mois)
            </div>
            <div class="card-body">
                <canvas id="chartAbsences" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Agents en congé plus dernières demandes --}}
<div class="row g-3">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-people me-2"></i> Agents actuellement en congé
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark">
                        <tr>
                            <th class="ps-3">Agent</th>
                            <th>Direction</th>
                            <th>Retour</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($AgentsEnConge as $d)
                        <tr>
                            <td class="ps-3">{{ $d->user->nom }} {{ $d->user->prenom }}</td>
                            <td>{{ $d->user->departement->direction->libelle_court ?? '—' }}</td>
                            <td style="font-size:11px;">
                                {{ \Carbon\Carbon::parse($d->date_fin)->format('d/m/Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Aucun Agent en congé</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">
                <i class="bi bi-list-ul me-2"></i> Dernières demandes
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark">
                        <tr>
                            <th class="ps-3">Agent</th>
                            <th>Direction</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dernieresDemandes as $d)
                        <tr>
                            <td class="ps-3">{{ $d['Agent'] }}</td>
                            <td>{{ $d['dir'] }}</td>
                            <td>{{ $d['type'] }}</td>
                            <td style="font-size:11px;">{{ $d['periode'] }}</td>
                            <td>
                                <span class="baDGe-statut baDGe-{{ $d['statut'] }}">
                                    {{ ucfirst(str_replace('_',' ',$d['statut'])) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucune demande</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!--Tableau de bord administrateur -->
@elseif($role === 'Administrateur')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tableau de bord Administrateur</h5>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-people fs-3 text-primary mb-1"></i>
            <div class="fw-bold fs-3">{{ $totalUsers }}</div>
            <div style="font-size:12px;">Utilisateurs total</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-person-check fs-3 text-success mb-1"></i>
            <div class="fw-bold fs-3">{{ $connectesAujourdhui }}</div>
            <div style="font-size:12px;">Connectés aujourd'hui</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-person-x fs-3 text-danger mb-1"></i>
            <div class="fw-bold fs-3">{{ $jamaisConnectes }}</div>
            <div style="font-size:12px;">Jamais connectés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-shield-check fs-3 text-warning mb-1"></i>
            <div class="fw-bold fs-3">{{ $totalAdministrateurs }}</div>
            <div style="font-size:12px;">Administrateurs</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Utilisateurs par rôle --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-pie-chart me-2"></i> Utilisateurs par rôle
            </div>
            <div class="card-body">
                <canvas id="chartRoles" height="160"></canvas>
            </div>
        </div>
    </div>

    {{-- Agents sans demande de congé --}}
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Agents n'ayant pas soumis de congé en {{ now()->year }}
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:250px;overflow-y:auto;">
                    <table class="table table-sm mb-0">
                        <thead class="table-anptic-dark">
                            <tr>
                                <th class="ps-3">Agent</th>
                                <th>Direction</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($AgentsSansConge as $Agent)
                            <tr>
                                <td class="ps-3">{{ $Agent->nom }} {{ $Agent->prenom }}</td>
                                <td>{{ $Agent->departement->direction->libelle_court ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted">Tous les Agents ont soumis</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dernières demandes --}}
<div class="card shadow-sm">
    <div class="card-header card-header-anptic">
        <i class="bi bi-list-ul me-2"></i> Dernières demandes
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-anptic-dark">
                <tr>
                    <th class="ps-3">Agent</th>
                    <th>Direction</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dernieresDemandes as $d)
                <tr>
                    <td class="ps-3">{{ $d['Agent'] }}</td>
                    <td>{{ $d['dir'] }}</td>
                    <td>{{ $d['type'] }}</td>
                    <td style="font-size:11px;">{{ $d['periode'] }}</td>
                    <td>
                        <span class="baDGe-statut baDGe-{{ $d['statut'] }}">
                            {{ ucfirst(str_replace('_',' ',$d['statut'])) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Aucune demande</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif

@endsection

@section('scripts')
<script>
// Couleurs communes
const COLORS = {
    validee  : '#198754',
    en_cours : '#0d6efd',
    rejetee  : '#dc3545',
    attente  : '#ffc107',
    conge    : '#1B384F',
    absence  : '#42A5F5',
};

@if($role === 'Agent')
// Graphique évolution Agent
const evoData = @json($evolutionMois);
new Chart(document.getElementById('chartEvolution'), {
    type : 'line',
    data : {
        labels   : evoData.map(m => m.label),
        datasets : [
            { label:'Validées',  data: evoData.map(m => m.validees),
              borderColor: COLORS.validee,  backgroundColor: COLORS.validee+'22', tension:.3, fill:true },
            { label:'En cours',  data: evoData.map(m => m.en_cours),
              borderColor: COLORS.en_cours, backgroundColor: COLORS.en_cours+'22', tension:.3, fill:true },
            { label:'Rejetées',  data: evoData.map(m => m.rejetees),
              borderColor: COLORS.rejetee,  backgroundColor: COLORS.rejetee+'22', tension:.3, fill:true },
        ]
    },
    options : { plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true } } }
});
@endif

@if(in_array($role, ['Chef de Département','Responsable Direction']) || ($role !== 'Agent' && $role !== 'Agent RH' && $role !== 'SG' && $role !== 'DG' && $role !== 'PCA' && $role !== 'Administrateur'))
const evoMois = @json($evolutionMois ?? []);
if (document.getElementById('chartEvolution') && evoMois.length) {
    new Chart(document.getElementById('chartEvolution'), {
        type : 'bar',
        data : {
            labels   : evoMois.map(m => m.label),
            datasets : [
                { label:'Congés',   data: evoMois.map(m => m.conges),
                  backgroundColor: COLORS.conge },
                { label:'Absences', data: evoMois.map(m => m.absences),
                  backgroundColor: COLORS.absence },
            ]
        },
        options : { plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true } } }
    });
}
@endif

@if(in_array($role, ['Agent RH','SG','DG','PCA']))
// Graphique congés global
const congesData = @json($evolutionConges);
new Chart(document.getElementById('chartConges'), {
    type : 'line',
    data : {
        labels   : congesData.map(m => m.label),
        datasets : [{
            label           : 'Demandes de congé',
            data            : congesData.map(m => m.count),
            borderColor     : COLORS.conge,
            backgroundColor : COLORS.conge+'33',
            tension:.3, fill:true
        }]
    },
    options : { plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});

// Graphique absences global
const absencesData = @json($evolutionAbsences);
new Chart(document.getElementById('chartAbsences'), {
    type : 'line',
    data : {
        labels   : absencesData.map(m => m.label),
        datasets : [{
            label           : "Demandes d'absence",
            data            : absencesData.map(m => m.count),
            borderColor     : COLORS.absence,
            backgroundColor : COLORS.absence+'33',
            tension:.3, fill:true
        }]
    },
    options : { plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
});
@endif

@if($role === 'Administrateur')
// Graphique rôles Administrateur
const rolesData = @json($userParRole);
new Chart(document.getElementById('chartRoles'), {
    type : 'doughnut',
    data : {
        labels   : Object.keys(rolesData),
        datasets : [{
            data            : Object.values(rolesData),
            backgroundColor : ['#1B384F','#42A5F5','#198754','#dc3545','#ffc107','#6f42c1','#0dcaf0','#fd7e14'],
        }]
    },
    options : { plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } } }
});
@endif
</script>
@endsection