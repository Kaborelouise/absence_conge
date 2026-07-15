@extends('layouts.app')
@section('title', 'Sessions administratives')
@section('page-title', 'Sessions administratives')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Sessions administratives</h5>
    <a href="{{ route('sessions_administratives.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle session
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-anptic-dark">
                <tr>
                    <th>Libellé</th>
                    <th>Période</th>
                    <th>Absence</th>
                    <th>Congé</th>
                    <th>Jouissance</th>
                    <th>Créée par</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                @php
                    $aujourdhui = \Carbon\Carbon::today();
                    $estCourante = $aujourdhui->between($session->date_debut, $session->date_fin);
                @endphp
                <tr class="{{ $estCourante ? 'table-success bg-opacity-25' : '' }}">
                    <td>
                        {{ $session->libelle }}
                        @if($estCourante)
                            <span class="badge bg-success ms-1">En cours</span>
                        @endif
                    </td>
                    <td>{{ $session->date_debut->format('d/m/Y') }} → {{ $session->date_fin->format('d/m/Y') }}</td>
                    <td>{!! $session->active_absence ? '<span class="text-success">Ouvert</span>' : '<span class="text-danger">Fermé</span>' !!}</td>
                    <td>{!! $session->active_conge ? '<span class="text-success">Ouvert</span>' : '<span class="text-danger">Fermé</span>' !!}</td>
                    <td>{!! $session->active_jouissance ? '<span class="text-success">Ouvert</span>' : '<span class="text-danger">Fermé</span>' !!}</td>
                    <td>{{ $session->creePar->nom ?? '' }} {{ $session->creePar->prenom ?? '' }}</td>
                    <td>
                        <a href="{{ route('sessions_administratives.show', $session->id) }}"
                           class="btn btn-sm btn-outline-primary">Voir</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Aucune session créée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection