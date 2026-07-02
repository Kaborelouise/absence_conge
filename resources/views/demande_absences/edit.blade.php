@extends('layouts.app')
@section('title', 'Modifier la demande')
@section('page-title', 'Autorisation d\'absence')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">

            <div class="card-header text-white text-center" style=" background-color: #1B384F; padding: 20px;">
                <h5 class="mb-0">Modifier la demande d'absence</h5>
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

                <form action="{{ route('demande_absences.update', $demande->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date"
                                   name="date_debut"
                                   id="date_debut"
                                   class="form-control @error('date_debut') is-invalid @enderror"
                                   value="{{ old('date_debut', $demande->date_debut) }}"
                                   required>
                            @error('date_debut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de fin</label>
                            <input type="date"
                                   name="date_fin"
                                   id="date_fin"
                                   class="form-control @error('date_fin') is-invalid @enderror"
                                   value="{{ old('date_fin', $demande->date_fin) }}"
                                   required>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Durée</label>
                            <input type="text" id="duree" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Intérimaire</label>
                            <input type="text"
                                   name="interimaire"
                                   class="form-control"
                                   value="{{ old('interimaire', $demande->interimaire) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Statut</label>
                            <select name="statut"
                                    class="form-select @error('statut') is-invalid @enderror">
                                <option value="en_attente"
                                    {{ old('statut', $demande->statut) == 'en_attente' ? 'selected' : '' }}>
                                    En attente
                                </option>
                                <option value="en_cours"
                                    {{ old('statut', $demande->statut) == 'en_cours' ? 'selected' : '' }}>
                                    En cours
                                </option>
                                <option value="validee"
                                    {{ old('statut', $demande->statut) == 'validee' ? 'selected' : '' }}>
                                    Validée
                                </option>
                                <option value="rejetee"
                                    {{ old('statut', $demande->statut) == 'rejetee' ? 'selected' : '' }}>
                                    Rejetée
                                </option>
                            </select>
                            @error('statut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Motif</label>
                            <textarea name="motif"
                                      class="form-control @error('motif') is-invalid @enderror"
                                      rows="3"
                                      required>{{ old('motif', $demande->motif) }}</textarea>
                            @error('motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <button type="submit" class="btn btn-success"> Mettre à jour
                        </button>
                        <a href="{{ route('demande_absences.index') }}"
                           class="btn btn-secondary px-3">Annuler</a>
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
            document.getElementById('duree').value = diff > 0 ? diff + ' jour(s)' : 'Date invalide';
        }
    }
    document.getElementById('date_debut').addEventListener('change', calculerDuree);
    document.getElementById('date_fin').addEventListener('change', calculerDuree);
    calculerDuree(); // Calcule dès le chargement
</script>
@endsection