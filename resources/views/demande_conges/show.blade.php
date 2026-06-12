@extends('layouts.app')
@section('title', 'Détail congé')
@section('page-title', 'Demande de congé')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Détail de la demande de congé</h5>
    <a href="{{ route('demande_conges.index') }}" class="btn btn-sm btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Informations</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Agent</th><td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td></tr>
                    <tr><th>Lieu jouissance</th><td>{{ $demande->lieu_jouissance }}</td></tr>
                    <tr>
                        <th>Statut</th>
                        <td>
                            <span class="badge-statut badge-{{ $demande->statut }}">
                                {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Soumise le</th><td>{{ $demande->created_at->format('d/m/Y') }}</td></tr>
                </table>

                {{-- Bouton donner avis : seulement si pas encore compilée --}}
                @if($demande->statut === 'en_attente')
                    <a href="{{ route('avis_conges.create', ['demande_conge_id' => $demande->id]) }}"
                       class="btn btn-primary btn-sm mt-3">
                        <i class="bi bi-stack me-1"></i> Compiler / Donner avis
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Suivi</div>
            <div class="card-body">
                @forelse($demande->avisconge as $avis)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:36px;height:36px;background:#1e2a3a;color:white;font-size:11px;flex-shrink:0">
                        RH
                    </div>
                    <div style="flex:1">
                        <div class="fw-bold" style="font-size:13px">Agent RH</div>
                        <span class="badge-statut badge-{{ $avis->avis }}">
                            {{ ucfirst($avis->avis) }}
                        </span>
                        @if($avis->commentaire)
                            <div class="text-muted mt-1" style="font-size:12px">
                                {{ $avis->commentaire }}
                            </div>
                        @endif
                        <div class="mt-2 d-flex gap-2">
                            <a href="{{ route('avis_conges.edit', $avis->id) }}"
                               class="btn btn-outline-warning"
                               style="font-size:11px;padding:2px 8px">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <form action="{{ route('avis_conges.destroy', $avis->id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-outline-danger"
                                        style="font-size:11px;padding:2px 8px"
                                        onclick="return confirm('Supprimer ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3">Aucun avis</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection