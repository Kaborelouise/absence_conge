@extends('layouts.app')
@section('title', 'Nouvelle demande d\'absence')
@section('page-title', 'Autorisation d\'absence')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic text-center" style="padding: 20px;">
                <h5 class="mb-0">Nouvelle demande d'autorisation d'absence</h5>
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

                {{-- Ajout alerte solde insuffisant ou faible --}}
                @php $solde = $soldeRestant ?? auth()->user()->solde_absence; @endphp
                @if($solde <= 0)
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Vous n'avez plus de jours d'absence disponibles pour cette année.
                    </div>
                @elseif($solde <= 2)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Attention : il ne vous reste que <strong>{{ $solde }} jour(s)</strong> d'absence.
                    </div>
                @endif

                <form action="{{ route('demande_absences.store') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- Ligne 1 dates --}}
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

                    {{-- Ligne 2 durée et motif --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Durée (calculée automatiquement)</label>
                            <input type="text" id="duree" class="form-control"
                                   readonly placeholder="Sélectionnez les dates">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Motif</label>
                            <select name="motif" id="motif"
                                    class="form-select @error('motif') is-invalid @enderror"
                                    required onchange="toggleAutreMotif()">
                                <option value="">Motif de l'absence</option>
                                <option value="evenement_familliaux"
                                    {{ old('motif') === 'evenement_familliaux' ? 'selected' : '' }}>
                                    Évènements familiaux (décès)
                                </option>
                                <option value="jouissance_de_reliquat_de_congé_paye"
                                    {{ old('motif') === 'jouissance_de_reliquat_de_congé_paye' ? 'selected' : '' }}>
                                    Jouissance de reliquats de congés payés
                                </option>
                                <option value="convenances_personnelles"
                                    {{ old('motif') === 'convenances_personnelles' ? 'selected' : '' }}>
                                    Convenances personnelles
                                </option>
                                {{--"Autre (à préciser)" --}}
                                <option value="autre"
                                    {{ old('motif') === 'autre' ? 'selected' : '' }}>
                                    Autre (à préciser)
                                </option>
                            </select>
                            @error('motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ajout du champ texte pour préciser le motif "Autre" --}}
                    <div class="mb-3" id="bloc_autre_motif"
                         style="{{ old('motif') === 'autre' ? '' : 'display:none;' }}">
                        <label class="form-label">Précisez le motif <span class="text-danger">*</span></label>
                        <input type="text"
                               name="motif_autre"
                               id="motif_autre"
                               class="form-control @error('motif_autre') is-invalid @enderror"
                               placeholder="Décrivez votre motif..."
                               value="{{ old('motif_autre') }}"
                               maxlength="255">
                        @error('motif_autre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ligne 3 intérimaire et solde --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Intérimaire désigné</label>
                            <select name="interimaire" class="form-select">
                                <option value="">Aucun intérimaire</option>
                                @foreach($agentsMemeDepartement as $agent)
                                    <option value="{{ $agent->nom }} {{ $agent->prenom }}"
                                        {{ old('interimaire') === $agent->nom.' '.$agent->prenom ? 'selected' : '' }}>
                                        {{ $agent->nom }} {{ $agent->prenom }} — {{ $agent->poste }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Solde disponible</label>
                            {{-- Modification affiche le solde calculé dynamiquement--}}
                            <input type="text" class="form-control text-center fw-bold"
                                   readonly
                                   value="{{ $solde }} jours restants">
                        </div>
                    </div>

                    {{-- Justificatif --}}
                    <div class="mb-4 text-center">
                        <label class="form-label d-block">Joindre un justificatif (optionnel)</label>
                        <label for="fichier" class="form-control text-center"
                               style="cursor:pointer; max-width:400px; margin:auto">
                            <i class="bi bi-paperclip me-1"></i>
                            <span id="fichier-label">Cliquer pour joindre un fichier</span>
                        </label>
                        <input type="file" name="fichier" id="fichier"
                               class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Formats acceptés : PDF, JPG, PNG</small>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4"
                                {{ $solde <= 0 ? 'disabled' : '' }}>
                            <i class="bi bi-send me-1"></i> Soumettre
                        </button>
                        <a href="{{ route('demande_absences.index') }}"
                           class="btn btn-secondary px-4">Annuler</a>
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
            const d1   = new Date(debut);
            const d2   = new Date(fin);
            const diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('duree').value =
                diff > 0 ? diff + ' jour(s)' : 'Date invalide';
        }
    }

    document.getElementById('date_debut').addEventListener('change', calculerDuree);
    document.getElementById('date_fin').addEventListener('change', calculerDuree);

    function toggleAutreMotif() {
        const motif = document.getElementById('motif').value;
        const bloc  = document.getElementById('bloc_autre_motif');
        const input = document.getElementById('motif_autre');

        if (motif === 'autre') {
            bloc.style.display  = '';
            input.required      = true;
        } else {
            bloc.style.display  = 'none';
            input.required      = false;
            input.value         = '';
        }
    }
    toggleAutreMotif();

    document.getElementById('fichier').addEventListener('change', function() {
        const label = document.getElementById('fichier-label');
        label.textContent = this.files[0]
            ? this.files[0].name
            : 'Cliquer pour joindre un fichier';
    });
</script>
@endsection