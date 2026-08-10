@extends('layouts.app')
@section('title', 'Détail demande de congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding: 20px;">
                <h5 class="mb-0">Détail de la demande de congé</h5>
            </div>
            <div class="card-body p-4">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif



                <table class="table table-borderless">
                    <tr>
                        <th style="width: 220px;">Numéro</th>
                        <td>{{ $demande->num_demande }}</td>
                    </tr>
                    <tr>
                        <th>Agent</th>
                        <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    </tr>
                    <tr>
                        <th>Département</th>
                        <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Direction</th>
                        <td>{{ $demande->user->departement->direction->libelle_court ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Lieu(x) de jouissance</th>
                        <td>{{ implode(', ', $demande->lieu_jouissance ?? []) }}</td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td>
                            @if($demande->abandonnee)
                                <span class="baDGe-statut baDGe-rejetee">Abandonnée</span>
                            @elseif($demande->estCompilee())
                                <span class="baDGe-statut baDGe-validee">Compilée</span>
                            @else
                                <span class="baDGe-statut baDGe-en_attente">En attente</span>
                            @endif
                        </td>
                    </tr>

                    @php
                        $periode = $demande->user->periodeJouissance();
                    @endphp
                    <tr>
                        <th>Période de jouissance</th>
                        <td>
                            @if($session)
                                <span class="text-muted">
                                    {{ $session['date_debut']->format('d/m/Y') }} au {{ $session['date_fin']->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted fst-italic">
                                    Non calculable (Aucune session en cours)
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="{{ route('demande_conges.index') }}" class="btn btn-secondary px-4">
                        Retour à la liste
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection