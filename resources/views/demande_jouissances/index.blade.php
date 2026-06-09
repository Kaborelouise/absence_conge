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
                <input type="text" id="recherche" class="form-control form-control-sm" placeholder="Rechercher...">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle" id="tableJouissances">
                <thead class="table-dark">
                    <tr>
                        <th>Agents</th>
                        <th>Num°Demande</th>
                        <th>Période</th>
                        <th>Durée</th>
                        <th>Statut</th>
                        <th class="text-center" style="width: 150px;">Action</th>
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
                            <span class="badge bg-{{ $demande->statut == 'approuve' ? 'success' : ($demande->statut == 'en_attente' ? 'warning' : 'danger') }}">
                                {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                {{-- Bouton Voir --}}
                                <a href="{{ route('demande_jouissances.show', $demande->id) }}" 
                                   class="btn btn-outline-secondary" 
                                   title="Voir les détails">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{-- Bouton Modifier --}}
                                <a href="{{ route('demande_jouissances.edit', $demande->id) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Bouton Supprimer --}}
                                <form action="{{ route('demande_jouissances.destroy', $demande->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger" 
                                            title="Supprimer"
                                            style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Aucune demande de jouissance de congé trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
