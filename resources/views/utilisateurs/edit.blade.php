{{--
    le fichierresources/views/utilisateurs/edit.blade.php a pour role d'afficher le formulaire de modification utilisateur
donnée : $utilisateur, $roles, $departements → UserController@edit --}}
@extends('layouts.app')
@section('title', 'Modifier utilisateur')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>
                    Modifier — {{ $utilisateur->nom }} {{ $utilisateur->prenom }}
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

                {{-- route .update avec l'id de l'utilisateur --}}
                <form action="{{ route('utilisateurs.update', $utilisateur->id) }}"
                      method="POST">
                    @csrf
                    {{-- @method('PUT') : simuler PUT car HTML ne le supporte pas --}}
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Matricule</label>
                            <input type="number" name="matricule"
                                   class="form-control @error('matricule') is-invalid @enderror"
                                   value="{{ old('matricule', $utilisateur->matricule) }}"
                                   required>
                            @error('matricule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" name="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $utilisateur->nom) }}"
                                   required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" name="prenom"
                                   class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom', $utilisateur->prenom) }}"
                                   required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Poste</label>
                            <input type="text" name="poste"
                                   class="form-control"
                                   value="{{ old('poste', $utilisateur->poste) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $utilisateur->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Nouveau mot de passe
                                <small class="text-muted">(laisser vide pour ne pas changer)</small>
                            </label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rôle</label>
                            <select name="role_id" class="form-select">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{-- Pré-sélectionne le rôle actuel --}}
                                        {{ old('role_id', $utilisateur->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Département</label>
                            <select name="departement_id" class="form-select">
                                @foreach($departements as $dep)
                                    <option value="{{ $dep->id }}"
                                        {{-- Pré-sélectionne le département actuel --}}
                                        {{ old('departement_id', $utilisateur->departement_id) == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->libelle_long }}
                                        ({{ $dep->direction->libelle_court ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde congé (jours)</label>
                            <input type="number" name="solde_conge"
                                   class="form-control"
                                   value="{{ old('solde_conge', $utilisateur->solde_conge) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde absence (jours)</label>
                            <input type="number" name="solde_absence"
                                   class="form-control"
                                   value="{{ old('solde_absence', $utilisateur->solde_absence) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Responsabilités</label>
                            <div class="form-check">
                                <input type="checkbox"
                                       name="est_responsable_departement"
                                       value="1"
                                       class="form-check-input"
                                       id="resp_dep"
                                       {{-- Pré-coche si déjà responsable --}}
                                       {{ $utilisateur->est_responsable_departement ? 'checked' : '' }}>
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
                                       {{ $utilisateur->est_responsable_direction ? 'checked' : '' }}>
                                <label class="form-check-label" for="resp_dir">
                                    Responsable de direction
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
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