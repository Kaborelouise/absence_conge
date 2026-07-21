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

                {{--
                    RETIRÉ : bouton "Compiler" (déplacé vers l'index, action
                    globale sur toute la liste — plus de sens d'avoir un bouton
                    de compilation individuelle par demande) et section
                    "historique" (les avis de compilation sont désormais
                    consultables globalement, pas nécessaire de dupliquer un
                    historique détaillé sur chaque demande individuelle).
                --}}

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

                    {{--
                        AJOUTÉ : période de jouissance calculée à partir de la
                        date de prise de service de l'Agent (voir
                        User::periodeJouissance()). Affichée en gris et non
                        modifiable : c'est une information calculée, pas un
                        champ saisi par l'Agent.
                    --}}
                    @php
                        $periode = $demande->user->periodeJouissance();
                    @endphp
                    <tr>
                        <th>Période de jouissance</th>
                        <td>
                            @if($periode)
                                <span class="text-muted">
                                    {{ $periode['debut']->format('d/m/Y') }} → {{ $periode['fin']->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted fst-italic">
                                    Non calculable (date de prise de service non renseignée)
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