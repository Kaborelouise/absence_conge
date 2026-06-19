@extends('layouts.app')
@section('title', 'Détail demande de jouissance')
@section('page-title', 'Demande de jouissance de congé')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail / Suivi de la demande</h5>
    <a href="{{ route('demande_jouissances.index') }}" class="btn btn-sm btn-secondary">
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
                        <th class="ps-3" style="width:40%">Numéro</th>
                        <td>{{ $demande->num_demande }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Agent</th>
                        <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Département</th>
                        <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Direction</th>
                        <td>{{ $demande->user->departement->direction->libelle_court ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Date début</th>
                        <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Date fin</th>
                        <td>{{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Nombre de jours</th>
                        <td>{{ $demande->nombre_jour }} jour(s)</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Statut</th>
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

    <div class="col-md-6">

        <div class="card shadow-sm mb-3">
            <div class="card-header text-white" style="background:#1e2a3a;">
                <i class="bi bi-diagram-3 me-2"></i> Suivi du circuit
            </div>
            <div class="card-body">
                @forelse($demande->avis as $avis)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:38px;height:38px;background:#1e2a3a;color:white;font-size:11px;">
                        {{ strtoupper(substr($avis->type, 0, 2)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size:13px;">
                            {{ ucfirst(str_replace('_', ' ', $avis->type)) }}
                        </div>
                        <span class="badge-statut badge-{{ $avis->avis === 'favorable' ? 'validee' : 'rejetee' }}">
                            {{ ucfirst($avis->avis) }}
                        </span>
                        @if($avis->commentaire)
                            <div class="text-muted mt-1" style="font-size:12px;">
                                {{ $avis->commentaire }}
                            </div>
                        @endif
                        <div class="text-muted" style="font-size:11px;">
                            {{ $avis->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                @empty
                    <p class="text-muted text-center mb-0">
                        <i class="bi bi-hourglass me-1"></i>
                        Aucun avis pour le moment
                    </p>
                @endforelse

                @if(!in_array($demande->statut, ['validee', 'rejetee']) && $prochainActeur)
                    <div class="alert alert-info mb-0 mt-2 py-2" style="font-size:12px;">
                        <i class="bi bi-clock me-1"></i>
                        En attente de : <strong>{{ ucfirst(str_replace('_', ' ', $prochainActeur)) }}</strong>
                    </div>
                @endif
            </div>
        </div>

        @if($peutAgir)
        <div class="card shadow-sm border-primary">
            <div class="card-header text-white" style="background:#1976D2;">
                <i class="bi bi-pencil-square me-2"></i>
                @if(in_array(auth()->user()->role->libelle, ['sg','dg','pca']))
                    Valider ou rejeter la demande
                @else
                    Donner votre avis
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('avis_jouissances.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="demande_jouissance_id" value="{{ $demande->id }}">

                    @if(auth()->user()->role->libelle === 'agent_rh')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Solde congé restant de l'agent</label>
                        <input type="text" class="form-control" readonly
                               value="{{ $demande->user->solde_conge }} jours">
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            @if(in_array(auth()->user()->role->libelle, ['sg','dg','pca']))
                                Décision
                            @else
                                Avis
                            @endif
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="avis" value="favorable" id="favorable" required>
                                <label class="form-check-label text-success fw-bold" for="favorable">
                                    <i class="bi bi-check-circle me-1"></i> Favorable / Valider
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="avis" value="defavorable" id="defavorable">
                                <label class="form-check-label text-danger fw-bold" for="defavorable">
                                    <i class="bi bi-x-circle me-1"></i> Défavorable / Rejeter
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Commentaire <span class="text-muted fw-normal">(optionnel)</span>
                        </label>
                        <textarea name="commentaire" class="form-control" rows="3"
                                  placeholder="Motif du rejet ou remarques..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> Soumettre
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection