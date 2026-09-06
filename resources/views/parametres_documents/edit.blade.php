@extends('layouts.app')
@section('title', 'Paramètres des documents')
@section('page-title', 'Paramètres des documents')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding: 20px;">
                <h5 class="mb-0">Paramètres des documents officiels</h5>
            </div>
            <div class="card-body p-4">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('parametres_documents.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nom complet du ministère</label>
                        <textarea name="ministere_libelle" class="form-control" rows="2" required>{{ old('ministere_libelle', $parametres->ministere_libelle) }}</textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Sigle du ministère</label>
                            <input type="text" name="sigle_ministere" class="form-control"
                                   value="{{ old('sigle_ministere', $parametres->sigle_ministere) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sigle du secrétariat</label>
                            <input type="text" name="sigle_secretariat" class="form-control"
                                   value="{{ old('sigle_secretariat', $parametres->sigle_secretariat) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sigle de l'agence</label>
                            <input type="text" name="sigle_agence" class="form-control"
                                   value="{{ old('sigle_agence', $parametres->sigle_agence) }}" required>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Nombre de chiffres par type de numéro</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Décision</label>
                            <input type="number" name="nb_chiffres_decision" class="form-control"
                                   value="{{ old('nb_chiffres_decision', $parametres->nb_chiffres_decision) }}" min="1" max="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cessation</label>
                            <input type="number" name="nb_chiffres_cessation" class="form-control"
                                   value="{{ old('nb_chiffres_cessation', $parametres->nb_chiffres_cessation) }}" min="1" max="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prise de service</label>
                            <input type="number" name="nb_chiffres_prise_service" class="form-control"
                                   value="{{ old('nb_chiffres_prise_service', $parametres->nb_chiffres_prise_service) }}" min="1" max="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Intérim</label>
                            <input type="number" name="nb_chiffres_interim" class="form-control"
                                   value="{{ old('nb_chiffres_interim', $parametres->nb_chiffres_interim) }}" min="1" max="10" required>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Suffixes affichés après le numéro</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Suffixe de la décision</label>
                            <input type="text" name="suffixe_decision" class="form-control"
                                   value="{{ old('suffixe_decision', $parametres->suffixe_decision) }}" required>
                            <div class="form-text">Ex : DG/SG/DRH</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Suffixe des certificats</label>
                            <input type="text" name="suffixe_certificat" class="form-control"
                                   value="{{ old('suffixe_certificat', $parametres->suffixe_certificat) }}" required>
                            <div class="form-text">Cessation et prise de service. Ex : DG/SG</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Suffixe de l'intérim</label>
                            <input type="text" name="suffixe_interim" class="form-control"
                                   value="{{ old('suffixe_interim', $parametres->suffixe_interim) }}" required>
                            <div class="form-text">Ex : SG/DRH</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection