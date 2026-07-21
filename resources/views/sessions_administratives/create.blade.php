@extends('layouts.app')
@section('title', 'Nouvelle session')
@section('page-title', 'Sessions Administratives')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding: 20px;">
                <h5 class="mb-0">Ouvrir une nouvelle session Administratives</h5>
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
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="alert alert-warning" style="font-size: 13px;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Ouvrir une session réinitialise automatiquement le solde de <strong>tous les Agents</strong>
                    (10 jours d'absence, 30 jours de congé). La période ne doit pas chevaucher une session existante.
                </div> 

                <form action="{{ route('sessions_Administratives.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="libelle"
                               class="form-control @error('libelle') is-invalid @enderror"
                               value="{{ old('libelle') }}" placeholder="Ex: Session 2026" required>
                        @error('libelle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Année</label>
                        <input type="number" name="annee"
                               class="form-control @error('annee') is-invalid @enderror"
                               value="{{ old('annee', now()->year) }}" required>
                        @error('annee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de début</label>
                            <input type="date" name="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut') }}" required>
                            @error('date_debut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin</label>
                            <input type="date" name="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin') }}" required>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">Ouvrir la session</button>
                        <a href="{{ route('sessions_Administratives.index') }}" class="btn btn-secondary px-4">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection