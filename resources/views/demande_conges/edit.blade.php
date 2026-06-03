@extends('layouts.app')
@section('title', 'Modifier demande congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
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

                <form action="{{ route('demande_conges.update', $demande->id) }}"
                      method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Localités de jouissance</label>
                        <input type="text"
                               name="lieu_jouissance"
                               class="form-control @error('lieu_jouissance') is-invalid @enderror"
                               value="{{ old('lieu_jouissance', $demande->lieu_jouissance) }}"
                               required>
                        @error('lieu_jouissance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="en_attente" {{ $demande->statut == 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="compilee"   {{ $demande->statut == 'compilee'   ? 'selected' : '' }}>Compilée</option>
                            <option value="validee"    {{ $demande->statut == 'validee'    ? 'selected' : '' }}>Validée</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Mettre à jour</button>
                        <a href="{{ route('demande_conges.index') }}"
                           class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection