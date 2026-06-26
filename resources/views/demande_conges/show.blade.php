@extends('layouts.app')
@section('title', 'Détail demande de congé')
@section('page-title', 'demande de congé')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail de la demande de congé</h5>
    <a href="{{ route('demande_conges.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
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

<div class="row g-3">

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header text-white" style="background:#1e2a3a;">
                <i class="bi bi-file-text me-2"></i> Informations de la demande
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="ps-3" style="width:40%">Agent</th>
                        <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Département</th>
                        <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Lieu de jouissance</th>
                        <td>{{ $demande->lieu_jouissance }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Statut</th>
                        <td>
                            @if($demande->estCompilee())
                                <span class="badge-statut badge-compilee">Compilée</span>
                            @else
                                <span class="badge-statut badge-en_attente">En attente</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">

        <div class="card shadow-sm mb-3">
            <div class="card-header text-white" style="background:#1e2a3a;">
                <i class="bi bi-diagram-3 me-2"></i> Suivi
            </div>
            <div class="card-body">
                @if($demande->avisConge)
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:38px;height:38px;background:#6f42c1;color:white;font-size:11px;">
                            RH
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:13px;">Agent RH</div>
                            <span class="badge-statut badge-compilee">Compilée</span>
                            @if($demande->avisConge->commentaire)
                                <div class="text-muted mt-1" style="font-size:12px;">
                                    {{ $demande->avisConge->commentaire }}
                                </div>
                            @endif
                            <div class="text-muted" style="font-size:11px;">
                                {{ $demande->avisConge->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">
                        <i class="bi bi-hourglass me-1"></i>
                        En attente de compilation du DRH
                    </p>
                @endif
            </div>
        </div>

        {{-- Bouton compiler est seulement visible pour l'agent RH, si pas déjà compilée --}}
        @if($peutCompiler)
        <div class="card shadow-sm border-primary">
            <div class="card-header text-white" style="background:#1976D2;">
                <i class="bi bi-pencil-square me-2"></i> Compiler la demande
            </div>
            <div class="card-body">
                <form action="{{ route('avis_conges.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="demande_conge_id" value="{{ $demande->id }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Commentaire <span class="text-muted fw-normal">(optionnel)</span>
                        </label>
                        <textarea name="commentaire" class="form-control" rows="3"
                                  placeholder="Remarques..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i> Marquer comme compilée
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection