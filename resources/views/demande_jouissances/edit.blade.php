@extends('layouts.app')
@section('title', 'Modifier demande de jouissance de congé')
@section('page-title', 'Demande de jouissance de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Modifier la demande de jouissance</h5>
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
                        <label class="form-label fw-bold">Date de début</label>
                        <input type="date"
                               name="date_debut"
                               class="form-control @error('date_debut') is-invalid @enderror"
                               value="{{ old('date_debut', $demande->date_debut) }}"
                               required>
                        @error('date_debut')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Date de fin</label>
                        <input type="date"
                               name="date_fin"
                               class="form-control @error('date_fin') is-invalid @enderror"
                               value="{{ old('date_fin', $demande->date_fin) }}"
                               required>
                        @error('date_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Interimaire</label>
                        <input type="text"
                               name="interimaire"
                               class="form-control @error('interimaire') is-invalid @enderror"
                               value="{{ old('interimaire', $demande->interimaire) }}"
                               required>
                        @error('interimaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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