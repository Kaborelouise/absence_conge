@extends('layouts.app')
@section('title', 'Modifier la demande de congé')
@section('page-title', 'Demande de congé administratif')

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

                <form action="{{ route('demande_conges.update', $demande->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">Lieu de jouissance</label>
                        <select name="lieu_jouissance"
                                class="form-select @error('lieu_jouissance') is-invalid @enderror"
                                required>
                            <option value="Afrique" {{ old('lieu_jouissance', $demande->lieu_jouissance) == 'Afrique' ? 'selected' : '' }}>Afrique</option>
                            <option value="Asie" {{ old('lieu_jouissance', $demande->lieu_jouissance) == 'Asie' ? 'selected' : '' }}>Asie</option>
                            <option value="Amerique" {{ old('lieu_jouissance', $demande->lieu_jouissance) == 'Amerique' ? 'selected' : '' }}>Amérique</option>
                            <option value="Europe" {{ old('lieu_jouissance', $demande->lieu_jouissance) == 'Europe' ? 'selected' : '' }}>Europe</option>
                        </select>
                        @error('lieu_jouissance')
                            <div class="invalid-feedback">{{ $message }}</div>
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