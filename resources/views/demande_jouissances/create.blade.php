@extends('layouts.app')
@section('title', 'Nouvelle demande de jouissance')
@section('page-title', 'Demande de jouissance de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1e2a3a; padding: 20px;">
                <h5 class="mb-0">Nouvelle demande de jouissance de congé</h5>
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
                <form action="{{ route('demande_jouissances.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut') }}" required>
                            @error('date_debut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror 
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin') }}" required>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de jours</label>
                            <input type="number" name="nombre_jour" id="nombre_jour"
                                   class="form-control @error('nombre_jour') is-invalid @enderror"
                                   value="{{ old('nombre_jour') }}" min="1" required>
                            @error('nombre_jour')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Solde congé disponible</label>
                            <input type="text" class="form-control text-center fw-bold" readonly
                                   value="{{ auth()->user()->solde_conge }} jours restants">
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> Soumettre
                        </button>
                        <a href="{{ route('demande_jouissances.index') }}" class="btn btn-secondary px-4">
                            Annuler
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function calculerJours() {
        const debut = document.getElementById('date_debut').value;
        const fin   = document.getElementById('date_fin').value;
        if (debut && fin) {
            const diff = Math.ceil((new Date(fin) - new Date(debut)) / (1000 * 60 * 60 * 24));
            if (diff > 0) {
                document.getElementById('nombre_jour').value = diff;
            }
        }
    }
    document.getElementById('date_debut').addEventListener('change', calculerJours);
    document.getElementById('date_fin').addEventListener('change', calculerJours);
</script>
@endsection