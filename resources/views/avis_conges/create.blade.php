@extends('layouts.app')
@section('title', 'Donner un avis congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

        {{-- Récapitulatif --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Demande concernée</h6>
            </div>
            <div class="card-body">
                <div class="row g-2" style="font-size:13px">
                    <div class="col-md-6">
                        <span class="text-muted">Agent :</span>
                        <strong>{{ $demande->user->nom }} {{ $demande->user->prenom }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted">Lieu jouissance :</span>
                        <strong>{{ $demande->lieu_jouissance }}</strong>
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

                <form action="{{ route('avis_conges.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="demande_conge_id" value="{{ $demande->id }}">

                    {{-- Type : seulement Agent RH pour les congés
                         Basé sur workflow slide 6 --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type d'avis</label>
                        <input type="text" class="form-control" value="Agent RH" readonly>
                        <input type="hidden" name="type" value="Agent RH">
                    </div>

                    {{-- Décision --}}
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

                    {{-- Commentaire --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="3"
                                  placeholder="Motif de votre décision...">{{ old('commentaire') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Soumettre l'avis
                        </button>
                        <a href="{{ route('demande_conges.show', $demande->id) }}"
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