@extends('layouts.app')

@section('title', 'Demandes d\'absence')
@section('page-title', 'Gestion des demandes d\'absence')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des demandes d'absence</h5>

    <a href="{{ route('demande_absences.create') }}"
       class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>
        Nouvelle demande
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">

        <div class="mb-3">
            <input type="text"
                   id="recherche"
                   class="form-control w-25"
                   placeholder="Rechercher...">
        </div>

        <table class="table table-hover" id="tableAbsences">
            <thead class="table-dark">
                <tr>
                    <th>N° Demande</th>
                    <th>Agent</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($demandes as $demande)

                <tr>
                    <td>{{ $demande->num_demande }}</td>

                    <td>
                        {{ $demande->user->nom ?? '' }}
                        {{ $demande->user->prenom ?? '' }}
                    </td>

                    <td>{{ $demande->date_debut }}</td>

                    <td>{{ $demande->date_fin }}</td>

                    <td>{{ $demande->motif }}</td>

                    <td>
                        <span class="badge-statut badge-{{ $demande->statut }}">
                            {{ ucfirst(str_replace('_',' ',$demande->statut)) }}
                        </span>
                    </td>

                    <td>
                        <a href="{{ route('demande_absences.edit', $demande->id) }}"
                           class="btn btn-sm btn-success">
                            Modifier
                        </a>

                        <form action="{{ route('demande_absences.destroy', $demande->id) }}"
                              method="POST"
                              class="d-inline">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Supprimer cette demande ?')">
                                Supprimer
                            </button>

                        </form>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="7" class="text-center">
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
document.getElementById('recherche').addEventListener('input', function () {

    let valeur = this.value.toLowerCase();

    document.querySelectorAll('#tableAbsences tbody tr').forEach(function(ligne) {

        ligne.style.display =
            ligne.textContent.toLowerCase().includes(valeur)
            ? ''
            : 'none';

    });
});
</script>
@endsection