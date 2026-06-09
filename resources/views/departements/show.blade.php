@extends('layouts.app')
@section('title', 'Nouveau département')
@section('page-title', 'Départements')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Créer un département</h5>
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
                <form action="{{ route('departements.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Libellé court <span class="text-danger">*</span></label>
                        <input type="text" name="libelle_court"
                               class="form-control @error('libelle_court') is-invalid @enderror"
                               value="{{ old('libelle_court') }}"
                               placeholder="Ex: INFO" maxlength="50">
                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Libellé long <span class="text-danger">*</span></label>
                        <input type="text" name="libelle_long"
                               class="form-control @error('libelle_long') is-invalid @enderror"
                               value="{{ old('libelle_long') }}"
                               placeholder="Ex: Département Informatique">
                        @error('libelle_long')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Direction <span class="text-danger">*</span></label>
                        <select name="direction_id"
                                class="form-select @error('direction_id') is-invalid @enderror">
                            <option value="">-- Choisir une direction --</option>
                            @foreach($directions as $direction)
                                <option value="{{ $direction->id }}"
                                    {{ old('direction_id') == $direction->id ? 'selected' : '' }}>
                                    {{ $direction->libelle_long }}
                                </option>
                            @endforeach
                        </select>
                        @error('direction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('departements.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection