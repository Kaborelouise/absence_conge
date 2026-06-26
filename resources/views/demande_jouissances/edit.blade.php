@extends('layouts.app')
@section('title', 'Modifier la demande de jouissance')
@section('page-title', 'Demande de jouissance de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Modifier la demande de jouissance</h5>
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

                <form action="{{ route('demande_jouissances.update', $demande->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut', $demande->date_debut) }}" required>
                            @error('date_debut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin', $demande->date_fin) }}" required>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre de jours</label>
                            <input type="number" name="nombre_jour" id="nombre_jour"
                                   class="form-control @error('nombre_jour') is-invalid @enderror"
                                   value="{{ old('nombre_jour', $demande->nombre_jour) }}" min="1" required>
                            @error('nombre_jour')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('demande_jouissances.index') }}" class="btn btn-secondary">Annuler</a>
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
            const diff = Math.ceil((new Date(fin) - new Date(debut)) / 86400000);
            if (diff > 0) {
                document.getElementById('nombre_jour').value = diff;
            }
        }
    }
    document.getElementById('date_debut').addEventListener('change', calculerJours);
    document.getElementById('date_fin').addEventListener('change', calculerJours);
</script>
@endsection