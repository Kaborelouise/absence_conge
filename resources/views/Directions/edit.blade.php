@extends('layouts.app')
@section('title', 'Modifier la direction')
@section('page-title', 'Gestion des directions')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Modifier la direction</h5>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('directions.update', $direction->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Libellé court</label>
                        <input type="text" name="libelle_court"
                               class="form-control @error('libelle_court') is-invalid @enderror"
                               value="{{ old('libelle_court', $direction->libelle_court) }}"
                               required>
                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Libellé long</label>
                        <input type="text" name="libelle_long"
                               class="form-control @error('libelle_long') is-invalid @enderror"
                               value="{{ old('libelle_long', $direction->libelle_long) }}"
                               required>
                        @error('libelle_long')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{--
                        Avertissement si des départements existent déjà :
                        on utilise la relation departements() définie
                        dans Direction.php pour compter en direct.
                    --}}
                    @if($direction->departements()->count() > 0)
                        <div class="alert alert-info py-2" style="font-size:12px;">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ $direction->departements()->count() }} département(s)
                            sont rattachés à cette direction.
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('directions.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection