@extends('layouts.app')
@section('title', 'Modifier avis congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>Modifier l'avis
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

                <form action="{{ route('avis_conges.update', $avis->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

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

                    <div class="mb-3">
                        <label class="form-label fw-bold">Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="3">
                            {{ old('commentaire', $avis->commentaire) }}
                        </textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('demande_conges.show', $avis->demande_conge_id) }}"
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