@extends('layouts.app')
@section('title', 'Modifier utilisateur')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Modifier l'utilisateur</h5>
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
                <form action="{{ route('utilisateurs.update', $utilisateur->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Matricule</label>
                            <input type="number" name="matricule"
                                   class="form-control"
                                   value="{{ old('matricule', $utilisateur->matricule) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" name="nom"
                                   class="form-control"
                                   value="{{ old('nom', $utilisateur->nom) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" name="prenom"
                                   class="form-control"
                                   value="{{ old('prenom', $utilisateur->prenom) }}" required>
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
                                   class="form-control"
                                   value="{{ old('email', $utilisateur->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rôle</label>
                            <select name="role_id" class="form-select">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
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
                                        {{ old('departement_id', $utilisateur->departement_id) == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->libelle_long }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde congé</label>
                            <input type="number" name="solde_conge"
                                   class="form-control"
                                   value="{{ old('solde_conge', $utilisateur->solde_conge) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde absence</label>
                            <input type="number" name="solde_absence"
                                   class="form-control"
                                   value="{{ old('solde_absence', $utilisateur->solde_absence) }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">Mettre à jour</button>
                        <a href="{{ route('utilisateurs.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection