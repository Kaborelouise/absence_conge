<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
            padding: 15px 20px;
        }
        .entete { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .col-gauche {
            width: 38%;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.55;
            vertical-align: top;
        }
        .col-centre {
            width: 24%;
            text-align: center;
            vertical-align: middle;
            padding: 0 6px;
        }
        .logo-sous { font-size: 7.5px; font-style: italic; }
        .col-droite {
            width: 38%;
            text-align: right;
            vertical-align: top;
            font-size: 9.5px;
            font-weight: bold;
        }
        .col-droite .devise {
            font-style: italic;
            font-weight: normal;
            font-size: 9px;
        }
        .col-droite .trait {
            border-bottom: 1px solid #000;
            display: block;
            width: 120px;
            margin-left: auto;
            margin-top: 2px;
        }
        .titre-wrapper {
            border-top: 2.5px solid #000;
            border-bottom: 2.5px solid #000;
            text-align: center;
            padding: 5px 0;
            margin: 8px 0 10px;
        }
        .titre {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.bloc {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: -1px;
        }
        table.bloc td, table.bloc th {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 9.5px;
            vertical-align: top;
        }
        .section-header {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #000;
            border-bottom: none;
            padding: 3px;
            margin-top: 7px;
            background: #f5f5f5;
        }
        .cb {
            display: inline-block;
            width: 10px; height: 10px;
            border: 1px solid #000;
            text-align: center;
            font-size: 8px;
            line-height: 10px;
            margin-right: 2px;
            vertical-align: middle;
        }
        .cb.ok { background: #000; color: #fff; font-weight: bold; }
        .note {
            font-size: 7.5px;
            font-style: italic;
            margin-top: 5px;
            border-top: 1px solid #ccc;
            padding-top: 3px;
        }
        .footnote { font-size: 7.5px; margin-top: 4px; }
    </style>
</head>
<body>

@php
    $motifLabels = [
        'evenement_familliaux'                 => 'Évènements familiaux (Décès)',
        'jouissance_de_reliquat_de_congé_paye' => 'Jouissance de reliquats de congés payés',
        'convenances_personnelles'             => 'Convenances personnelles',
        'autre'                                => 'Autre',
    ];
    $avisParType   = $demande->avisAbsence->keyBy('type');
    $avisChef      = $avisParType['chef_departement']      ?? null;
    $avisDirection = $avisParType['responsable_direction'] ?? null;
    $avisRH        = $avisParType['agent_rh']              ?? null;
    $avisFinale    = $avisParType['sg'] ?? $avisParType['dg'] ?? $avisParType['pca'] ?? null;
    $nbJours = \Carbon\Carbon::parse($demande->date_debut)
        ->diffInDays(\Carbon\Carbon::parse($demande->date_fin)->addDay());
@endphp

<table class="entete" cellpadding="0" cellspacing="0">
    <tr>
        <td class="col-gauche">
            MINISTERE DE LA TRANSITION<br>
            DIGITALE, DES POSTES ET DES<br>
            COMMUNICATIONS DIGITALES<br>
            -=-=-=-=-=-<br>
            SECRETARIAT GENERAL<br>
            -=-=-=-=-=-<br>
            AGENCE NATIONALE DE PROMOTION<br>
            DES TIC<br>
            -=-=-=-=-=-
        </td>
        <td class="col-centre">
            {{-- CORRECTION : logo chargé depuis public/images/logo_anptic.png --}}
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo_anptic.png'))) }}"
                 style="width:80px; height:auto;">
            <div class="logo-sous">Le label du numérique</div>
        </td>
        <td class="col-droite">
            BURKINA FASO<br>
            <span class="devise">Unité – Progrès – Justice</span><br>
            <span class="trait"></span>
        </td>
    </tr>
</table>

<div class="titre-wrapper">
    <span class="titre">Demande d'autorisation d'absence</span>
</div>

<table class="bloc">
    <tr>
        <td style="width:40%"><strong>Nom :</strong> {{ strtoupper($demande->user->nom) }}</td>
        <td><strong>Prénom(s) :</strong> {{ $demande->user->prenom }}</td>
    </tr>
    <tr>
        <td><strong>Matricule :</strong> {{ $demande->user->matricule }}</td>
        <td><strong>Fonction ou poste occupé :</strong> {{ $demande->user->poste }}</td>
    </tr>
    <tr>
        <td colspan="2">
            <strong>Structure de rattachement :</strong>
            {{ $demande->user->departement->libelle_court ?? '—' }}
            ({{ $demande->user->departement->direction->libelle_court ?? '—' }})
        </td>
    </tr>
</table>

<table class="bloc" style="margin-top:-1px;">
    <tr>
        <td style="width:55%; border-right:1px solid #000;">
            <strong>Motif de l'absence</strong> <em>(joindre un justificatif si possible)</em><br>
            @foreach($motifLabels as $key => $label)
                <span class="cb {{ $demande->motif === $key ? 'ok' : '' }}">{{ $demande->motif === $key ? '✓' : 'o' }}</span>
                {{ $label }}<br>
            @endforeach
        </td>
        <td style="width:45%;">
            <strong>Durée de l'absence</strong><br>
            Du {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
            au {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }} inclus<br><br>
            Soit <strong>{{ $nbJours }}</strong> jour(s)
        </td>
    </tr>
    <tr>
        <td>
            <strong>Nombre de jours d'absence cumulés dans l'année :</strong><br>
            <em>(à renseigner par l'agent)</em> : _______ jours
        </td>
        <td>
            <strong>Date et Signature de l'agent :</strong><br><br><br>
        </td>
    </tr>
</table>

<div class="section-header">
    Avis du supérieur hiérarchique immédiat
    ({{ $demande->user->departement->libelle_court ?? '' }})
</div>
<table class="bloc">
    <tr>
        <td style="width:58%;">
            <span class="cb {{ $avisChef && $avisChef->avis === 'favorable' ? 'ok' : '' }}">
                {{ $avisChef && $avisChef->avis === 'favorable' ? '✓' : 'o' }}
            </span> Favorable<br>
            <span class="cb {{ $avisChef && $avisChef->avis === 'defavorable' ? 'ok' : '' }}">
                {{ $avisChef && $avisChef->avis === 'defavorable' ? '✓' : 'o' }}
            </span> Défavorable<br><br>
            Remplacement<br>
            &nbsp;&nbsp;<span class="cb {{ $demande->interimaire ? 'ok' : '' }}">{{ $demande->interimaire ? '✓' : 'o' }}</span>
            Assuré par<sup>1</sup>
            {{ $demande->interimaire ?? '............................................' }}<br>
            &nbsp;&nbsp;<span class="cb {{ !$demande->interimaire ? 'ok' : '' }}">{{ !$demande->interimaire ? '✓' : 'o' }}</span>
            Non assuré<br><br>
            Si avis défavorable, motif :
            {{ $avisChef && $avisChef->avis === 'defavorable' && $avisChef->commentaire
                ? $avisChef->commentaire
                : '............................................' }}<br>
            ........................................................................
        </td>
        <td style="width:42%;">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisChef ? (optional($avisChef->user)->nom.' '.optional($avisChef->user)->prenom) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisChef ? $avisChef->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>

<div class="section-header">
    Avis du Directeur de service
    ({{ $demande->user->departement->direction->libelle_court ?? '' }})
</div>
<table class="bloc">
    <tr>
        <td style="width:58%;">
            <span class="cb {{ $avisDirection && $avisDirection->avis === 'favorable' ? 'ok' : '' }}">
                {{ $avisDirection && $avisDirection->avis === 'favorable' ? '✓' : 'o' }}
            </span> Favorable<br>
            <span class="cb {{ $avisDirection && $avisDirection->avis === 'defavorable' ? 'ok' : '' }}">
                {{ $avisDirection && $avisDirection->avis === 'defavorable' ? '✓' : 'o' }}
            </span> Défavorable<br>
            @if($avisDirection && $avisDirection->commentaire)
                <br>{{ $avisDirection->commentaire }}
            @endif
            <br>&nbsp;
        </td>
        <td style="width:42%;">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisDirection ? (optional($avisDirection->user)->nom.' '.optional($avisDirection->user)->prenom) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisDirection ? $avisDirection->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>

<div class="section-header">Suite réservée par la DRH</div>
<table class="bloc">
    <tr>
        <td style="width:58%;">
            <span class="cb {{ $avisRH && $avisRH->avis === 'favorable' ? 'ok' : '' }}">
                {{ $avisRH && $avisRH->avis === 'favorable' ? '✓' : 'o' }}
            </span> Autorisation<br>
            &nbsp;&nbsp;<span class="cb {{ $demande->retenue_salaire ? 'ok' : '' }}">{{ $demande->retenue_salaire ? '✓' : 'o' }}</span>
            Avec retenue sur salaire<br>
            &nbsp;&nbsp;<span class="cb {{ !$demande->retenue_salaire ? 'ok' : '' }}">{{ !$demande->retenue_salaire ? '✓' : 'o' }}</span>
            Sans retenue sur salaire<br><br>
            <span class="cb {{ $avisRH && $avisRH->avis === 'defavorable' ? 'ok' : '' }}">
                {{ $avisRH && $avisRH->avis === 'defavorable' ? '✓' : 'o' }}
            </span> Refus, Motif :
            {{ $avisRH && $avisRH->avis === 'defavorable' && $avisRH->commentaire
                ? $avisRH->commentaire
                : '............................................' }}<br>
            ........................................................................<br>
            ........................................................................
        </td>
        <td style="width:42%;">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisRH ? (optional($avisRH->user)->nom.' '.optional($avisRH->user)->prenom) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisRH ? $avisRH->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>

<div class="section-header">Directeur / Secrétaire Général / Directeur Général</div>
<table class="bloc">
    <tr>
        <td style="width:58%;">
            Décision :
            @if($avisFinale)
                <strong>{{ $avisFinale->avis === 'favorable' ? 'Favorable' : 'Défavorable' }}</strong>
                @if($avisFinale->commentaire)
                    <br>{{ $avisFinale->commentaire }}
                @endif
            @endif
            <br>&nbsp;<br>&nbsp;
        </td>
        <td style="width:42%;">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisFinale ? (optional($avisFinale->user)->nom.' '.optional($avisFinale->user)->prenom) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisFinale ? $avisFinale->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>

<div class="note">
    <em>NB : Autorisations d'absence de plus de 48 heures et moins de 5 jours = Décision du SG ; + 5 jours = Décision du DG.<br>
    Une fois remplie et les avis portés, l'original est remis à l'intéressé, une copie au SHI et une copie à la DRH.</em>
</div>
<div class="footnote"><sup>1</sup> Prendre une note d'intérim en cas d'absence d'un responsable</div>

</body>
</html>