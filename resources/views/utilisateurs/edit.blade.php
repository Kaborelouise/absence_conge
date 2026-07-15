@extends('layouts.app')
@section('title', 'Modifier utilisateur')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center"
                    style="background-color: #1B384F; padding: 20px;">
                    <h5 class="mb-0">Modifier l'utilisateur</h5>
                </div>
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('utilisateurs.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Matricule</label>
                            <input type="number" name="matricule"
                                   class="form-control @error('matricule') is-invalid @enderror"
                                   value="{{ old('matricule', $user->matricule) }}" required>
                            @error('matricule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom"
                                   class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $user->nom) }}" required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom"
                                   class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom', $user->prenom) }}" required>
                            @error('prenom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Mot de passe
                                <span class="text-muted fw-normal">(laisser vide pour ne pas changer)</span>
                            </label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Poste</label>
                            <input type="text" name="poste"
                                   class="form-control @error('poste') is-invalid @enderror"
                                   value="{{ old('poste', $user->poste) }}" required>
                            @error('poste')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Signature</label>
                            <input type="text" name="signature"
                                   class="form-control"
                                   value="{{ old('signature', $user->signature) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Rôle</label>
                            <select name="role_id"
                                    class="form-select @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role->libelle)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Département</label>
                            <select name="departement_id"
                                    class="form-select @error('departement_id') is-invalid @enderror" required>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}"
                                        {{ old('departement_id', $user->departement_id) == $departement->id ? 'selected' : '' }}>
                                        {{ $departement->libelle_court }} ({{ $departement->direction->libelle_court ?? '—' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('departement_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="est_responsable_departement" value="1" id="est_chef_dept"
                                       {{ old('est_responsable_departement', $user->est_responsable_departement) ? 'checked' : '' }}>
                                <label class="form-check-label" for="est_chef_dept">
                                    Responsable de département
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="est_responsable_direction" value="1" id="est_resp_dir"
                                       {{ old('est_responsable_direction', $user->est_responsable_direction) ? 'checked' : '' }}>
                                <label class="form-check-label" for="est_resp_dir">
                                    Responsable de direction
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Solde congé (jours)</label>
                            <input type="number" name="solde_conge"
                                   class="form-control" value="{{ old('solde_conge', $user->solde_conge) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Solde absence (jours)</label>
                            <input type="number" name="solde_absence"
                                   class="form-control" value="{{ old('solde_absence', $user->solde_absence) }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary  px-4"> Enregistrer
                        </button>
                        <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary px-4">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection