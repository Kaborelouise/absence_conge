@extends('layouts.app')
@section('title', 'Demandes d\'absence')
@section('page-title', 'Gestion des demandes d\'absence')

@section('content')
{{-- EN-TÊTE titre, recherche, bouton créer --}}
{{-- <div class="d-flex justify-content-between align-items-center mb-4"> --}}
    <h5 class="mb-0 fw-bold">Liste des demandes d'absence</h5>
    <div class="card shadow-sm">
    <div class="card-body">

<div class="card shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('demande_absences.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle demande
            </a>
             <div class="input-group w-25">
                <input type="text" id="recherche" class="form-control form-control-sm"
                       placeholder="Rechercher..."> <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
        </div>
         <table class="table table-hover table-sm" id="tableConges">
           <thead class="table-dark">
                <tr>
                    <th>N° Demande</th>
                    <th>Agent</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    {{--colspan="7" dans @empty --}}
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

                    <td>
                        {{-- format('d/m/Y') : affiche 10/06/2026 au lieu de 2026-06-10 --}}
                        {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}
                    </td>

                    <td>{{ $demande->motif }}</td>

                    <td>
                        <span class="badge-statut badge-{{ $demande->statut }}">
                            {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                        </span>
                    </td>

                    <td>
                        {{-- le bouton voir est en premier car c'est l'action principale , btn-info : couleur bleue claire = consulter --}}
                        <a href="{{ route('demande_absences.show', $demande->id) }}"
                           class="btn btn-sm btn-info btn-action">
                            </i> Voir
                        </a>

                        <a href="{{ route('demande_absences.edit', $demande->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            <i class="bi bi-pencil me-1"></i> Modifier
                        </a>

                        <form action="{{ route('demande_absences.destroy', $demande->id) }}"
                              method="POST"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette demande ?')">
                                <i class="bi bi-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
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
        let valeur = this.value.toLowerCase();
        document.querySelectorAll('#tableAbsences tbody tr').forEach(function(ligne) {
            ligne.style.display =
                ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection
