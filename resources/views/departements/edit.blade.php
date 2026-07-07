@extends('layouts.app')
@section('title', 'Modifier le département')
@section('page-title', 'Gestion des départements')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center"
                    style="background-color: #1B384F; padding: 20px;">
                <h5 class="mb-0">Modifier le département</h5>
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
                <form action="{{ route('departements.update', $departement->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Libellé court</label>
                            <input type="text" name="libelle_court"
                                   class="form-control @error('libelle_court') is-invalid @enderror"
                                   value="{{ old('libelle_court', $departement->libelle_court) }}"
                                   required>
                            @error('libelle_court')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Libellé long</label>
                            <input type="text" name="libelle_long"
                                   class="form-control @error('libelle_long') is-invalid @enderror"
                                   value="{{ old('libelle_long', $departement->libelle_long) }}"
                                   required>
                            @error('libelle_long')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Direction de rattachement</label>
                        <select name="direction_id"
                                class="form-select @error('direction_id') is-invalid @enderror"
                                required>
                            @foreach($directions as $direction)
                                <option value="{{ $direction->id }}"
                                    {{ old('direction_id', $departement->direction_id) == $direction->id ? 'selected' : '' }}>
                                    {{ $direction->libelle_court }} — {{ $direction->libelle_long }}
                                </option>
                            @endforeach
                        </select>
                        @error('direction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($departement->user()->count() > 0)
                        <div class="alert alert-warning py-2" style="font-size:12px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Attention : {{ $departement->user()->count() }} utilisateur(s)
                            sont rattachés à ce département. Changer sa direction
                            les affecte aussi dans le circuit de validation.
                        </div>
                    @endif

                    <div class="d-flex justify-content-center gap-3">
                         <button type="submit" class="btn btn-primary px-4"> Enregistrer</button>
                        </button>
                        <a href="{{ route('departements.index') }}" class="btn btn-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection