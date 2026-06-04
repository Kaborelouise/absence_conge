{{-- FICHIER : resources/views/directions/index.blade.phpR a pour role d'afficher la liste de toutes les directions--}}

{{-- @extends dit que cette vue utilise le layout app.blade.php
     Sans @extends, la page serait vide --}}
@extends('layouts.app')

{{-- Titre de l'onglet du navigateur --}}
@section('title', 'Directions')

{{-- Titre affiché dans la navbar en haut --}}
@section('page-title', 'Directions')

{{-- @section('content') ... @endsection va dans @yield('content') du layout --}}
@section('content')

{{-- EN-TÊTE DE LA PAGE--}}
<div class="d-flex justify-content-between align-items-center mb-4">
    {{-- d-flex : active flexbox éléments côte à côte
         justify-content-between : titre à gauche, bouton à droite
         align-items-center : centrés verticalement
         mb-4 : margin-bottom de 4 unités (1.5rem) --}}

    

    {{-- on peut changer le texte ou la route ici route('directions.create') génère l'URL /directions/create --}}

    <a href="{{ route('directions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle direction
    </a>
</div>

{{-- carte qui contient le tableau --}}
<div class="card shadow-sm">
    <div class="card-body">

        {{--modifier le JavaScript --}}
        <div class="mb-3">
            <input type="text"
                   id="recherche"
                   class="form-control w-25"
                   {{-- w-25 veut dire largeur 25% de la carte --}}
                   placeholder="Rechercher...">
        </div>

        {{-- TABLEAU DES DIRECTIONS
             id="tableDirections" : utilisé par le JavaScript de recherche--}}
        <table class="table table-hover" id="tableDirections">
            {{-- table-hover qui permet à la ligne de changer de couleur au survol --}}

            <thead class="table-">
                {{-- table-dark : en-tête fond sombre --}}
                <tr>
                    <th>Libellé court</th>
                    <th>Libellé long</th>
                    <th>Nombre de départements</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                {{-- @forelse fait comme @foreach mais s'occupe du cas vide
                     $directions vient du controller :
                     Direction::with('departements')->get() --}}
                @forelse($directions as $direction)
                <tr>
                    <td>
                        {{-- badge bg-secondary : pastille grise pour le code court --}}
                        <span class="badge bg-secondary">
                            {{-- {{ }} : affiche la valeur ET échappe le HTML
                                 Empêche les attaques XSS --}}
                            {{ $direction->libelle_court }}
                        </span>
                    </td>

                    <td>{{ $direction->libelle_long }}</td>

                    <td>
                        {{-- $direction->departements vient de with('departements')
                             .count() : compte le nombre de départements
                             C'est plus efficace que de faire une requête séparée --}}
                        <span class="badge bg-info text-dark">
                            {{ $direction->departements->count() }} département(s)
                        </span>
                    </td>

                    <td>
                        {{-- bouton modifier
                             route('directions.edit', $direction->id) :
                             Génère /directions/3/edit si $direction->id = 3 --}}
                        <a href="{{ route('directions.edit', $direction->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>

                        {{-- formulaire pour la suppresion , On ne peut pas faire DELETE avec un <a>, Le navigateur ne supporte que GET et POST, Donc on utilise un formulaire POST avec @method('DELETE') --}}
                        <form action="{{ route('directions.destroy', $direction->id) }}"
                              method="POST"
                              class="d-inline">
                              {{-- d-inline dit que le formulaire reste sur la même ligne que le bouton Modifier --}}

                            {{-- @csrf : protection de sécurité obligatoire , Génère un champ caché avec un token unique, Laravel vérifie ce token à chaque POST,  Sans @csrf → erreur 419 --}}
                            @csrf

                            {{-- @method('DELETE') Génère <input type="hidden" name="_method" value="DELETE"> Laravel lit ce champ et traite ça comme DELETE même si le navigateur envoie POST --}}
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-sm btn-danger btn-action"
                                    {{-- onclick : demande confirmation avant suppression
                                         Si l'utilisateur clique "Annuler" → return false
                                         Le formulaire n'est PAS envoyé --}}
                                    onclick="return confirm('Supprimer cette direction ? Ses départements seront aussi supprimés.')">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>

                {{-- @empty : affiché si $directions est vide --}}
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        {{-- colspan="4" : cette cellule occupe les 4 colonnes --}}
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Aucune direction trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

{{-- @section('scripts') :
     Le JavaScript de cette page
     Va dans @yield('scripts') du layout
     Placé en bas pour ne pas bloquer le chargement --}}
@section('scripts')
<script>
    // On sélectionne l'input de recherche par son id HTML
    const recherche = document.getElementById('recherche');

    // addEventListener('input', function) :
    // 'input' : se déclenche à CHAQUE frappe du clavier
    // Contrairement à 'change' qui attend que l'utilisateur quitte le champ
    recherche.addEventListener('input', function() {

        // this.value : la valeur actuelle de l'input
        // .toLowerCase() : convertit en minuscules
        // Pourquoi ? Pour que "DRH" et "drh" donnent le même résultat
        // (recherche insensible à la casse)
        const valeur = this.value.toLowerCase();

        // querySelectorAll('#tableDirections tbody tr') 
        // Sélectionne TOUTES les lignes <tr> dans le <tbody>
        // du tableau avec l'id "tableDirections"
        const lignes = document.querySelectorAll('#tableDirections tbody tr');

        // On parcourt chaque ligne
        lignes.forEach(function(ligne) {

            // textContent  tout le texte visible de la ligne
            // .toLowerCase() pour comparer sans casse
            const texte = ligne.textContent.toLowerCase();

            // texte.includes(valeur) :
            // → true si le texte de la ligne contient la recherche
            // Opérateur ternaire : condition ? siVrai ou siFaux
            // '' : display normal afficher
            // 'none' : display none cacher
            ligne.style.display = texte.includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection