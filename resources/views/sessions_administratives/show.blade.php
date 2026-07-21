@extends('layouts.app')
@section('title', 'Détail session')
@section('page-title', 'Sessions Administratives')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding: 20px;">
                <h5 class="mb-0">{{ $session->libelle }}</h5>
            </div>
            <div class="card-body p-4">
                <table class="table table-borderless">
                    <tr>
                        <th style="width: 200px;">Année</th>
                        <td>{{ $session->annee }}</td>
                    </tr>
                    <tr>
                        <th>Période</th>
                        <td>{{ $session->date_debut->format('d/m/Y') }} → {{ $session->date_fin->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Demandes d'absence</th>
                        <td>{!! $session->active_absence ? '<span class="text-success">Ouvertes</span>' : '<span class="text-danger">Fermées</span>' !!}</td>
                    </tr>
                    <tr>
                        <th>Demandes de congé</th>
                        <td>{!! $session->active_conge ? '<span class="text-success">Ouvertes</span>' : '<span class="text-danger">Fermées</span>' !!}</td>
                    </tr>
                    <tr>
                        <th>Demandes de jouissance</th>
                        <td>{!! $session->active_jouissance ? '<span class="text-success">Ouvertes</span>' : '<span class="text-danger">Fermées</span>' !!}</td>
                    </tr>
                    <tr>
                        <th>Créée par</th>
                        <td>{{ $session->creePar->nom ?? '' }} {{ $session->creePar->prenom ?? '' }}</td>
                    </tr>
                </table>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="{{ route('sessions_Administratives.index') }}" class="btn btn-secondary px-4">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection