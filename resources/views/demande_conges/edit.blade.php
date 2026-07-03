@extends('layouts.app')
@section('title', 'Modifier la demande de congé')
@section('page-title', 'Demande de congé administratif')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic text-center" style="padding: 20px;">
                <h5 class="mb-0">Modifier la demande de congé</h5>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('demande_conges.update', $demande->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Lieu(x) de jouissance
                            <span class="text-danger">*</span>
                            {{-- MODIFICATION : sélection multiple --}}
                        </label>

                        @php
                            {{-- AJOUT : liste des lieux --}}
                            $lieux = ['Afrique', 'Burkina', 'Canada', 'Europe', 'Asie', 'USA'];
                            {{-- AJOUT : récupère les choix existants de la demande
                                 old() reprend les anciens choix si erreur de validation --}}
                            $choixActuels = old('lieu_jouissance', $demande->lieu_jouissance ?? []);
                        @endphp

                        {{-- SUPPRESSION : <select> remplacé par des cases à cocher --}}
                        <div class="row">
                            @foreach($lieux as $lieu)
                            <div class="col-6 col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="lieu_jouissance[]"
                                           value="{{ $lieu }}"
                                           id="lieu_{{ $lieu }}"
                                           {{-- AJOUT : pré-cocher les lieux déjà sélectionnés --}}
                                           {{ in_array($lieu, $choixActuels) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lieu_{{ $lieu }}">
                                        {{ $lieu }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @error('lieu_jouissance')
                            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('demande_conges.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection