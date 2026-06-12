@extends('layouts.app')
@section('title', 'Nouvelle jouissance')
@section('page-title', 'Jouissance de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Soumettre une demande de jouissance de congé</h5>
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

                <form action="{{ route('demande_jouissances.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                    <div class="row g-3">

                       
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solde disponible</label>
                            <input type="text" class="form-control" readonly
                                   value="{{ auth()->user()->solde_conge }} jours restants">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date début <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" id="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut') }}" required>
                            @error('date_debut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date fin <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin') }}" required>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Durée</label>
                            <input type="number" name="nombre_jour" id="duree"
                                   class="form-control" readonly
                                   placeholder="Calculée automatiquement">
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Soumettre
                        </button>
                        <a href="{{ route('demande_jouissances.index') }}"
                           class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function calculerDuree() {
        const debut = document.getElementById('date_debut').value;
        const fin   = document.getElementById('date_fin').value;
        if (debut && fin) {
            const diff = Math.ceil((new Date(fin) - new Date(debut)) / 86400000);
            document.getElementById('duree').value = diff > 0 ? diff : 0;
        }
    }
    document.getElementById('date_debut').addEventListener('change', calculerDuree);
    document.getElementById('date_fin').addEventListener('change', calculerDuree);
</script>
@endsection