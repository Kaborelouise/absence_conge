
@extends('layouts.app')
@section('title', 'Nouvelle direction')
@section('page-title', 'Directions')

@section('content')
<div class="row justify-content-center">
    {{-- justify-content-center : centre la carte horizontalement --}}

    <div class="col-md-8">

        <div class="card shadow-sm">

                        <div class="card-header text-white text-center"
                    style="background-color: #1B384F; padding: 20px;">
                    <h5 class="mb-0">Ajouter une direction</h5>
                </div>


            <div class="card-body">

                {{-- erreur de validation ,$errors : variable automatiquement disponible dans toutes les vues Laravel après un validate()     $errors->any() : true s'il y a au moins une erreur --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            {{-- $errors->all() : toutes les erreurs dans un tableau --}}
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulaire action : où envoyer les données → POST /directions
                     method="POST" : méthode HTTP --}}
                <form action="{{ route('directions.store') }}"
                      method="POST"
                      id="formDirection">

                    {{-- @csrf obligatoire sur tout formulaire POST
                         Génère un champ caché avec un token de sécurité --}}
                    @csrf

                    {{-- CHAMP : Libellé court --}}
                    <div class="mb-3">
                        <label for="libelle_court" class="form-label fw-bold">
                            Libellé court
                            {{-- donne la couleur rouge au champ quand on a la remplie --}}
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            {{-- @error('libelle_court') is-invalid @enderror Si ce champ a une erreur bordure rouge Bootstrap --}}
                            class="form-control @error('libelle_court') is-invalid @enderror"
                            id="libelle_court"
                            {{-- name="libelle_court"  nom reçu dans le controller doit correspondre à la clé dans validate() --}}
                            name="libelle_court"
                            {{-- old('libelle_court') Si formulaire échoue il remet la valeur tapée Si premier affichage → vide --}}
                            value="{{ old('libelle_court') }}"
                            placeholder="Ex: DRH"
                            {{-- maxlength : limite côté navigateur Correspond au max:50 du controller --}}
                            maxlength="50">

                        {{-- @error('libelle_court') : affiche le message d'erreur sous le champ $message : le message de la règle échouée, invalid-feedback classe Bootstrap le texte rouge --}}
                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        {{-- form-text : texte d'aide sous le champ --}}
                        <div class="form-text">Abréviation (max 50 caractères)</div>
                    </div>

                    {{-- Champ  Libellé long --}}
                    <div class="mb-3">
                        <label for="libelle_long" class="form-label fw-bold">
                            Libellé long <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('libelle_long') is-invalid @enderror"
                            id="libelle_long"
                            name="libelle_long"
                            value="{{ old('libelle_long') }}"
                            placeholder="Ex: Direction des Ressources Humaines"
                            maxlength="255">

                        @error('libelle_long')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Boutton --}}
                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">
                            Enregistrer
                        </button>

                        {{-- Annuler fais un lien vers la liste C'est un <a> pas un <button> Retourne à la liste SANS envoyer de données --}}
                        <a href="{{ route('directions.index') }}"
                           class="btn btn-secondary px-4">
                            </i> Annuler
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

    const form = document.getElementById('formDirection');

    // 'submit' : se déclenche quand on clique sur "Enregistrer"
    // avant que le formulaire soit envoyé au serveur
    form.addEventListener('submit', function(e) {
        let valide = true; 
        // valide on va supposer que tout est bon au départ
        // Récupère et nettoie les valeurs
        // .trim() enlève les espaces qui ne sont pas utiles
        const libelleCourt = document.getElementById('libelle_court').value.trim();
        const libelleLong  = document.getElementById('libelle_long').value.trim();

        // Vérification libelle_court
        if (libelleCourt === '') {
            // e.preventDefault() : Annule l'envoi du formulaire
            e.preventDefault();
            // classList.add('is-invalid') : bordure rouge Bootstrap
            document.getElementById('libelle_court').classList.add('is-invalid');
            valide = false;
        }

        // Vérification libelle_long
        if (libelleLong === '') {
            e.preventDefault();
            document.getElementById('libelle_long').classList.add('is-invalid');
            valide = false;
        }
    });

    // Enlève le rouge quand l'utilisateur retape dans un champ
    // forEach boucle sur les deux ids
    ['libelle_court', 'libelle_long'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            // classList.remove : enlève la bordure rouge
            this.classList.remove('is-invalid');
        });
    });
</script>
@endsection