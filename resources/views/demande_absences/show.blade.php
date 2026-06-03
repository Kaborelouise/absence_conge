@extends('layouts.app')
@section('title', 'Détail demande')
@section('page-title', 'Autorisation d\'absence')

@section('content')

{{-- En-tête --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail / Suivi de la demande</h5>
    <a href="{{ route('demande_absences.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row g-3">

    {{-- Infos de la demande --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                Informations de la demande
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Numéro</th>
                        <td>{{ $demande->num_demande }}</td>
                    </tr>
                    <tr>
                        <th>Agent</th>
                        <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    </tr>
                    <tr>
                        <th>Date début</th>
                        <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Date fin</th>
                        <td>{{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Durée</th>
                        <td>
                            {{ \Carbon\Carbon::parse($demande->date_debut)
                                ->diffInDays($demande->date_fin) }} jour(s)
                        </td>
                    </tr>
                    <tr>
                        <th>Motif</th>
                        <td>{{ $demande->motif }}</td>
                    </tr>
                    <tr>
                        <th>Intérimaire</th>
                        <td>{{ $demande->interimaire ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Statut</th>
                        <td>
                            <span class="badge-statut badge-{{ $demande->statut }}">
                                {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Suivi des avis --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                Suivi des avis
            </div>
            <div class="card-body">
                @forelse($demande->avisAbsences as $avis)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:36px;height:36px;background:#1e2a3a;color:white;font-size:12px;flex-shrink:0">
                        {{ strtoupper(substr($avis->type, 0, 2)) }}
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:13px">
                            {{ ucfirst(str_replace('_', ' ', $avis->type)) }}
                        </div>
                        <span class="badge-statut badge-{{ $avis->avis }}">
                            {{ ucfirst($avis->avis) }}
                        </span>
                        @if($avis->commentaire)
                            <div class="text-muted mt-1" style="font-size:12px">
                                {{ $avis->commentaire }}
                            </div>
                        @endif
                        <div class="text-muted" style="font-size:11px">
                            {{ $avis->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center">Aucun avis pour le moment</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection