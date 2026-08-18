<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

?>
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
        .entete {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 8px;
            }

            .col-gauche {
                width: 42%;
                text-align: center;
                font-size: 9.5px;
                font-weight: bold;
                text-transform: uppercase;
                line-height: 1.45;
                vertical-align: top;
            }

            .col-centre {
                width: 20%;
                text-align: center;
                vertical-align: top;
                padding-top: 5px;
            }

            .col-centre img {
                display: block;
                width: 85px;
                height: auto;
                margin: 0 auto;
            }

            .logo-sous {
                font-size: 7.5px;
                font-style: italic;
                text-align: center;
                margin-top: 1px;
            }

            .col-droite {
                width: 38%;
                text-align: center;
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
                margin: 3px auto 0;
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
        /* Toutes les tables à deux colonnes utilisent désormais la même
           répartition (58% / 42%) pour que les traits verticaux
           s'alignent d'un bloc à l'autre sur toute la page. */
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
        table.bloc .col-58 { width: 58%; }
        table.bloc .col-42 { width: 42%; }

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

        .cb-sub {
            display: inline-block;
            width: 8px; height: 8px;
            border: 1px solid #000;
            border-radius: 50%;
            text-align: center;
            font-size: 6px;
            line-height: 8px;
            margin-right: 2px;
            vertical-align: middle;
        }
        .cb-sub.ok { background: #000; color: #fff; }
        .footnote { font-size: 7.5px; margin-top: 4px; }
        .footer-telechargement {
            font-size: 7.5px;
            margin-top: 10px;
            color: #333;
        }
        .footer-adresse {
            text-align: center;
            font-size: 8px;
            margin-top: 4px;
            border-top: 1px solid #000;
            padding-top: 4px;
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

    // Les vraies valeurs stockées en base (voir AvisAbsenceController::store)
    $avisParType = $demande->avisAbsence->keyBy('type');
    $avisChef      = $avisParType['chef_departement']      ?? null;
    $avisDirection = $avisParType['responsable_direction'] ?? null;
    $avisRH        = $avisParType['agent_rh']               ?? null;

    // Dernière étape réelle du circuit de CETTE demande (peut être 'sg', 'dg', 'pca'
    // ou même 'agent_rh' si le circuit s'arrête là — absence courte)
    $etapeFinale = collect($circuit)->last();
    $avisFinale  = $avisParType[$etapeFinale] ?? null;

    $labelsFinale = [
        'sg'       => 'Secrétaire Général',
        'dg'       => 'Directeur Général',
        'pca'      => "Conseil d'Administration (PCA)",
        'agent_rh' => 'Directeur des Ressources Humaines',
    ];

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
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo_anptic.png'))) }}"
                 style="width:80px; height:auto;">
            <div class="logo-sous">Le label du numérique</div>
        </td>
        <td class="col-droite">
            BURKINA FASO<br>
            <span class="devise">La Patrie ou La Mort, Nous Vaincrons</span><br>
            <span class="trait"></span>
        </td>
    </tr>
</table>

<div class="titre-wrapper">
    <span class="titre">Demande d'autorisation d'absence</span>
</div>

<table class="bloc">
    <tr>
        <td colspan="2" style="text-align:right; font-size:9px;">
            <strong>N° de la demande :</strong> {{ $demande->num_demande }}
        </td>
    </tr>
    <tr>
        <td class="col-58"><strong>Nom :</strong> {{ strtoupper($demande->user->nom) }}</td>
        <td class="col-42"><strong>Prénom(s) :</strong> {{ $demande->user->prenom }}</td>
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
        <td class="col-58" style="border-right:1px solid #000;">
            <strong>Motif de l'absence</strong> <em>(joindre un justificatif si possible)</em><br>
            @foreach($motifLabels as $key => $label)
                <span class="cb {{ $demande->motif === $key ? 'ok' : '' }}">{{ $demande->motif === $key ? '✓' : 'o' }}</span>
                {{ $label }}<br>
            @endforeach
        </td>
        <td class="col-42">
            <strong>Durée de l'absence</strong><br>
            Du {{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
            au {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }} inclus<br><br>
            Soit <strong>{{ $nbJours }}</strong> jour(s)
        </td>
    </tr>
    <tr>
        <td class="col-58">
            <strong>Nombre de jours d'absence cumulés dans l'année :</strong><br>
            <strong>{{ $joursCumules }}</strong> jour(s)
        </td>
        <td class="col-42">
            <strong>Date et Signature de l'Agent :</strong><br><br><br>
        </td>
    </tr>
</table>

{{-- Bloc Chef de Département : uniquement si cette étape fait partie du circuit de la demande --}}
@if(in_array('chef_departement', $circuit))
<div class="section-header">
    Avis du supérieur hiérarchique immédiat
    ({{ $demande->user->departement->libelle_court ?? '' }})
</div>
<table class="bloc">
    <tr>
        <td class="col-58">
            <span class="cb {{ $avisChef && $avisChef->avis === 'favorable' ? 'ok' : '' }}">
                {{ $avisChef && $avisChef->avis === 'favorable' ? '✓' : 'o' }}
            </span> Favorable<br>
            <span class="cb {{ $avisChef && $avisChef->avis === 'defavorable' ? 'ok' : '' }}">
                {{ $avisChef && $avisChef->avis === 'defavorable' ? '✓' : 'o' }}
            </span> Défavorable<br><br>
            Remplacement<br>
            &nbsp;&nbsp;<span class="cb-sub {{ $demande->interimaire ? 'ok' : '' }}">{{ $demande->interimaire ? '✓' : 'o' }}</span>
            Assuré par<sup>1</sup>
            {{ $demande->interimaire ? $demande->interimaire->nom.' '.$demande->interimaire->prenom : '............................................' }}<br>
            &nbsp;&nbsp;<span class="cb-sub {{ !$demande->interimaire ? 'ok' : '' }}">{{ !$demande->interimaire ? '✓' : 'o' }}</span>
            Non assuré<br><br>
            Si avis défavorable, motif :
            {{ $avisChef && $avisChef->avis === 'defavorable' && $avisChef->commentaire
                ? $avisChef->commentaire
                : '............................................' }}<br>
            ........................................................................
        </td>
        <td class="col-42">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisChef ? trim(($avisChef->user->nom ?? '').' '.($avisChef->user->prenom ?? '')) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisChef ? $avisChef->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>
@endif

{{-- Bloc Direction : uniquement si cette étape fait partie du circuit de la demande --}}
@if(in_array('responsable_direction', $circuit))
<div class="section-header">
    Avis du Directeur de service
    ({{ $demande->user->departement->direction->libelle_court ?? '' }})
</div>
<table class="bloc">
    <tr>
        <td class="col-58">
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
        <td class="col-42">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisDirection ? trim(($avisDirection->user->nom ?? '').' '.($avisDirection->user->prenom ?? '')) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisDirection ? $avisDirection->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>
@endif

{{-- Bloc DRH : uniquement si cette étape fait partie du circuit de la demande --}}
@if(in_array('agent_rh', $circuit))
<div class="section-header">Suite réservée par la DRH</div>
<table class="bloc">
    <tr>
        <td class="col-58">
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
        <td class="col-42">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisRH ? trim(($avisRH->user->nom ?? '').' '.($avisRH->user->prenom ?? '')) : '' }}<br><br>
            <strong>Date et signature :</strong><br><br>
            {{ $avisRH ? $avisRH->created_at->format('d/m/Y') : '' }}<br>
        </td>
    </tr>
</table>
@endif

{{-- Bloc décision finale : toujours affiché, mais son contenu dépend
     du dernier acteur RÉEL du circuit (peut être SG, DG, PCA... ou même
     l'Agent RH si le circuit s'arrête là pour une absence courte) --}}
<div class="section-header">
    @if(in_array($etapeFinale, ['sg', 'dg', 'pca']))
        Directeur / Secrétaire Général / Directeur Général
    @else
        Décision finale ({{ $labelsFinale[$etapeFinale] ?? '' }})
    @endif
</div>
<table class="bloc">
    <tr>
        <td class="col-58">
            Décision :
            @if($avisFinale)
                <strong>{{ $avisFinale->avis === 'favorable' ? 'Favorable' : 'Défavorable' }}</strong>
                @if($avisFinale->commentaire)
                    <br>{{ $avisFinale->commentaire }}
                @endif
            @endif
            <br>&nbsp;<br>&nbsp;
        </td>
        <td class="col-42">
            <strong>Nom et prénom(s) :</strong><br>
            {{ $avisFinale ? trim(($avisFinale->user->nom ?? '').' '.($avisFinale->user->prenom ?? '')) : '' }}<br><br>
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

<div class="footer-telechargement">
    Document téléchargé par {{ $telechargePar->nom }} {{ $telechargePar->prenom }}
    le {{ $telechargeLe->format('d/m/Y à H:i') }}
</div>

<div class="footer-adresse">
    03 BP : 7108 Ouagadougou 03 – Tél. : (00226) 25 49 77 99 – 25 49 00 24 – Email : anptic@tic.gov.bf / secretariat@anptic.gov.bf
</div>

</body>
</html>