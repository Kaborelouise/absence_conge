@extends('layouts.app')
@section('title', 'Nouvel utilisateur')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>Créer un utilisateur
                </h5>
            </div>
            <div class="card-body">

                {{-- Affiche toutes les erreurs de validation --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('utilisateurs.store') }}"
                      method="POST" id="formUser">
                    @csrf

                    <div class="row g-3">

                        {{-- Matricule --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Matricule <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="matricule"
                                   class="form-control @error('matricule') is-invalid @enderror"
                                   value="{{ old('matricule') }}"
                                   required>
                            @error('matricule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nom --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom') }}"
                                   required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Prénom --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Prénom <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="prenom"
                                   class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom') }}"
                                   required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Poste --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Poste <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="poste"
                                   class="form-control @error('poste') is-invalid @enderror"
                                   value="{{ old('poste') }}"
                                   required>
                            @error('poste')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Mot de passe --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Mot de passe <span class="text-danger">*</span>
                            </label>
                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Rôle --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Rôle <span class="text-danger">*</span>
                            </label>
                            <select name="role_id"
                                    class="form-select @error('role_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Choisir un rôle --</option>
                                {{-- On boucle sur $roles envoyé par le controller
                                     $role->id c'est la valeur qu'on envoieau serveur
                                     $role->libelle s'est le texte visible par l'utilisateur --}}
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Département --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Département <span class="text-danger">*</span>
                            </label>
                            <select name="departement_id"
                                    class="form-select @error('departement_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Choisir un département --</option>
                                {{-- On affiche "Département (Direction)" pour plus de clarté --}}
                                @foreach($departements as $dep)
                                    <option value="{{ $dep->id }}"
                                        {{ old('departement_id') == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->libelle_long }}
                                        ({{ $dep->direction->libelle_court ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('departement_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Solde congé --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde congé (jours)</label>
                            <input type="number"
                                   name="solde_conge"
                                   class="form-control"
                                   value="{{ old('solde_conge', 30) }}">
                            {{-- 30 : valeur par défaut --}}
                        </div>

                        {{-- Solde absence --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde absence (jours)</label>
                            <input type="number"
                                   name="solde_absence"
                                   class="form-control"
                                   value="{{ old('solde_absence', 10) }}">
                            {{-- 10 : valeur par défaut --}}
                        </div>

                        {{-- Responsabilités --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Responsabilités</label>
                            <div class="form-check">
                                <input type="checkbox"
                                       name="est_responsable_departement"
                                       value="1"
                                       class="form-check-input"
                                       id="resp_dep"
                                       {{ old('est_responsable_departement') ? 'checked' : '' }}>
                                <label class="form-check-label" for="resp_dep">
                                    Chef de département
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox"
                                       name="est_responsable_direction"
                                       value="1"
                                       class="form-check-input"
                                       id="resp_dir"
                                       {{ old('est_responsable_direction') ? 'checked' : '' }}>
                                <label class="form-check-label" for="resp_dir">
                                    Responsable de direction
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('utilisateurs.index') }}"
                           class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection