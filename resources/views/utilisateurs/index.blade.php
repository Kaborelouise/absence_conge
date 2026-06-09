{{-- le fichier  resources/views/utilisateurs/index.blade.php a pour role d'afficher la liste de tous les utilisateurs
les données $utilisateurs vient du controller UserController@index, compact('utilisateurs')--}}
@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')

{{-- En-tête  titre, recherche, bouton créer --}}
<div class="d-flex justify-content-between align-items-center mb-6">
    <h5 class="mb-0 fw-bold">Liste des utilisateurs</h5>

    {{-- Barre de recherche --}}
    <div class="input-group w-25">
        <input type="text" id="recherche"
               class="form-control form-control-sm" placeholder="">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
    </div>

    <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau
    </a>
</div>
<div class="table-responsive">
<table class="table table-hover table-sm align-middle" id="tableJouissances">
<div class="card shadow-sm">

        <table class="table table-hover" id="tableUsers">
            <thead class="table-dark">
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Poste</th>
                    <th>Email</th>
                    <th>Département</th>
                    <th>Direction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- @forelse utilise $utilisateurs car c'est ce que le controller envoie, avec compact('utilisateurs') --}}
                @forelse($utilisateurs as $utilisateur)
                <tr>
                    <td>{{ $utilisateur->matricule }}</td>
                    <td>{{ $utilisateur->nom }}</td>
                    <td>{{ $utilisateur->prenom }}</td>
                    <td>{{ $utilisateur->poste }}</td>
                    <td>{{ $utilisateur->email }}</td>
                    <td>
                        {{-- $utilisateur->departement->libelle Accède au département chargé par with('departement') ?? '—'  si s'est null ca affiche un tiret --}}
                        {{ $utilisateur->departement->libelle ?? '—' }}
                    </td>
                    <td>
                        {{ $utilisateur->departement->direction->libelle_long ?? '—' }}
                    </td>
                    <td>
                        {{-- Bouton modifier On passe $utilisateur->id dans la route --}}
                        <a href="{{ route('utilisateurs.edit', $utilisateur->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            <i class="bi bi-pencil me-1"></i> Modifier
                        </a>

                        {{-- Formulaire de suppression avec post et @method('DELETE') car HTML ne supporte pas DELETE directement --}}
                        <form action="{{ route('utilisateurs.destroy', $utilisateur->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cet utilisateur ?')">
                                </i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Aucun utilisateur trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('recherche').addEventListener('input', function() {
        let valeur = this.value.toLowerCase();
        document.querySelectorAll('#tableUsers tbody tr').forEach(function(ligne) {
            ligne.style.display =
                ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection