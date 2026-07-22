@extends('layouts.app')
@section('title', 'Sessions Administratives')
@section('page-title', 'Sessions Administratives')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Sessions Administratives</h5>
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

@php
    // Rôle de l'utilisateur connecté — utilisé pour afficher les boutons toggle
    $roleConnecte = auth()->user()->role->libelle;
@endphp

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
                        $aujourdhui  = \Carbon\Carbon::today();
                        $estCourante = $aujourdhui->between($session->date_debut, $session->date_fin);
                    @endphp

                    <tr class="{{ $estCourante ? 'table-success bg-opacity-25' : '' }}">

                        <td>
                            {{ $session->libelle }}
                            @if($estCourante)
                                <span class="badge bg-success ms-1">En cours</span>
                            @endif
                        </td>

                        <td>
                            {{ $session->date_debut->format('d/m/Y') }}
                            →
                            {{ $session->date_fin->format('d/m/Y') }}
                        </td>

                        {{-- ABSENCE : toggle visible par Administrateur uniquement --}}
                        <td>
                            <span class="{{ $session->active_absence ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                {{ $session->active_absence ? 'Ouvert' : 'Fermé' }}
                            </span>
                            {{-- CORRECTION : 'Administrateur' et non 'Administrateuristrateur' --}}
                            @if($roleConnecte === 'Administrateur')
                                <form action="{{ route('sessions_Administratives.toggle_absence', $session->id) }}"
                                      method="POST" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $session->active_absence ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            style="font-size:10px;padding:2px 8px;"
                                            onclick="return confirm('{{ $session->active_absence ? 'Fermer' : 'Ouvrir' }} les absences ?')">
                                        {{ $session->active_absence ? 'Fermer' : 'Ouvrir' }}
                                    </button>
                                </form>
                            @endif
                        </td>

                        {{-- CONGÉ : toggle visible par Administrateur ET Agent RH --}}
                        <td>
                            <span class="{{ $session->active_conge ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                {{ $session->active_conge ? 'Ouvert' : 'Fermé' }}
                            </span>
                            {{-- CORRECTION : noms de rôles corrects --}}
                            @if(in_array($roleConnecte, ['Administrateur', 'Agent RH']))
                                <form action="{{ route('sessions_Administratives.toggle_conge', $session->id) }}"
                                      method="POST" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $session->active_conge ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            style="font-size:10px;padding:2px 8px;"
                                            onclick="return confirm('{{ $session->active_conge ? 'Fermer' : 'Ouvrir' }} les congés ?')">
                                        {{ $session->active_conge ? 'Fermer' : 'Ouvrir' }}
                                    </button>
                                </form>
                            @endif
                        </td>

                        {{-- JOUISSANCE : toggle visible par Administrateur uniquement --}}
                        <td>
                            <span class="{{ $session->active_jouissance ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                {{ $session->active_jouissance ? 'Ouvert' : 'Fermé' }}
                            </span>
                            @if($roleConnecte === 'Administrateur')
                                <form action="{{ route('sessions_Administratives.toggle_jouissance', $session->id) }}"
                                      method="POST" class="d-inline ms-1">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $session->active_jouissance ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            style="font-size:10px;padding:2px 8px;"
                                            onclick="return confirm('{{ $session->active_jouissance ? 'Fermer' : 'Ouvrir' }} les jouissances ?')">
                                        {{ $session->active_jouissance ? 'Fermer' : 'Ouvrir' }}
                                    </button>
                                </form>
                            @endif
                        </td>

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