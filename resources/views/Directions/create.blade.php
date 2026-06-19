@extends('layouts.app')
@section('title', 'Nouvelle direction')
@section('page-title', 'Gestion des directions')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color:#1e2a3a; padding: 20px;">
                <h5 class="mb-0">Créer une nouvelle direction</h5>
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

                <form action="{{ route('directions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Libellé court</label>
                        <input type="text" name="libelle_court"
                               class="form-control @error('libelle_court') is-invalid @enderror"
                               value="{{ old('libelle_court') }}"
                               placeholder="ex: DSA"
                               required>
                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Libellé long</label>
                        <input type="text" name="libelle_long"
                               class="form-control @error('libelle_long') is-invalid @enderror"
                               value="{{ old('libelle_long') }}"
                               placeholder="ex: Direction des Systèmes Applicatifs"
                               required>
                        @error('libelle_long')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Créer
                        </button>
                        <a href="{{ route('directions.index') }}" class="btn btn-secondary px-4">
                            Annuler
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection