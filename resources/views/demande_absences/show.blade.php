@extends('layouts.app')
@section('title', 'Détail demande')
@section('page-title', 'Autorisation d\'absence')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail /Suivi de la demande</h5>
    <a href="{{ route('demande_absences.index') }}" class="btn btn-sm btn-secondary">
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
           <div class="card-header card-header-anptic">
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
                        <th class="ps-3">Durée</th>
                        <td>
                            {{ \Carbon\Carbon::parse($demande->date_debut)->diffInDays($demande->date_fin) }} jour(s)
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Motif</th> 
                        <td>
                            @php
                                $motifLabels = [
                                    'evenement_familliaux'                 => 'Évènements familiaux (décès)',
                                    'jouissance_de_reliquat_de_congé_paye' => 'Jouissance de reliquats de congés payés',
                                    'convenances_personnelles'             => 'Convenances personnelles',
                                    'autre'                                => 'Autre',
                                ];
                            @endphp
                            {{ $motifLabels[$demande->motif] ?? $demande->motif }}
                        </td>
                    </tr>
                    <tr>
                        <th class="ps-3">Intérimaire</th>
                        <td>{{ $demande->interimaire ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Retenue salaire</th>
                        <td>{{ $demande->retenue_salaire ? 'Oui' : 'Non' }}</td>
                    </tr>
                    <tr>
                        <th class="ps-3">Étape actuelle</th>
                        <td>
                            @php
                                $etapeLabels = [
                                    'chef_departement'      => 'Avis Chef Département',
                                    'responsable_direction' => 'Avis Responsable Direction',
                                    'agent_rh'              => 'Vérification RH',
                                    'sg'                    => 'Validation Secrétaire Général',
                                    'dg'                    => 'Validation Directeur Général',
                                    'pca'                   => 'Validation PCA',
                                ];
                            @endphp

                           @if($demande->statut === 'validee' && $demande->user_id === auth()->id())
                            <a href="{{ route('demande_absences.telecharger', $demande->id) }}" class="btn btn-success">
                            <i class="bi bi-download me-1"></i> Télécharger l'autorisation
                            </a>
                        
                            @elseif($demande->statut === 'rejetee')
                                <span class="badge-statut badge-rejetee">Rejetée</span>

                            @elseif($demande->statut === 'en_attente')
                                <span class="badge-statut badge-en_attente">
                                    Initiation — en attente de :
                                    {{ $etapeLabels[$prochainActeur] ?? $prochainActeur }}
                                </span>

                            @else
                                <span class="badge-statut badge-en_cours">
                                    En cours — {{ $etapeLabels[$prochainActeur] ?? $prochainActeur }}
                                </span>
                            @endif
                            
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">

        <div class="card shadow-sm mb-3">
            <div class="card-header card-header-anptic">
                <i class="bi bi-diagram-3 me-2"></i> Suivi du circuit
            </div>
            <div class="card-body">

                @forelse($demande->avisAbsence as $avis)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:38px;height:38px;background:#1B384F;color:white;font-size:11px;">
                        {{ strtoupper(substr($avis->type, 0, 2)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size:13px;">
                            {{ $etapeLabels[$avis->type] ?? ucfirst(str_replace('_', ' ', $avis->type)) }}
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
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Prochaine étape :
                        <strong>{{ $etapeLabels[$prochainActeur] ?? $prochainActeur }}</strong>
                    </div>
                @endif

            </div>
        </div>

        @if($peutAgir)
        <div class="d-grid">
            <button type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAvis">
                <i class="bi bi-pencil-square me-2"></i>
                @if(in_array(auth()->user()->role->libelle, ['sg','dg','pca']))
                    Valider ou rejeter la demande
                @else
                    Donner mon Avis 
                @endif
            </button>
        </div>
        @endif

    </div>
</div>

@if($peutAgir)
<div class="modal fade" id="modalAvis" tabindex="-1" aria-labelledby="modalAvisLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header text-white" style="background:#1B384F;">
                <h5 class="modal-title" id="modalAvisLabel">
                    <i class="bi bi-pencil-square me-2"></i>
                    @if(in_array(auth()->user()->role->libelle, ['sg','dg','pca']))
                        Valider ou rejeter la demande
                    @else
                        Donner mon avis
                    @endif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            @if($demande->abandonnee ?? false)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Cette demande a été <strong>abandonnée</strong> par l'agent.
        Elle ne peut plus être traitée.
    </div>
@endif

            @if($demande->abandonnee ?? false)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Cette demande a été <strong>abandonnée</strong> par l'agent.
        Elle ne peut plus être traitée.
    </div>
@endif
            <form action="{{ route('avis_absences.store') }}" method="POST">
                @csrf
                <input type="hidden" name="demande_absence_id" value="{{ $demande->id }}">

                <div class="modal-body">

                    @if(auth()->user()->role->libelle === 'agent_rh')
                    <div class="alert alert-info py-2 mb-3" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Solde d'absence restant de l'agent :
                        <strong>{{ $demande->user->solde_absence }} jours</strong>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="retenue_salaire" id="retenue_salaire" value="1"
                                   {{ $demande->retenue_salaire ? 'checked' : '' }}>
                            <label class="form-check-label" for="retenue_salaire">
                                Avec retenue sur salaire
                            </label>
                        </div>
                    </div>
                    @endif

                    @if(in_array(auth()->user()->role->libelle, ['chef_departement', 'responsable_direction']))
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Intérimaire désigné
                            <span class="text-muted fw-normal">(optionnel)</span>
                        </label>
                        <select name="interimaire" class="form-select">
                            <option value=""> Choisir un intérimaire </option>
                            @foreach($agentsMemeDepartement as $agent)
                                <option value="{{ $agent->nom }} {{ $agent->prenom }}"
                                    {{ $demande->interimaire === $agent->nom.' '.$agent->prenom ? 'selected' : '' }}>
                                    {{ $agent->nom }} {{ $agent->prenom }} — {{ $agent->poste }}
                                </option>
                            @endforeach
                        </select>
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
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="avis" value="favorable"
                                       id="favorable" required
                                       onchange="toggleMotif(this.value)">
                                <label class="form-check-label text-success fw-bold" for="favorable">
                                    <i class="bi bi-check-circle me-1"></i> Favorable
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="avis" value="defavorable"
                                       id="defavorable"
                                       onchange="toggleMotif(this.value)">
                                <label class="form-check-label text-danger fw-bold" for="defavorable">
                                    <i class="bi bi-x-circle me-1"></i> Défavorable
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" id="labelCommentaire">
                            Commentaire
                            <span class="text-muted fw-normal">(optionnel)</span>
                        </label>
                        <textarea name="commentaire"
                                  id="commentaire"
                                  class="form-control" rows="3"
                                  placeholder="Remarques éventuelles..."></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send me-1"></i> Soumettre  
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endif

@if(isset($peutAbandonner) && $peutAbandonner)
<div class="modal fade" id="modalAbandonnerAbsence" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background:#fd7e14;">
                <h5 class="modal-title">
                    <i class="bi bi-x-octagon me-2"></i> Abandonner la demande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir abandonner cette demande ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form action="{{ route('demande_absences.abandonner', $demande->id) }}"
                      method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-x-octagon me-1"></i> Oui, abandonner
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function toggleMotif(valeur) {
    const label       = document.getElementById('labelCommentaire');
    const commentaire = document.getElementById('commentaire');

    if (valeur === 'defavorable') {
        label.innerHTML         = '<i class="bi bi-exclamation-triangle me-1 text-danger"></i>'
                                + '<span class="text-danger fw-bold">Motif du refus *</span>';
        commentaire.classList.add('border-danger');
        commentaire.placeholder = 'Expliquez la raison de votre refus';
        commentaire.required    = true;
    } else {
        label.innerHTML         = 'Commentaire <span class="text-muted fw-normal">(optionnel)</span>';
        commentaire.classList.remove('border-danger');
        commentaire.placeholder = 'Remarques éventuelles...';
        commentaire.required    = false;
    }
}
</script>
@endsection