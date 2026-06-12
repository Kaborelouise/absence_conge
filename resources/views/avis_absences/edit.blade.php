@extends('layouts.app')
@section('title', 'Modifier l\'avis')
@section('page-title', 'Autorisation d\'absence')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

        {{-- Récapitulatif de la demande --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">
                    Demande concernée — N° {{ $demande->num_demande }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-2" style="font-size:13px">
                    <div class="col-md-4">
                        <span class="text-muted">Agent :</span>
                        <strong>{{ $demande->user->nom }} {{ $demande->user->prenom }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Période :</span>
                        <strong>
                            {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
                            au
                            {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>Modifier mon avis
                </h5>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('avis_absences.update', $avis->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Décision --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Décision</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input type="radio" name="avis" value="favorable"
                                       class="form-check-input" id="favorable"
                                       {{ old('avis', $avis->avis) == 'favorable' ? 'checked' : '' }}>
                                <label class="form-check-label text-success fw-bold"
                                       for="favorable">✓ Favorable</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="avis" value="defavorable"
                                       class="form-check-input" id="defavorable"
                                       {{ old('avis', $avis->avis) == 'defavorable' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-bold"
                                       for="defavorable">✗ Défavorable</label>
                            </div>
                        </div>
                    </div>

                    {{-- Commentaire --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Commentaire</label>
                        <textarea name="commentaire"
                                  class="form-control"
                                  rows="3">{{ old('commentaire', $avis->commentaire) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('demande_absences.show', $demande->id) }}"
                           class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection