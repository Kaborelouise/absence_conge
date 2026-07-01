
@extends('layouts.app')
@section('title', 'Nouveau rôle')
@section('page-title', 'Gestion des rôles')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
                <div class="card-header text-white text-center"
                    style="background-color: #1B384F; padding: 20px;">
                    <h5 class="mb-0">Ajouter un rôle</h5>
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

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Libellé du rôle</label>
                        <input type="text" name="libelle"
                               class="form-control @error('libelle') is-invalid @enderror"
                               value="{{ old('libelle') }}"
                               placeholder="ex: chef_departement"
                               required>
                        @error('libelle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Attention : les rôles utilisés par le circuit de validation
                            (agent, chef_departement, responsable_direction, agent_rh,
                            sg, dg, pca) sont reconnus tels quels par le système.
                        </small>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4"> Créer
                        </button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary px-4">
                            Annuler
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection