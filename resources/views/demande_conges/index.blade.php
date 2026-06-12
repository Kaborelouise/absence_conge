@extends('layouts.app')
@section('title', 'Demandes de congé')
@section('page-title', 'Demande de congé')

@section('content')

<h5 class="mb-3 fw-bold">Liste des demandes de congé administratif</h5>

<div class="card shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('demande_conges.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle demande
            </a>
            <div class="input-group w-25">
                <input type="text" id="recherche" class="form-control form-control-sm"
                       placeholder="Rechercher...">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
        </div>

        <table class="table table-hover table-sm" id="tableConges">
            <thead class="table-dark">
                <tr>
                    <th>Agents</th>
                    <th>Lieu de jouissance</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    <td>{{ $demande->lieu_jouissance }}</td>
                    <td>
                        <span class="badge-statut badge-{{ $demande->statut }}">
                            {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                        </span>
                    </td>
                    <td>
                        {{-- le bouton voir est en premier car c'est l'action principale , btn-info : couleur bleue claire = consulter --}}
                        <a href="{{ route('demande_conges.show', $demande->id) }}"
                           class="btn btn-sm btn-info btn-action">
                            </i> Voir
                        </a>
                        <a href="{{ route('demande_conges.edit', $demande->id) }}"
                           class="btn btn-sm btn-success btn-action">Modifier</a>
                        <form action="{{ route('demande_conges.destroy', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Aucune demande trouvée
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
        const valeur = this.value.toLowerCase();
        document.querySelectorAll('#tableConges tbody tr').forEach(function(ligne) {
            ligne.style.display =
                ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection