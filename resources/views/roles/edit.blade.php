@extends('layouts.app')
@section('title', 'Modifier le rôle')
@section('page-title', 'Gestion des rôles')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Modifier le rôle</h5>
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

                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold">Libellé du rôle</label>
                        <input type="text" name="libelle"
                               class="form-control @error('libelle') is-invalid @enderror"
                               value="{{ old('libelle', $role->libelle) }}"
                               required>
                        @error('libelle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Attention : modifier ce libellé peut casser le circcuit de
                            validation si ce rôle est l'un des 8 rôles système reconnus.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Mettre à jour
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection