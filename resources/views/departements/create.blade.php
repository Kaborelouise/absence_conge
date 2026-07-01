@extends('layouts.app')
@section('title', 'Nouveau département')
@section('page-title', 'Départements')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
                        <div class="card-header text-white text-center"
                    style="background-color: #1B384F; padding: 20px;">
                    <h5 class="mb-0">Ajouter un département</h5>
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
                               placeholder="Ex: DDM" maxlength="50">
                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Libellé long <span class="text-danger">*</span></label>
                        <input type="text" name="libelle_long"
                               class="form-control @error('libelle_long') is-invalid @enderror"
                               value="{{ old('libelle_long') }}"
                               placeholder="Ex: Département du Développement et de la Mainteanance ">
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
                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                        <a href="{{ route('departements.index') }}" class="btn btn-secondary px-4">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection