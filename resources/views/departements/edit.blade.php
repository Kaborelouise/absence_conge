@extends('layouts.app')
@section('title', 'Modifier département')
@section('page-title', 'Départements')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Modifier le département</h5>
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
                <form action="{{ route('departements.update', $departement->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Libellé court</label>
                        <input type="text" name="libelle_court"
                               class="form-control @error('libelle_court') is-invalid @enderror"
                               value="{{ old('libelle_court', $departement->libelle_court) }}"
                               maxlength="50">
                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Libellé long</label>
                        <input type="text" name="libelle_long"
                               class="form-control @error('libelle_long') is-invalid @enderror"
                               value="{{ old('libelle_long', $departement->libelle_long) }}">
                        @error('libelle_long')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                   
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Mettre à jour</button>
                        <a href="{{ route('departements.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection