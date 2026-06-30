<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 20px 25px;
        }
        .entete { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .entete td { vertical-align: top; }
        .entete-gauche {
            width: 45%;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.5;
        }
        .entete-centre {
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }
        .entete-droite {
            width: 35%;
            text-align: right;
            font-size: 10px;
            font-weight: bold;
        }
        .entete-droite .devise { font-style: italic; font-weight: normal; font-size: 10px; }
        .titre {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 5px 0;
            margin: 10px 0;
            letter-spacing: 1px;
        }
        table.bloc { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        table.bloc td, table.bloc th {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
            vertical-align: top;
        }
        table.bloc th {
            font-weight: bold;
            background: #fff;
        }

        .section-header {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #000;
            border-bottom: none;
            padding: 4px;
            background: #f5f5f5;
            margin-top: 6px;
        }
        .check-box {
            display: inline-block;
            width: 11px; height: 11px;
            border: 1px solid #000;
            text-align: center;
            font-size: 9px;
            line-height: 11px;
            margin-right: 3px;
            vertical-align: middle;
        }
        .check-box.coche { background: #000; color: #fff; font-weight: bold; }

        /* Note de bas de page*/
        .note {
            font-size: 8px;
            font-style: italic;
            margin-top: 8px;
            border-top: 1px solid #ccc;
            padding-top: 3px;
        }
        .footnote {
            font-size: 8px;
            margin-top: 6px;
        }
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
    $motif = $demande->motif;

    // Récupère les avis par type pour pré-remplir les cases
    $avisParType = $demande->avisAbsence->keyBy('type');

    $avisChef      = $avisParType['chef_departement'] ?? null;
    $avisDirection = $avisParType['responsable_direction'] ?? null;
    $avisRH        = $avisParType['agent_rh'] ?? null;
    $avisFinale    = $avisParType['sg'] ?? $avisParType['dg'] ?? $avisParType['pca'] ?? null;

    $nbJours = \Carbon\Carbon::parse($demande->date_debut)
        ->diffInDays(\Carbon\Carbon::parse($demande->date_fin)->addDay());
@endphp

{{-- EN-TÊTE --}}
<table class="entete" cellpadding="0" cellspacing="0">
    <tr>
        <td class="entete-gauche">
            MINISTERE DE LA TRANSITION DIGITALE,<br>
            DES POSTES ET DES<br>
            COMMUNICATIONS DIGITALES<br>
            -=-=-=-=-=-<br>
            SECRETARIAT GENERAL<br>
            -=-=-=-=-=-<br>
            AGENCE NATIONALE DE PROMOTION<br>
            DES TIC<br>
            -=-=-=-=-=-
        </td>
        <td class="entete-centre">
            {{-- Logo texte ANPTIC --}}
            <div style="font-size:18px; font-weight:bold; border:2px solid #000; padding:6px 10px; display:inline-block;">
                ANPTIC
            </div>
            <div style="font-size:8px; font-style:italic;">Le label du numérique</div>
        </td>
        <td class="entete-droite">
            BURKINA FASO<br>
            <span class="devise">Unité – Progrès – Justice</span><br>
            <span style="border-bottom:1px solid #000; display:inline-block; width:80px;">&nbsp;</span>
        </td>
    </tr>
</table>

{{-- TITRE --}}
<div class="titre">Demande d'autorisation d'absence</div>

{{-- BLOC INFOS AGENT --}}
<table class="bloc">
    <tr>
        <td style="width:35%;"><strong>Nom :</strong> {{ strtoupper($demande->user->nom) }}</td>
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

{{-- MOTIF ET DURÉE --}}
<table class="bloc" style="margin-top:-1px;">
    <tr>
        <td style="width:50%; border-right:1px solid #000;">
            <strong>Motif de l'absence</strong> (joindre un justificatif si possible)<br>
            @foreach($motifLabels as $key => $label)
                <span class="check-box {{ $motif === $key ? 'coche' : '' }}">{{ $motif === $key ? '✓' : '&nbsp;' }}</span>
                {{ $label }}<br>
            @endforeach
        </td>
        <td style="width:50%;">
            <strong>Durée de l'absence</strong><br>
            Du {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
            au {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }} inclus<br><br>
            Soit <strong>{{ $nbJours }}</strong> jour(s)
        </td>
    </tr>
    <tr>
        <td>
            <strong>Nombre de jours d'absence cumulés dans l'année :</strong><br>
            (à renseigner par l'agent) : _______ jours
        </td>
        <td>
            <strong>Date et Signature de l'agent :</strong><br><br><br>
        </td>
    </tr>
</table>

{{-- AVIS DU SUPÉRIEUR HIÉRARCHIQUE IMMÉDIAT --}}
<div class="section-header">
    Avis du supérieur hiérarchique immédiat
    ({{ $demande->user->departement->libelle_court ?? '' }})
</div>
<table class="bloc">
    <tr>
        <td style="width:55%;">
            <span class="check-box {{ $avisChef && $avisChef->avis === 'favorable' ? 'coche' : '' }}">{{ $avisChef && $avisChef->avis === 'favorable' ? '✓' : 'o' }}</span> Favorable<br>
            <span class="check-box {{ $avisChef && $avisChef->avis === 'defavorable' ? 'coche' : '' }}">{{ $avisChef && $avisChef->avis === 'defavorable' ? '✓' : 'o' }}</span> Défavorable<br><br>
            Remplacement<br>
            &nbsp;&nbsp;&nbsp;<span class="check-box {{ $demande->interimaire ? 'coche' : '' }}">{{ $demande->interimaire ? '✓' : 'o' }}</span>
            Assuré par<sup>1</sup> {{ $demande->interimaire ?? '................................' }}<br>
            &nbsp;&nbsp;&nbsp;<span class="check-box {{ !$demande->interimaire ? 'coche' : '' }}">{{ !$demande->interimaire ? '✓' : 'o' }}</span> Non assuré<br><br>
            Si avis défavorable, motif :
            {{ $avisChef && $avisChef->avis === 'defavorable' ? $avisChef->commentaire : '............................................' }}<br>
            ........................................................................
        </td>
        <td style="width:45%;">
            <strong>Nom et prénom(s) :</strong>
            {{ $avisChef ? optional($avisChef->user)->nom.' '.optional($avisChef->user)->prenom : '' }}<br><br><br>
            <strong>Date et signature :</strong><br>
            {{ $avisChef ? $avisChef->created_at->format('d/m/Y') : '' }}<br><br>
        </td>
    </tr>
</table>

{{-- AVIS DU DIRECTEUR DE SERVICE --}}
<div class="section-header">
    Avis du Directeur de service
    ({{ $demande->user->departement->direction->libelle_court ?? '' }})
</div>
<table class="bloc">
    <tr>
        <td style="width:55%;">
            <span class="check-box {{ $avisDirection && $avisDirection->avis === 'favorable' ? 'coche' : '' }}">{{ $avisDirection && $avisDirection->avis === 'favorable' ? '✓' : 'o' }}</span> Favorable<br>
            <span class="check-box {{ $avisDirection && $avisDirection->avis === 'defavorable' ? 'coche' : '' }}">{{ $avisDirection && $avisDirection->avis === 'defavorable' ? '✓' : 'o' }}</span> Défavorable<br>
            @if($avisDirection && $avisDirection->commentaire)
                <br>Commentaire : {{ $avisDirection->commentaire }}
            @endif
        </td>
        <td style="width:45%;">
            <strong>Nom et prénom(s) :</strong>
            {{ $avisDirection ? optional($avisDirection->user)->nom.' '.optional($avisDirection->user)->prenom : '' }}<br><br><br>
            <strong>Date et signature :</strong><br>
            {{ $avisDirection ? $avisDirection->created_at->format('d/m/Y') : '' }}<br><br>
        </td>
    </tr>
</table>

{{-- SUITE RÉSERVÉE PAR LA DRH --}}
<div class="section-header">Suite réservée par la DRH</div>
<table class="bloc">
    <tr>
        <td style="width:55%;">
            <span class="check-box {{ $avisRH && $avisRH->avis === 'favorable' ? 'coche' : '' }}">{{ $avisRH && $avisRH->avis === 'favorable' ? '✓' : 'o' }}</span> Autorisation<br>
            &nbsp;&nbsp;&nbsp;<span class="check-box {{ $demande->retenue_salaire ? 'coche' : '' }}">{{ $demande->retenue_salaire ? '✓' : 'o' }}</span> Avec retenue sur salaire<br>
            &nbsp;&nbsp;&nbsp;<span class="check-box {{ !$demande->retenue_salaire ? 'coche' : '' }}">{{ !$demande->retenue_salaire ? '✓' : 'o' }}</span> Sans retenue sur salaire<br><br>
            <span class="check-box {{ $avisRH && $avisRH->avis === 'defavorable' ? 'coche' : '' }}">{{ $avisRH && $avisRH->avis === 'defavorable' ? '✓' : 'o' }}</span> Refus,
            Motif : {{ $avisRH && $avisRH->avis === 'defavorable' ? $avisRH->commentaire : '................................' }}<br>
            ........................................................................<br>
            ........................................................................
        </td>
        <td style="width:45%;">
            <strong>Nom et prénom(s) :</strong>
            {{ $avisRH ? optional($avisRH->user)->nom.' '.optional($avisRH->user)->prenom : '' }}<br><br><br>
            <strong>Date et signature :</strong><br>
            {{ $avisRH ? $avisRH->created_at->format('d/m/Y') : '' }}<br><br>
        </td>
    </tr>
</table>

{{-- DÉCISION FINALE --}}
<div class="section-header">Directeur / Secrétaire Général / Directeur Général</div>
<table class="bloc">
    <tr>
        <td style="width:55%;">
            Décision :
            {{ $avisFinale ? ucfirst($avisFinale->avis) : '' }}
            @if($avisFinale && $avisFinale->commentaire)
                <br>{{ $avisFinale->commentaire }}
            @endif
        </td>
        <td style="width:45%;">
            <strong>Nom et prénom(s) :</strong>
            {{ $avisFinale ? optional($avisFinale->user)->nom.' '.optional($avisFinale->user)->prenom : '' }}<br><br><br>
            <strong>Date et signature :</strong><br>
            {{ $avisFinale ? $avisFinale->created_at->format('d/m/Y') : '' }}<br><br>
        </td>
    </tr>
</table>

<div class="note">
    NB : Autorisations d'absence de plus de 48 heures et moins de 5 jours = Décision du SG ; + 5 jours = Décision du DG.<br>
    Une fois remplie et les avis portés, l'original est remis à l'intéressé, une copie au SHI et une copie à la DRH.
</div>

<div class="footnote"><sup>1</sup> Prendre une note d'intérim en cas d'absence d'un responsable</div>

</body>
</html>