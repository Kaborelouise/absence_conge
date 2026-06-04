@extends('layouts.app')
@section('title', 'Nouvelle demande de jouissances de congé')
@section('page-title', 'Jouissances de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center"
                 style="background-color:#1e2a3a; padding: 20px;">
                <h5 class="mb-0">Nouvelle demande de jouissances de congé</h5>
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

                <form action="{{ route('demande_jouissances.store') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    {{-- user_id caché --}}
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    {{-- num_demande auto généré --}}
                    <input type="hidden" name="num_demande"
                           value="{{ rand(100000, 999999) }}">

                    {{-- Ligne 1 : Date début + Date fin --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de début</label>
                            <input type="date"
                                   name="date_debut"
                                   id="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut') }}"
                                   placeholder="JJ/mm/aa"
                                   required>
                            @error('date_debut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin</label>
                            <input type="date"
                                   name="date_fin"
                                   id="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin') }}"
                                   placeholder="JJ/mm/aa"
                                   required>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Ligne 2 : Durée --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Durée (calculer automatiquement)</label>
                            <input type="text"
                                   id="duree"
                                   class="form-control"
                                   readonly
                                   placeholder="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Solde disponible</label>
                            <input type="text"
                                   class="form-control text-center fw-bold"
                                   readonly
                                   value="{{ auth()->user()->solde_jouissances }} jours restants 30">
                        </div>
                       

                    {{-- Ligne 3 : Intérimaire + Solde disponible --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Intérimaire désigné</label>
                            <input type="text"
                                   name="interimaire"
                                   class="form-control"
                                   value="{{ old('interimaire') }}">
                        </div>
                       
                    </div>
                    

                    {{-- Boutons --}}
                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> soumettre
                        </button>
                        <a href="{{ route('demande_jouissances.index') }}"
                           class="btn btn-secondary px-4">
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
    // Calcul automatique de la durée
    function calculerDuree() {
        const debut = document.getElementById('date_debut').value;
        const fin   = document.getElementById('date_fin').value;
        if (debut && fin) {
            const diff = Math.ceil(
                (new Date(fin) - new Date(debut)) / (1000 * 60 * 60 * 24)
            );
            document.getElementById('duree').value =
                diff > 0 ? diff + ' jour(s)' : 'Date invalide';
        }
    }

    document.getElementById('date_debut').addEventListener('change', calculerDuree);
    document.getElementById('date_fin').addEventListener('change', calculerDuree);

  
</script>
@endsection