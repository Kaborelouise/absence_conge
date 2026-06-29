@extends('layouts.app')
@section('title', 'Détail demande de jouissance')
@section('page-title', 'Demande de jouissance de congé')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail / Suivi de la demande #{{ $demande->num_demande }}</h5>
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

@if($demande->abandonnee ?? false)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Cette demande a été <strong>abandonnée</strong> par l'agent.
        Elle ne peut plus être traitée.
    </div>
@endif

<div class="row g-3">

    {{-- Colonne de gauche --}}
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header card-header-anptic">
                <i class="bi bi-file-text me-2"></i> Informations de la demande
            </div>
            <div class="card-body p-0">

                @php
                    $etapeLabels = [
                        'chef_departement'      => 'Avis Chef de département',
                        'agent_rh'              => 'Avis Agent RH',
                        'responsable_direction' => 'Décision Directeur de Service',
                        'sg'                    => 'Décision Secrétaire Général',
                        'dg'                    => 'Décision Directeur Général',
                        'pca'                   => 'Décision PCA',
                    ];
                @endphp

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
                        <th class="ps-3">Étape actuelle</th>
                        <td>
                            @if($demande->abandonnee ?? false)
                                <span class="badge bg-warning text-dark">Abandonnée</span>
                            @elseif($demande->statut === 'validee')
                                <span class="badge-statut badge-validee">Validée</span>
                            @elseif($demande->statut === 'rejetee')
                                <span class="badge-statut badge-rejetee">Rejetée</span>
                            @elseif($derniereEtape)
                                <span class="badge-statut badge-en_cours">
                                    {{ $etapeLabels[$derniereEtape] ?? $derniereEtape }}
                                </span>
                            @else
                                <span class="badge-statut badge-en_attente">Initiation</span>
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

                @forelse($demande->avis as $avis)
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

                @if(!($demande->abandonnee ?? false) && !in_array($demande->statut, ['validee', 'rejetee']) && $prochainActeur)
                    <div class="alert alert-info mb-0 mt-2 py-2" style="font-size:12px;">
                        <i class="bi bi-clock me-1"></i>
                        En attente de :
                        <strong>{{ $etapeLabels[$prochainActeur] ?? $prochainActeur }}</strong>
                    </div>
                @endif

            </div>
        </div>

        {{-- Bouton donner avis --}}
        @if($peutAgir && !($demande->abandonnee ?? false) && !in_array($demande->statut, ['validee', 'rejetee']))
        <div class="d-grid mb-3">
            <button type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAvisJouissance">
                <i class="bi bi-pencil-square me-2"></i>
                @if(in_array(auth()->user()->role->libelle, ['responsable_direction','sg','dg','pca']))
                    Prendre ma décision
                @else
                    Donner mon avis
                @endif
            </button>
        </div>
        @endif

        {{-- CORRECTION : bouton Abandonner sans modal, même style que Supprimer --}}
        @if(isset($peutAbandonner) && $peutAbandonner)
        <div class="d-grid mb-3">
            <form action="{{ route('demande_jouissances.abandonner', $demande->id) }}"
                  method="POST">
                @csrf
                <button type="submit" class="btn btn-warning w-100"
                        onclick="return confirm('Abandonner cette demande ?')">
                    <i class="bi bi-x-octagon me-2"></i> Abandonner la demande
                </button>
            </form>
        </div>
        @endif

        @if($demande->statut === 'validee' && $demande->user_id === auth()->id())
        <div class="card shadow-sm border-success">
            <div class="card-header text-white" style="background:#198754;">
                <i class="bi bi-check-circle me-2"></i>
                @if($demande->estCloturee())
                    Demande clôturée
                @else
                    Demande validée — Clôture
                @endif
            </div>
            <div class="card-body">

                @if($demande->estCloturee())
                    <div class="alert alert-success text-center">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Demande clôturée le
                        <strong>{{ $demande->cloturee_at->format('d/m/Y à H:i') }}</strong>
                    </div>
                @else
                    {{-- ÉTAPE 1 --}}
                    <div class="mb-4">
                        <h6 class="fw-bold">
                            <span class="badge bg-primary me-2">1</span>
                            Télécharger et imprimer les certificats
                        </h6>
                        <p class="text-muted" style="font-size:12px;">
                            Téléchargez les certificats, imprimez-les et faites-les
                            signer par les responsables concernés.
                        </p>
                        <a href="{{ route('demande_jouissances.telecharger', $demande->id) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download me-1"></i>
                            Télécharger les certificats
                        </a>
                    </div>
                    <hr>

                    {{-- ÉTAPE 2 --}}
                    <div class="mb-4">
                        <h6 class="fw-bold">
                            <span class="badge {{ $demande->certificat_cessation ? 'bg-success' : 'bg-secondary' }} me-2">2</span>
                            Certificat de cessation de service
                            @if($demande->certificat_cessation)
                                <i class="bi bi-check-circle-fill text-success ms-1"></i>
                            @endif
                        </h6>
                        <p class="text-muted" style="font-size:12px;">
                            Joignez le scan/photo du certificat de cessation signé.
                        </p>
                        @if($demande->certificat_cessation)
                            <div class="alert alert-success py-2 mb-2" style="font-size:12px;">
                                <i class="bi bi-file-earmark-check me-1"></i>
                                Fichier uploadé.
                                <a href="{{ \Storage::url($demande->certificat_cessation) }}"
                                   target="_blank" class="ms-1">Voir le fichier</a>
                            </div>
                        @endif
                        <form action="{{ route('demande_jouissances.upload_cessation', $demande->id) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="file" name="certificat_cessation"
                                       class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload me-1"></i>
                                    {{ $demande->certificat_cessation ? 'Remplacer' : 'Uploader' }}
                                </button>
                            </div>
                            <small class="text-muted">PDF, JPG, PNG — max 5MB</small>
                        </form>
                    </div>
                    <hr>

                    {{-- ÉTAPE 3 --}}
                    <div class="mb-4">
                        <h6 class="fw-bold">
                            <span class="badge {{ $demande->certificat_prise_service ? 'bg-success' : 'bg-secondary' }} me-2">3</span>
                            Certificat de prise de service
                            @if($demande->certificat_prise_service)
                                <i class="bi bi-check-circle-fill text-success ms-1"></i>
                            @endif
                        </h6>
                        <p class="text-muted" style="font-size:12px;">
                            Joignez le scan/photo du certificat de prise de service signé.
                        </p>
                        @if($demande->certificat_prise_service)
                            <div class="alert alert-success py-2 mb-2" style="font-size:12px;">
                                <i class="bi bi-file-earmark-check me-1"></i>
                                Fichier uploadé.
                                <a href="{{ \Storage::url($demande->certificat_prise_service) }}"
                                   target="_blank" class="ms-1">Voir le fichier</a>
                            </div>
                        @endif
                        <form action="{{ route('demande_jouissances.upload_prise_service', $demande->id) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="file" name="certificat_prise_service"
                                       class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload me-1"></i>
                                    {{ $demande->certificat_prise_service ? 'Remplacer' : 'Uploader' }}
                                </button>
                            </div>
                            <small class="text-muted">PDF, JPG, PNG — max 5MB</small>
                        </form>
                    </div>
                    <hr>

                    {{-- ÉTAPE 4 --}}
                    <div>
                        <h6 class="fw-bold">
                            <span class="badge bg-secondary me-2">4</span>
                            Clôturer la demande
                        </h6>
                        <p class="text-muted" style="font-size:12px;">
                            Une fois les deux certificats uploadés, vous pouvez
                            clôturer officiellement la demande.
                        </p>
                        @if($demande->certificat_cessation && $demande->certificat_prise_service)
                            <form action="{{ route('demande_jouissances.cloturer', $demande->id) }}"
                                  method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success px-4"
                                        onclick="return confirm('Clôturer définitivement cette demande ?')">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Clôturer la demande
                                </button>
                            </form>
                        @else
                            <button class="btn btn-success px-4" disabled>
                                <i class="bi bi-check-circle me-1"></i>
                                Clôturer la demande
                            </button>
                            <div class="text-muted mt-1" style="font-size:11px;">
                                <i class="bi bi-info-circle me-1"></i>
                                Uploadez les deux certificats
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Modal donner un avis — INCHANGÉ --}}
@if($peutAgir && !($demande->abandonnee ?? false) && !in_array($demande->statut, ['validee', 'rejetee']))
<div class="modal fade" id="modalAvisJouissance" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header card-header-anptic">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    @if(in_array(auth()->user()->role->libelle, ['responsable_direction','sg','dg','pca']))
                        Prendre ma décision
                    @else
                        Donner mon avis
                    @endif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('avis_jouissances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="demande_jouissance_id" value="{{ $demande->id }}">
                <div class="modal-body">

                    @if(auth()->user()->role->libelle === 'agent_rh')
                    <div class="alert alert-info py-2 mb-3" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Solde congé restant : <strong>{{ $demande->user->solde_conge }} jours</strong>
                        <br>
                        <small class="text-muted">Jours demandés : {{ $demande->nombre_jour }} jour(s)</small>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            @if(in_array(auth()->user()->role->libelle, ['responsable_direction','sg','dg','pca']))
                                Décision
                            @else
                                Avis
                            @endif
                        </label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="avis" value="favorable"
                                       id="favorable_jouissance" required
                                       onchange="toggleMotifJouissance(this.value)">
                                <label class="form-check-label text-success fw-bold"
                                       for="favorable_jouissance">
                                    <i class="bi bi-check-circle me-1"></i> Favorable
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="avis" value="defavorable"
                                       id="defavorable_jouissance"
                                       onchange="toggleMotifJouissance(this.value)">
                                <label class="form-check-label text-danger fw-bold"
                                       for="defavorable_jouissance">
                                    <i class="bi bi-x-circle me-1"></i> Défavorable
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" id="labelCommentaireJouissance">
                            Commentaire
                            <span class="text-muted fw-normal">(optionnel)</span>
                        </label>
                        <textarea name="commentaire"
                                  id="commentaireJouissance"
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

{{-- SUPPRESSION du modal abandonner — remplacé par confirm() directement sur le bouton --}}

@endsection

@section('scripts')
<script>
function toggleMotifJouissance(valeur) {
    const label       = document.getElementById('labelCommentaireJouissance');
    const commentaire = document.getElementById('commentaireJouissance');

    if (valeur === 'defavorable') {
        label.innerHTML         = '<i class="bi bi-exclamation-triangle me-1 text-danger"></i>'
                                + '<span class="text-danger fw-bold">Motif du refus *</span>';
        commentaire.classList.add('border-danger');
        commentaire.placeholder = 'Expliquez la raison du refus...';
        commentaire.required    = true;
    } else {
        label.innerHTML         = 'Commentaire <span class="text-muted fw-normal">(optionnel)</span>';
        commentaire.classList.remove('border-danger');
        commentaire.placeholder = '...';
        commentaire.required    = false;
    }
}
</script>
@endsection