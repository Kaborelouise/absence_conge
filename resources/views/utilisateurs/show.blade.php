@extends('layouts.app')
@section('title', 'Détail utilisateur')
@section('page-title', 'Gestion des utilisateurs')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail de l'utilisateur</h5>
    <a href="{{ route('utilisateurs.index') }}" class="btn btn-sm btn-secondary">Retour
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">

    {{-- Colonne gauche : infos personnelles --}}
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-person me-2"></i> Informations personnelles
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="ps-3" style="width:40%">Matricule</th>
                        <td>{{ $user->matricule }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Nom</th>
                        <td>{{ strtoupper($user->nom) }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Prénom(s)</th>
                        <td>{{ $user->prenom }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Poste</th>
                        <td>{{ $user->poste ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Rôle</th>
                        <td>
                            <span class="badge-statut badge-en_cours">
                                {{ ucfirst(str_replace('_', ' ', $user->role->libelle ?? '—')) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Département</th>
                        <td>{{ $user->departement->libelle_long ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Direction</th>
                        <td>{{ $user->departement->direction->libelle_long ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Chef de département</th>
                        <td>
                            @if($user->est_responsable_departement)
                                <span class="badge-statut badge-validee">Oui</span>
                            @else
                                <span class="badge-statut badge-rejetee">Non</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Resp. direction</th>
                        <td>
                            @if($user->est_responsable_direction)
                                <span class="badge-statut badge-validee">Oui</span>
                            @else
                                <span class="badge-statut badge-rejetee">Non</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Colonne droite : soldes + statistiques --}}
    <div class="col-md-6">

        {{-- Soldes --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header card-header-anptic">
                <i class="bi bi-calendar-check me-2"></i> Soldes
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="ps-3" style="width:50%">Solde congé</th>
                        <td>
                            <strong>{{ $user->solde_conge ?? 0 }}</strong> jour(s)
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Solde absence</th>
                        <td>
                            <strong>{{ $user->solde_absence ?? 0 }}</strong> jour(s)
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Statistiques demandes --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header card-header-anptic">
                <i class="bi bi-bar-chart me-2"></i> Statistiques des demandes
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="ps-3" style="width:60%">Absences en attente</th>
                        <td>{{ $user->demandeAbsences->where('statut', 'en_attente')->count() }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Absences validées</th>
                        <td>{{ $user->demandeAbsences->where('statut', 'validee')->count() }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Absences rejetées</th>
                        <td>{{ $user->demandeAbsences->where('statut', 'rejetee')->count() }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Jouissances en attente</th>
                        <td>{{ $user->demandeJouissances->where('statut', 'en_attente')->count() }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Jouissances validées</th>
                        <td>{{ $user->demandeJouissances->where('statut', 'validee')->count() }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Jouissances rejetées</th>
                        <td>{{ $user->demandeJouissances->where('statut', 'rejetee')->count() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2">
            <a href="{{ route('utilisateurs.edit', $user->id) }}"
               class="btn btn-warning flex-fill"> Modifier</a>
            <form action="{{ route('utilisateurs.destroy', $user->id) }}"
                  method="POST" class="flex-fill">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger w-100"
                        onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer
                </button>
            </form>
        </div>

    </div>
</div>
@endsection