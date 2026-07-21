@extends('layouts.app')
@section('title', 'Demandes de congé')
@section('page-title', 'Demandes de congé')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des demandes de congé</h5>

    {{--
        AJOUTÉ : zone d'actions globales (RH uniquement), avec état qui change
        selon où on en est dans le cycle compiler/télécharger/décompiler
        (règle confirmée) :
        - Rien compilé              → Compiler + Nouvelle demande
        - Compilé                   → Décompiler + Télécharger décision
        - Décision téléchargée      → Décompiler seulement
        - Après décompilation       → retour à l'état initial (Compiler + Nouvelle demande)

        On distingue "compilé mais pas encore téléchargé" de "décision déjà
        téléchargée" via un flag en session (le téléchargement est un GET, pas
        un POST, donc pas de redirection avec état possible autrement — une
        session flag simple suffit ici, remise à zéro à chaque nouvelle
        compilation/décompilation).
    --}}
    <div class="d-flex gap-2">
        @if($peutCompiler)
            @if($compilationActive)
                @if(session('decision_telechargee'))
                    {{-- État "décision téléchargée" : seulement Décompiler --}}
                    <form action="{{ route('demande_conges.decompiler') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm"
                                onclick="return confirm('Décompiler ? Toutes les demandes de la session repasseront en attente.')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Décompiler
                        </button>
                    </form>
                @else
                    {{-- État "compilé, décision pas encore téléchargée" --}}
                    <form action="{{ route('demande_conges.decompiler') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm"
                                onclick="return confirm('Décompiler ? Toutes les demandes de la session repasseront en attente.')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Décompiler
                        </button>
                    </form>
                    <a href="{{ route('demande_conges.telecharger_decision') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-download me-1"></i> Télécharger décision
                    </a>
                @endif
            @else
                {{-- État initial : rien compilé --}}
                <form action="{{ route('demande_conges.compiler') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Compiler toutes les demandes en attente de la session en cours ?')">
                        <i class="bi bi-check2-circle me-1"></i> Compiler
                    </button>
                </form>
                <a href="{{ route('demande_conges.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nouvelle demande
                </a>
            @endif
        @else
            {{-- Agents non-RH : uniquement Nouvelle demande --}}
            <a href="{{ route('demande_conges.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle demande
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($session)
    <div class="alert alert-info alert-dismissible fade show">
        <i class="bi bi-info-circle me-1"></i>
        Session en cours : <strong>{{ $session->libelle }}</strong>
        ({{ $session->date_debut->format('d/m/Y') }} → {{ $session->date_fin->format('d/m/Y') }})
        — Demandes de congé : <strong>{{ $session->active_conge ? 'ouvertes' : 'fermées' }}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@else
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Aucune session Administrateuristrative n'est actuellement ouverte. Les nouvelles demandes de congé sont impossibles.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">

        <div class="mb-3">
            <input type="text" id="recherche" class="form-control w-25"
                   placeholder="Rechercher...">
        </div>

        <div class="table-responsive">
        <table class="table table-hover" id="tableConges">
            <thead class="table-anptic-dark">
                <tr>
                    <th>N° Demande</th>
                    <th>Agent</th>
                    <th>Département</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                @php
                    $estAuteur  = $demande->user_id === auth()->id();
                    $modifiable = $estAuteur && !$demande->estCompilee() && !$demande->abandonnee;
                @endphp

                <tr>
                    <td>{{ $demande->num_demande }}</td>
                    <td>{{ $demande->user->nom ?? '' }} {{ $demande->user->prenom ?? '' }}</td>
                    <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    <td>
                        @if($demande->abandonnee)
                            <span class="baDGe-statut baDGe-rejetee">Abandonnée</span>
                        @elseif($demande->estCompilee())
                            <span class="baDGe-statut baDGe-validee">Compilée</span>
                        @else
                            <span class="baDGe-statut baDGe-en_attente">En attente</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('demande_conges.show', $demande->id) }}"
                           class="btn btn-sm btn-outline-primary btn-action">Voir
                        </a>
                        @if($modifiable)
                        <a href="{{ route('demande_conges.edit', $demande->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>
                        <form action="{{ route('demande_conges.destroy', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette demande ?')">
                                Supprimer
                            </button>
                        </form>
                        <form action="{{ route('demande_conges.abandonner', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning btn-action"
                                    onclick="return confirm('Abandonner cette demande ?')">
                                Abandonner
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Aucune demande trouvée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('recherche').addEventListener('input', function () {
    let valeur = this.value.toLowerCase();
    document.querySelectorAll('#tableConges tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection