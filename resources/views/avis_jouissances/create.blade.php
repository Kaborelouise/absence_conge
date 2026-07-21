@extends('layouts.app')
@section('title', 'Donner un avis jouissance')
@section('page-title', 'Jouissance de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Demande N° {{ $demande->num_demandes }}</h6>
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
                            au {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}
                        </strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Durée :</span>
                        <strong>{{ $demande->nombre_jour }} jour(s)</strong>
                    </div>
                </div>
            </div>
        </div> 
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-chat-square-text me-2"></i>Donner mon avis
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

                <form action="{{ route('avis_jouissances.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="demande_jouissance_id" value="{{ $demande->id }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Type d'avis <span class="text-danger">*</span>
                        </label>
                        <select name="type"
                                class="form-select @error('type') is-invalid @enderror"
                                required>
                            <option value="">-- Choisir --</option>
                            <option value="Chef de Département"
                                {{ old('type') == 'Chef de Département' ? 'selected' : '' }}>
                                Chef de département
                            </option>
                            <option value="Agent RH"
                                {{ old('type') == 'Agent RH' ? 'selected' : '' }}>
                                Agent RH
                            </option>
                            <option value="Responsable Direction"
                                {{ old('type') == 'Responsable Direction' ? 'selected' : '' }}>
                                Responsable de direction
                            </option>
                            <option value="SG"
                                {{ old('type') == 'SG' ? 'selected' : '' }}>
                                Secrétaire Général (SG)
                            </option>
                            <option value="DG"
                                {{ old('type') == 'DG' ? 'selected' : '' }}>
                                Directeur Général (DG)
                            </option>
                            <option value="PCA"
                                {{ old('type') == 'PCA' ? 'selected' : '' }}>
                                PCA
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Décision <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input type="radio" name="avis" value="favorable"
                                       class="form-check-input" id="favorable"
                                       {{ old('avis') == 'favorable' ? 'checked' : '' }}>
                                <label class="form-check-label text-success fw-bold"
                                       for="favorable">✓ Favorable</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="avis" value="defavorable"
                                       class="form-check-input" id="defavorable"
                                       {{ old('avis') == 'defavorable' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-bold"
                                       for="defavorable">✗ Défavorable</label>
                            </div>
                        </div>
                        @error('avis')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="3"
                                  placeholder="Motif...">{{ old('commentaire') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Soumettre l'avis
                        </button>
                        <a href="{{ route('demande_jouissances.show', $demande->id) }}"
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