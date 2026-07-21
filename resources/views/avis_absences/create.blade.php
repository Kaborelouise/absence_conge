{{-- ============================================================
     FICHIER : resources/views/avis_absences/create.blade.php
     RÔLE    : Formulaire pour donner un avis sur une demande
     DONNÉES : $demande vient du controller
     ACCÈS   : Depuis show(demande_absence) via bouton "Donner avis"
     ============================================================ --}}
@extends('layouts.app')
@section('title', 'Donner un avis')
@section('page-title', 'Autorisation d\'absence')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

        {{-- Récapitulatif de la demande
             Pour que le responsable sache sur quoi il donne son avis --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>
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
                    <div class="col-md-4">
                        <span class="text-muted">Motif :</span>
                        <strong>{{ $demande->motif }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulaire pour donner un avis --}}
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-chat-square-text me-2"></i>
                    Donner mon avis
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

                <form action="{{ route('avis_absences.store') }}"
                      method="POST">
                    @csrf

                    {{-- Champ caché : id de la demande envoyé automatiquement sans que l'utilisateur
                         n'ait à le saisir --}}
                    <input type="hidden"
                           name="demande_absence_id"
                           value="{{ $demande->id }}">

                    {{-- Type d'avis (qui donne l'avis) --}}
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
                            <option value="Responsable Direction"
                                {{ old('type') == 'Responsable Direction' ? 'selected' : '' }}>
                                Responsable de direction
                            </option>
                            <option value="Agent RH"
                                {{ old('type') == 'Agent RH' ? 'selected' : '' }}>
                                Agent RH
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

                    {{-- Décision --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Décision <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-3">
                            {{-- Boutons radio Bootstrap --}}
                            <div class="form-check">
                                <input type="radio"
                                       name="avis"
                                       value="favorable"
                                       class="form-check-input"
                                       id="favorable"
                                       {{ old('avis') == 'favorable' ? 'checked' : '' }}>
                                <label class="form-check-label text-success fw-bold"
                                       for="favorable">
                                    ✓ Favorable
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio"
                                       name="avis"
                                       value="defavorable"
                                       class="form-check-input"
                                       id="defavorable"
                                       {{ old('avis') == 'defavorable' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-bold"
                                       for="defavorable">
                                    ✗ Défavorable
                                </label>
                            </div>
                        </div>
                        @error('avis')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Commentaire --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Commentaire / Motif
                        </label>
                        <textarea name="commentaire"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Expliquer votre décision...">{{ old('commentaire') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Soumettre l'avis
                        </button>
                        {{-- Retour vers le détail de la demande --}}
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