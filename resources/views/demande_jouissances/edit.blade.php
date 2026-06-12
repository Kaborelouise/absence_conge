@extends('layouts.app')
@section('title', 'Modifier jouissance')
@section('page-title', 'Jouissance de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
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

                <form action="{{ route('demande_jouissances.update', $demande->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date début</label>
                            <input type="date" name="date_debut" id="date_debut"
                                   class="form-control"
                                   value="{{ old('date_debut', $demande->date_debut) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date fin</label>
                            <input type="date" name="date_fin" id="date_fin"
                                   class="form-control"
                                   value="{{ old('date_fin', $demande->date_fin) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Durée</label>
                            <input type="number" name="nombre_jour" id="duree"
                                   class="form-control"
                                   value="{{ old('nombre_jour', $demande->nombre_jour) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="en_attente" {{ $demande->statut == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                <option value="en_cours"   {{ $demande->statut == 'en_cours'   ? 'selected' : '' }}>En cours</option>
                                <option value="validee"    {{ $demande->statut == 'validee'    ? 'selected' : '' }}>Validée</option>
                                <option value="rejetee"    {{ $demande->statut == 'rejetee'    ? 'selected' : '' }}>Rejetée</option>
                            </select>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">Mettre à jour</button>
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
    calculerDuree();
</script>
@endsection