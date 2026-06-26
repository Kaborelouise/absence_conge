@extends('layouts.app')
@section('title', 'Nouveau département')
@section('page-title', 'Gestion des départements')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color:#1e2a3a; padding: 20px;">
                <h5 class="mb-0">Créer un nouveau département</h5>
            </div>
            <div class="card-body p-4">

                {{-- $errors est une variable automatiquement dispo dans TOUTES les vues Laravel (injectée par le 
                    middleware web). Elle contient les erreurs de la
                    dernière validation échouée, redirigées avec
                    ->withErrors() automatiquement par validate().
                --}}
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
                    {{-- 
                        @csrf génère un champ caché contenant un token de sécurité. Laravel vérifie ce token à la
                        réception du formulaire pour confirmer que la
                        requête vient bien de cette page (protection
                        contre les attaques CSRF - Cross-Site Request 
                        Forgery, où un site malveillant essaierait de
                        soumettre un formulaire à ta place).
                    --}}

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Libellé court</label>
                            {{-- 
                                @error('champ') ... @enderror : affiche
                                la classe CSS 'is-invalid' UNIQUEMENT si
                                ce champ précis a une erreur de validation.
                                Bootstrap colore alors le champ en rouge.
                            --}}
                            <input type="text" name="libelle_court"
                                   class="form-control @error('libelle_court') is-invalid @enderror"
                                   value="{{ old('libelle_court') }}"
                                   placeholder="ex: DRH"
                                   required>
                            {{-- 
                                old('libelle_court') : récupère la valeur
                                que l'utilisateur avait tapée AVANT que
                                la validation échoue. Sans ça, si l'admin
                                fait une erreur, le formulaire se vide
                                entièrement et il doit tout retaper.
                            --}}
                            @error('libelle_court')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Libellé long</label>
                            <input type="text" name="libelle_long"
                                   class="form-control @error('libelle_long') is-invalid @enderror"
                                   value="{{ old('libelle_long') }}"
                                   placeholder="ex: Direction des Ressources Humaines"
                                   required>
                            @error('libelle_long')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Direction de rattachement</label>
                        {{--  $directions vient du controller : Direction::all()
                            On boucle dessus pour générer les <option> de la
                            liste déroulante, en gardant la sélection précédente
                            (old) en cas d'erreur de validation --}}
                        <select name="direction_id"
                                class="form-select @error('direction_id') is-invalid @enderror"
                                required>
                            <option value="">-- Choisir une direction --</option>
                            @foreach($directions as $direction)
                                <option value="{{ $direction->id }}"
                                    {{ old('direction_id') == $direction->id ? 'selected' : '' }}>
                                    {{ $direction->libelle_court }} — {{ $direction->libelle_long }}
                                </option>
                            @endforeach
                        </select>
                        @error('direction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Créer
                        </button>
                        <a href="{{ route('departements.index') }}" class="btn btn-secondary px-4">
                            Annuler
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection