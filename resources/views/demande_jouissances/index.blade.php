@extends('layouts.app')
@section('title', 'Jouissance de congé')
@section('page-title', 'Jouissance de congé')

@section('content')

<h5 class="mb-3 fw-bold">Liste des demandes de jouissance de congé administratif</h5>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('demande_jouissances.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle demande
            </a>
            <div class="input-group w-25">
    <input type="text" 
           id="recherche" 
           class="form-control form-control-sm"
           placeholder="">
    {{-- placeholder vide, pas de texte --}}
    <span class="input-group-text">
        <i class="bi bi-search"></i>
    </span>
</div>
        </div>

        <table class="table table-hover table-sm" id="tableJouissances">
            <thead class="table-dark">
                <tr>
                    <th>Agents</th>
                    <th>Num°Demande</th>
                    <th>Période</th>
                    <th>Durée</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                <tr>
                    <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    <td>{{ $demande->num_demande }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
                        au
                        {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}
                    </td>
                    <td>{{ $demande->nombre_jour }} jour(s)</td>
                    <td>
                        <span class="badge-statut badge-{{ $demande->statut }}">
                            {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('demande_jouissances.show', $demande->id) }}"
                           class="btn btn-sm btn-info btn-action">voir</a>
                        <a href="{{ route('demande_jouissances.edit', $demande->id) }}"
                           class="btn btn-sm btn-success btn-action">Modifier</a>
                        <form action="{{ route('demande_jouissances.destroy', $demande->id) }}"
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
                    <td colspan="6" class="text-center text-muted py-4">
                        Aucune demande trouvée
                    </td>
                </tr>
                <td>
            <a href="{{ route('demande_jouissances.show', $demande->id) }}"
               class="btn btn-sm btn-info">
                <i class="bi bi-eye"></i> Voir
            </a>
        </td>
                @endforelse
            </tbody>
        </table>

        {{-- Bouton pour télécharger la décision --}}
        <div class="mt-3">
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i> Télécharger la décision
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('recherche').addEventListener('input', function() {
        const valeur = this.value.toLowerCase();
        document.querySelectorAll('#tableJouissances tbody tr').forEach(function(ligne) {
            ligne.style.display =
                ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection