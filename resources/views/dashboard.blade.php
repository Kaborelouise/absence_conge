@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')

{{-- tableau de bord Agent--}}
@if($role === 'Agent')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-0">Bonjour, {{ $user->prenom }} {{ $user->nom }}</h5>
        <small class="text-muted">{{ $user->poste }} — {{ $user->departement->libelle_court ?? '' }}</small>
    </div>
</div>

{{-- Soldes --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:50px;height:50px;background:#e8f5e9;">
                    <i class="bi bi-calendar-check text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px;">Solde de congé</div>
                    <div class="fw-bold fs-4">{{ $soldeConge }}</div>
                    <div class="text-muted" style="font-size:11px;">jours restants</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:50px;height:50px;background:#e3f2fd;">
                    <i class="bi bi-clock-history text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted" style="font-size:12px;">Solde d'autorisation d'absence</div>
                    <div class="fw-bold fs-4">{{ $soldeAbsence }}</div>
                    <div class="text-muted" style="font-size:11px;">jours restants</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Demandes de jouissance de congé / Demandes d'autorisation d'absence --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-bookmark-check me-2"></i> Demandes de jouissance de congé
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-3"><div class="fw-bold fs-4">{{ $congeTotal }}</div><div style="font-size:11px;">Total</div></div>
                    <div class="col-3"><div class="fw-bold fs-4 text-danger">{{ $congeRejetees }}</div><div style="font-size:11px;">Rejetées</div></div>
                    <div class="col-3"><div class="fw-bold fs-4 text-primary">{{ $congeEnCours }}</div><div style="font-size:11px;">En cours</div></div>
                    <div class="col-3"><div class="fw-bold fs-4 text-success">{{ $congeValidees }}</div><div style="font-size:11px;">Validées</div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-person-x me-2"></i> Demandes d'autorisation d'absence
            </div>
            <div class="card-body">
                <div class="row text-center g-2">
                    <div class="col-3"><div class="fw-bold fs-4">{{ $absenceTotal }}</div><div style="font-size:11px;">Total</div></div>
                    <div class="col-3"><div class="fw-bold fs-4 text-danger">{{ $absenceRejetees }}</div><div style="font-size:11px;">Rejetées</div></div>
                    <div class="col-3"><div class="fw-bold fs-4 text-primary">{{ $absenceEnCours }}</div><div style="font-size:11px;">En cours</div></div>
                    <div class="col-3"><div class="fw-bold fs-4 text-success">{{ $absenceValidees }}</div><div style="font-size:11px;">Validées</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chef de Département / Responsable Direction --}}

@elseif(in_array($role, ['Chef de Département', 'Responsable Direction']))

@php
    $perimetre = $role === 'Chef de Département' ? 'département' : 'direction';
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tableau de bord — {{ ucfirst($perimetre) }}</h5>
</div>

{{-- Globale --}}
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-people fs-3 text-primary mb-1"></i>
            <div class="fw-bold fs-2">{{ $nbAgents }}</div>
            <div style="font-size:12px;">Agents du {{ $perimetre }}</div>
        </div>
    </div>
</div>

{{-- DEMANDE DE JOUISSANCE DE CONGÉ --}}
<h6 class="fw-bold mb-3"><i class="bi bi-bookmark-check me-2"></i>Demande de jouissance de congé</h6>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm text-center p-3">
            <div class="fw-bold fs-3 text-success">{{ $nbAgentsEnConge }}</div>
            <div style="font-size:11px;">Agents en congé dans le {{ $perimetre }} (suivi)</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $alerteConge }}</div>
            <div style="font-size:11px;">Demandes de jouissance de congé en attente de votre avis (alerte)</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4">{{ $congeStats['total'] }}</div><div style="font-size:11px;">Total</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-danger">{{ $congeStats['rejetees'] }}</div><div style="font-size:11px;">Rejetées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-primary">{{ $congeStats['en_cours'] }}</div><div style="font-size:11px;">En cours</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-success">{{ $congeStats['validees'] }}</div><div style="font-size:11px;">Validées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-secondary">{{ $congeStats['cloturees'] }}</div><div style="font-size:11px;">Clôturées</div></div></div>
</div>


<div class="card shadow-sm mb-3">
    <div class="card-header card-header-anptic">
        <i class="bi bi-calendar3-range me-2"></i> Répartition des demandes de jouissance de congé (calendrier)
    </div>
    <div class="card-body"><canvas id="ganttConge" height="140"></canvas></div>
</div>

{{-- Détaillée par agent et par année --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Jouissances de congé par agent</div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Agent</th><th>Nb</th></tr></thead>
                    <tbody>
                        @forelse($congeParAgent as $agent => $nb)
                        <tr><td class="ps-2" style="font-size:11px;">{{ $agent }}</td><td>{{ $nb }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Aucune demande</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Jouissances de congé par année</div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Année</th><th>Nb</th></tr></thead>
                    <tbody>
                        @forelse($congeParAnnee as $annee => $nb)
                        <tr><td class="ps-2">{{ $annee }}</td><td>{{ $nb }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Aucune demande</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Demande d'autorisation d'absence --}}
<h6 class="fw-bold mb-3"><i class="bi bi-person-x me-2"></i>Demande d'autorisation d'absence</h6>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card shadow-sm text-center p-3">
            <div class="fw-bold fs-3 text-info">{{ $nbAgentsEnAbsence }}</div>
            <div style="font-size:11px;">Agents en absence dans le {{ $perimetre }} (suivi)</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $alerteAbsence }}</div>
            <div style="font-size:11px;">Demandes d'absence en attente de votre avis (alerte)</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4">{{ $absenceStats['total'] }}</div><div style="font-size:11px;">Total</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-danger">{{ $absenceStats['rejetees'] }}</div><div style="font-size:11px;">Rejetées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-primary">{{ $absenceStats['en_cours'] }}</div><div style="font-size:11px;">En cours</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-success">{{ $absenceStats['validees'] }}</div><div style="font-size:11px;">Validées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-4 text-secondary">{{ $absenceStats['cloturees'] }}</div><div style="font-size:11px;">Clôturées</div></div></div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header card-header-anptic">
        <i class="bi bi-calendar3-range me-2"></i> Répartition des demandes d'absence (calendrier)
    </div>
    <div class="card-body"><canvas id="ganttAbsence" height="140"></canvas></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Absences par agent</div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Agent</th><th>Nb</th></tr></thead>
                    <tbody>
                        @forelse($absenceParAgent as $agent => $nb)
                        <tr><td class="ps-2" style="font-size:11px;">{{ $agent }}</td><td>{{ $nb }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Aucune demande</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Absences par année</div>
            <div class="card-body p-0" style="max-height:220px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Année</th><th>Nb</th></tr></thead>
                    <tbody>
                        @forelse($absenceParAnnee as $annee => $nb)
                        <tr><td class="ps-2">{{ $annee }}</td><td>{{ $nb }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted">Aucune demande</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- RH / SG / DG / PCA --}}
@elseif(in_array($role, ['Agent RH', 'SG', 'DG', 'PCA']))

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tableau de bord global ANPTIC</h5>
    <span class="badge-statut badge-en_cours">{{ now()->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
</div>

{{--GLOBALE --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-people fs-3 text-primary mb-1"></i>
            <div class="fw-bold fs-2">{{ $nbAgents }}</div>
            <div style="font-size:12px;">Agents de la structure</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-bookmark-check fs-3 text-success mb-1"></i>
            <div class="fw-bold fs-2">{{ $nbEnConge }}</div>
            <div style="font-size:12px;">Agents en congé actuellement</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm text-center p-3">
            <i class="bi bi-person-x fs-3 text-info mb-1"></i>
            <div class="fw-bold fs-2">{{ $nbEnAbsence }}</div>
            <div style="font-size:12px;">Agents en absence actuellement</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-pie-chart me-2"></i> Agents par direction
            </div>
            <div class="card-body"><canvas id="chartAgentsDir" height="180"></canvas></div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-pie-chart me-2"></i> Agents en congé par direction
            </div>
            <div class="card-body"><canvas id="chartCongesDir" height="180"></canvas></div>
        </div>
    </div>
</div>

{{-- Alertes --}}
<h6 class="fw-bold mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Alertes</h6>
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card shadow-sm text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $alertesCongeRH }}</div>
            <div style="font-size:11px;">Jouissances de congé en attente<br>vérification RH</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center p-3 border-info">
            <div class="fw-bold fs-3 text-info">{{ $alertesCongeSG }}</div>
            <div style="font-size:11px;">Jouissances de congé en attente<br>avis SG</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center p-3 border-primary">
            <div class="fw-bold fs-3 text-primary">{{ $alertesCongeDG }}</div>
            <div style="font-size:11px;">Jouissances de congé en attente<br>avis DG</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center p-3 border-warning">
            <div class="fw-bold fs-3 text-warning">{{ $alertesAbsenceRH }}</div>
            <div style="font-size:11px;">Absences en attente<br>vérification RH</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center p-3 border-info">
            <div class="fw-bold fs-3 text-info">{{ $alertesAbsenceSG }}</div>
            <div style="font-size:11px;">Absences en attente<br>avis SG</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm text-center p-3 border-primary">
            <div class="fw-bold fs-3 text-primary">{{ $alertesAbsenceDG }}</div>
            <div style="font-size:11px;">Absences en attente<br>avis DG</div>
        </div>
    </div>
</div>

{{-- Générale congés--}}
<h6 class="fw-bold mb-3"><i class="bi bi-bookmark-check me-2"></i>Demandes de jouissance de congé</h6>
<div class="row g-3 mb-4">
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3">{{ $congeStats['total'] }}</div><div style="font-size:11px;">Total</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-danger">{{ $congeStats['rejetees'] }}</div><div style="font-size:11px;">Rejetées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-primary">{{ $congeStats['en_cours'] }}</div><div style="font-size:11px;">En cours</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-success">{{ $congeStats['validees'] }}</div><div style="font-size:11px;">Validées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-secondary">{{ $congeStats['cloturees'] }}</div><div style="font-size:11px;">Clôturées</div></div></div>
</div>

{{-- Générale par direction 5 diagrammes circulaires --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic" style="font-size:11px;">Total jouissances de congé par direction</div>
            <div class="card-body"><canvas id="chartCongesTotal" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic" style="font-size:11px;">Jouissances de congé rejetées par direction</div>
            <div class="card-body"><canvas id="chartCongesRejetes" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic" style="font-size:11px;">Jouissances de congé en cours par direction</div>
            <div class="card-body"><canvas id="chartCongesEnCours" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic" style="font-size:11px;">Jouissances de congé validées par direction</div>
            <div class="card-body"><canvas id="chartCongesValides" height="160"></canvas></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic" style="font-size:11px;">Jouissances de congé clôturées par direction</div>
            <div class="card-body"><canvas id="chartCongesClotures" height="160"></canvas></div>
        </div>
    </div>
</div>

{{-- Stratégique Gantt congé, avec filtre par direction --}}
<div class="card shadow-sm mb-4">
    <div class="card-header card-header-anptic d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar3-range me-2"></i> Répartition des demandes de jouissance de congé (Gantt)</span>
        <select id="filtreDirectionConge" class="form-select form-select-sm" style="width:auto;">
            <option value="">Toutes les directions</option>
            @foreach($directions as $dir)
            <option value="{{ $dir->libelle_court }}">{{ $dir->libelle_court }}</option>
            @endforeach
        </select>
    </div>
    <div class="card-body"><canvas id="ganttConge" height="180"></canvas></div>
</div>

{{-- Détaillée congés --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Par agent</div>
            <div class="card-body p-0" style="max-height:200px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Agent</th><th>Nb</th></tr></thead>
                    <tbody>
                        @foreach($congeParAgent as $agent => $nb)
                        <tr><td class="ps-2" style="font-size:11px;">{{ $agent }}</td><td>{{ $nb }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Par année</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Année</th><th>Nb</th></tr></thead>
                    <tbody>
                        @foreach($congeParAnnee as $annee => $nb)
                        <tr><td class="ps-2">{{ $annee }}</td><td>{{ $nb }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Par direction</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Direction</th><th>Nb</th></tr></thead>
                    <tbody>
                        @foreach($congeParDirectionListe as $dir => $nb)
                        <tr><td class="ps-2">{{ $dir }}</td><td>{{ $nb }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Générale absences --}}
<h6 class="fw-bold mb-3"><i class="bi bi-person-x me-2"></i>Demandes d'autorisation d'absence</h6>
<div class="row g-3 mb-4">
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3">{{ $absenceStats['total'] }}</div><div style="font-size:11px;">Total</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-danger">{{ $absenceStats['rejetees'] }}</div><div style="font-size:11px;">Rejetées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-primary">{{ $absenceStats['en_cours'] }}</div><div style="font-size:11px;">En cours</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-success">{{ $absenceStats['validees'] }}</div><div style="font-size:11px;">Validées</div></div></div>
    <div class="col"><div class="card shadow-sm text-center p-3"><div class="fw-bold fs-3 text-secondary">{{ $absenceStats['cloturees'] }}</div><div style="font-size:11px;">Clôturées</div></div></div>
</div>

{{-- Stratégique : Gantt absence, avec filtre par direction --}}
<div class="card shadow-sm mb-4">
    <div class="card-header card-header-anptic d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar3-range me-2"></i> Répartition des demandes d'absence (Gantt)</span>
        <select id="filtreDirectionAbsence" class="form-select form-select-sm" style="width:auto;">
            <option value="">Toutes les directions</option>
            @foreach($directions as $dir)
            <option value="{{ $dir->libelle_court }}">{{ $dir->libelle_court }}</option>
            @endforeach
        </select>
    </div>
    <div class="card-body"><canvas id="ganttAbsence" height="180"></canvas></div>
</div>

{{-- Détaillée absences --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Absences par agent</div>
            <div class="card-body p-0" style="max-height:200px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Agent</th><th>Nb</th></tr></thead>
                    <tbody>
                        @foreach($absenceParAgent as $agent => $nb)
                        <tr><td class="ps-2" style="font-size:11px;">{{ $agent }}</td><td>{{ $nb }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic">Absences par année</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-anptic-dark"><tr><th class="ps-2">Année</th><th>Nb</th></tr></thead>
                    <tbody>
                        @foreach($absenceParAnnee as $annee => $nb)
                        <tr><td class="ps-2">{{ $annee }}</td><td>{{ $nb }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{--Tableau de bord de l'Administrateur --}}
@elseif($role === 'Administrateur')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Tableau de bord Administrateur</h5>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-file-earmark-excel me-2"></i> Exportation des données (Excel)
            </div>
            <div class="card-body d-flex flex-wrap gap-2 align-items-start">
                <a href="{{ route('admin.export.users') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download me-1"></i> Utilisateurs
                </a>
                <a href="{{ route('admin.export.conges') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download me-1"></i> Demandes de congé
                </a>
                <a href="{{ route('admin.export.jouissances') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download me-1"></i> Demandes de jouissance
                </a>
                <a href="{{ route('admin.export.absences') }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-download me-1"></i> Demandes d'absence
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Liste des utilisateurs statut, derniere authentification et filtre par direction --}}
<div class="card shadow-sm mb-4">
    <div class="card-header card-header-anptic d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-ul me-2"></i> Liste des utilisateurs</span>
        <select id="filtreDirectionUsers" class="form-select form-select-sm" style="width:auto;">
            <option value="">Toutes les directions</option>
            @foreach($toutesDirections as $dir)
            <option value="{{ $dir }}">{{ $dir }}</option>
            @endforeach
        </select>
    </div>
    <div class="card-body p-0 pt-2" style="max-height:320px;overflow-y:auto;">
    <div class="table-responsive">
    <table class="table table-sm mb-0" id="tableUsers">
            <thead class="table-anptic-dark">
                <tr><th class="ps-3">Nom</th><th>Rôle</th><th>Direction</th><th>Statut</th><th>Dernière connexion</th></tr>
            </thead>
            <tbody>
                @foreach($listeUtilisateurs as $u)
                <tr data-direction="{{ $u['direction'] }}">
                    <td class="ps-3">{{ $u['nom'] }}</td>
                    <td style="font-size:11px;">{{ $u['role'] }}</td>
                    <td style="font-size:11px;">{{ $u['direction'] }}</td>
                    <td>
                        <span class="badge-statut badge-{{ $u['statut'] === 'Confirmé' ? 'validee' : 'rejetee' }}">
                            {{ $u['statut'] }}
                        </span>
                    </td>
                    <td style="font-size:11px;">{{ $u['derniere_connexion'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Agents n'ayant fait aucune demande de congé --}}
<div class="card shadow-sm mb-4">
    <div class="card-header card-header-anptic d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-triangle me-2"></i> Agents sans demande de congé</span>
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="annee" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                @foreach($anneesDisponibles as $annee)
                <option value="{{ $annee }}" @selected($annee == $anneeSelectionnee)>{{ $annee }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive" style="max-height:250px;overflow-y:auto;">
            <table class="table table-sm mb-0">
                <thead class="table-anptic-dark">
                    <tr><th class="ps-3">Agent</th><th>Direction</th></tr>
                </thead>
                <tbody>
                    @forelse($agentsSansConge as $agent)
                    <tr>
                        <td class="ps-3">{{ $agent->nom }} {{ $agent->prenom }}</td>
                        <td>{{ $agent->departement->direction->libelle_court ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center text-muted">Tous les agents ont soumis une demande en {{ $anneeSelectionnee }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Journal d'audit --}}
<div class="card shadow-sm mb-4">
    <div class="card-header card-header-anptic">
        <i class="bi bi-clock-history me-2"></i> Historique des actions
    </div>
    <div class="card-body pt-2 p-0">
    <table class="table table-sm mb-0">
            <thead class="table-anptic-dark">
                <tr><th class="ps-3">Date/Heure</th><th>Utilisateur</th><th>Action</th><th>Module</th><th>Description</th></tr>
            </thead>
            <tbody>
                @forelse($journalActions as $log)
                <tr>
                    <td class="ps-3" style="font-size:11px;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td style="font-size:11px;">{{ $log->user->nom ?? '—' }} {{ $log->user->prenom ?? '' }}</td>
                    <td>
                        <span class="badge-statut badge-{{ $log->action === 'create' ? 'validee' : ($log->action === 'delete' ? 'rejetee' : 'en_cours') }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td style="font-size:11px;">{{ $log->model }}</td>
                    <td style="font-size:11px;">{{ $log->description }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Aucune action enregistrée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else
<div class="alert alert-warning">
    Tableau de bord non disponible pour le rôle : <strong>{{ $role }}</strong>
</div>
@endif

@endsection

@section('scripts')
<script>
const COLORS = ['#1B384F','#42A5F5','#198754','#dc3545','#ffc107','#6f42c1','#0dcaf0','#fd7e14','#20c997'];


function couleurStatut(statut) {
    if (statut === 'validee') return '#198754';
    if (statut === 'rejetee') return '#dc3545';
    return '#42A5F5'; // en_attente / en_cours
}


function renderGantt(canvasId, items) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    if (items.length === 0) {
        ctx.replaceWith(Object.assign(document.createElement('div'), {
            className: 'text-center text-muted p-4',
            innerText: 'Aucune donnée à afficher pour ce filtre.'
        }));
        return null;
    }

    const toDay = (iso) => Math.floor(new Date(iso + 'T00:00:00').getTime() / 86400000);
    const origine = Math.min(...items.map(i => toDay(i.debut)));

    const labels = items.map(i => `${i.agent} (${i.direction})`);
    const data   = items.map(i => [toDay(i.debut) - origine, toDay(i.fin) - origine + 1]);
    const colors = items.map(i => couleurStatut(i.statut));

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{ data, backgroundColor: colors, borderRadius: 3 }]
        },
        options: {
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (c) => {
                            const item = items[c.dataIndex];
                            return `${item.debut} → ${item.fin} (${item.statut})`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: (v) => {
                            const d = new Date(origine * 86400000 + v * 86400000);
                            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
                        }
                    }
                },
                y: { ticks: { font: { size: 10 } } }
            }
        }
    });
}

@if($role === 'Chef de Département' || $role === 'Responsable Direction')
renderGantt('ganttConge', @json($congeCalendrier));
renderGantt('ganttAbsence', @json($absenceCalendrier));
@endif

@if(in_array($role, ['Agent RH', 'SG', 'DG', 'PCA']))
const agentsDir = @json($agentsParDirection);
new Chart(document.getElementById('chartAgentsDir'), {
    type: 'doughnut',
    data: { labels: agentsDir.map(d => d.label), datasets: [{ data: agentsDir.map(d => d.count), backgroundColor: COLORS }] },
    options: { plugins:{ legend:{ position:'bottom', labels:{ font:{ size:10 } } } } }
});

const congesDir = @json($congesParDirection);
new Chart(document.getElementById('chartCongesDir'), {
    type: 'doughnut',
    data: { labels: Object.keys(congesDir), datasets: [{ data: Object.values(congesDir), backgroundColor: COLORS }] },
    options: { plugins:{ legend:{ position:'bottom', labels:{ font:{ size:10 } } } } }
});


const statDir = @json($congesStatParDirection);
function pieParDirection(canvasId, champ) {
    new Chart(document.getElementById(canvasId), {
        type: 'pie',
        data: { labels: statDir.map(d => d.label), datasets: [{ data: statDir.map(d => d[champ]), backgroundColor: COLORS }] },
        options: { plugins:{ legend:{ position:'bottom', labels:{ font:{ size:9 } } } } }
    });
}
pieParDirection('chartCongesTotal', 'total');
pieParDirection('chartCongesRejetes', 'rejetes');
pieParDirection('chartCongesEnCours', 'en_cours');
pieParDirection('chartCongesValides', 'valides');
pieParDirection('chartCongesClotures', 'clotures');

// Gantt avec filtre par direction congé et absence
const congeCalendrierData   = @json($congeCalendrier);
const absenceCalendrierData = @json($absenceCalendrier);
let ganttCongeChart   = renderGantt('ganttConge', congeCalendrierData);
let ganttAbsenceChart = renderGantt('ganttAbsence', absenceCalendrierData);

document.getElementById('filtreDirectionConge')?.addEventListener('change', function () {
    const filtre = this.value;
    const filtrees = filtre ? congeCalendrierData.filter(i => i.direction === filtre) : congeCalendrierData;
    ganttCongeChart?.destroy();
    ganttCongeChart = renderGantt('ganttConge', filtrees);
});

document.getElementById('filtreDirectionAbsence')?.addEventListener('change', function () {
    const filtre = this.value;
    const filtrees = filtre ? absenceCalendrierData.filter(i => i.direction === filtre) : absenceCalendrierData;
    ganttAbsenceChart?.destroy();
    ganttAbsenceChart = renderGantt('ganttAbsence', filtrees);
});
@endif

@if($role === 'Administrateur')
const rolesData = @json($userParRole);
new Chart(document.getElementById('chartRoles'), {
    type: 'doughnut',
    data: { labels: Object.keys(rolesData), datasets: [{ data: Object.values(rolesData), backgroundColor: COLORS }] },
    options: { plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } } }
});

// Filtre client de la liste des utilisateurs par direction.
document.getElementById('filtreDirectionUsers')?.addEventListener('change', function () {
    const filtre = this.value;
    document.querySelectorAll('#tableUsers tbody tr').forEach(tr => {
        tr.style.display = (!filtre || tr.dataset.direction === filtre) ? '' : 'none';
    });
});
@endif
</script>
@endsection