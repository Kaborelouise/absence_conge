@php
    $owner = $demande->user;
    $interimaire = $demande->interimaire;

    $fonctionOwner = $owner->fonctionAffichee();
    $fonctionInterim = $interimaire?->fonctionAffichee();

    $civiliteOwner = $owner->genre === 'F' ? 'Madame' : ($owner->genre === 'M' ? 'Monsieur' : '');
    $civiliteInterim = $interimaire
        ? ($interimaire->genre === 'F' ? 'Madame' : ($interimaire->genre === 'M' ? 'Monsieur' : ''))
        : '';

    $posteLower = mb_strtolower($fonctionOwner ?? '');
    $commenceParVoyelle = $posteLower !== '' && in_array(mb_substr($posteLower, 0, 1), ['a','e','i','o','u','h']);

    if ($commenceParVoyelle) {
        $articleDe     = "de l’";
        $articleSimple = "l’";
    } elseif ($owner->genre === 'F') {
        $articleDe     = 'de la ';
        $articleSimple = 'la ';
    } else {
        $articleDe     = 'du ';
        $articleSimple = 'le ';
    }

    $absentAccord = $owner->genre === 'F' ? 'absente' : ($owner->genre === 'M' ? 'absent' : 'absent(e)');

    $ownerNomComplet = trim(($civiliteOwner ? $civiliteOwner.' ' : '') . strtoupper($owner->nom) . ' ' . $owner->prenom);
    $interimNomComplet = $interimaire
        ? trim(($civiliteInterim ? $civiliteInterim.' ' : '') . strtoupper($interimaire->nom) . ' ' . $interimaire->prenom)
        : '';
    $interimPosteSuffixe = ($interimaire && $fonctionInterim) ? ', '.$fonctionInterim : '';

    $logoPath = public_path('images/logo_anptic2.png');
    $logoData = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

$fonctionSignataire = $signataire->fonctionAffichee();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12.5px;
            color: #000;
            margin: 0;
            padding: 0 35px;
        }
        table.entete {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.entete td { vertical-align: top; }

        .col-gauche {
            width: 42%;
            text-align: center;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.5;
        }
        .col-centre {
            width: 16%;
            text-align: center;
            vertical-align: top;
            padding-top: 5px;
        }
        .col-centre img {
            width: 55px;
        }
        .col-droite {
            width: 42%;
            text-align: center;
            vertical-align: top;
            font-size: 11.5px;
            font-weight: bold;
        }
        .col-droite span {
            font-weight: normal;
            font-size: 10.5px;
        }

        table.ref-date {
            width: 100%;
            margin-top: 30px;
        }
        table.ref-date td {
            font-size: 12px;
        }
        table.ref-date .td-num { text-align: left; }
        table.ref-date .td-date { text-align: right; }

        .titre-wrapper {
            text-align: center;
            margin: 40px 0 35px;
        }
        .titre {
            display: inline-block;
            border: 1px solid #000;
            padding: 8px 22px;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .objet { margin-bottom: 20px; }

        p {
            text-align: justify;
            line-height: 1.6;
            margin: 0 0 16px;
        }

        .signature {
            margin-top: 70px;
            text-align: right;
            padding-right: 30px;
        }
        .signature .fonction { margin-bottom: 55px; }
        .signature .nom {
            text-decoration: underline;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: -40px;
            left: 35px;
            right: 35px;
            text-align: center;
            font-size: 9px;
            color: #444;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <table class="entete">
        <tr>
            <td class="col-gauche">
                MINISTERE DE LA TRANSITION DIGITALE<br>
                DES POSTES ET DES COMMUNICATIONS<br>
                ELECTRONIQUES<br>
                -=-=-=-<br>
                SECRETARIAT GENERAL<br>
                -=-=-=-<br>
                AGENCE NATIONALE DE<br>
                PROMOTION DES TIC<br>
                -=-=-=-
            </td>
            <td class="col-centre">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="ANPTIC">
                @else
                    <strong style="font-size:14px;">ANPTIC</strong>
                @endif
            </td>
            <td class="col-droite">
                BURKINA FASO<br>
                <span>La Patrie ou La Mort, Nous Vaincrons</span>
            </td>
        </tr>
    </table>

    <table class="ref-date">
        <tr>
            <td class="td-num">{{ $demande->numero_interim }}</td>
            <td class="td-date">Ouagadougou, le {{ now()->format('d') }} {{ now()->locale('fr')->isoFormat('MMMM') }} {{ now()->format('Y') }}</td>
        </tr>
    </table>

    <div class="titre-wrapper">
        <div class="titre">NOTE DE SERVICE</div>
    </div>

    <p class="objet">
        <u>Objet</u> : intérim {{ $articleDe }}{{ $fonctionOwner }}
    </p>

    <p>
        Durant l'absence de {{ $ownerNomComplet }},
        @if($fonctionOwner){{ $fonctionOwner }},@endif
        en autorisation d'absence,
        l'intérim sera assuré, du <strong>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}
        au {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }} inclus</strong>,
        par {{ $interimNomComplet }}{{ $interimPosteSuffixe }}.
    </p>

    <p>Par conséquent, toute correspondance soumise à sa signature portera la mention suivante :</p>

    <p>
        Pour {{ $articleSimple }}{{ $fonctionOwner }} {{ $absentAccord }},
        {{ $civiliteInterim === 'Madame' ? "l'agente" : "l'agent" }} chargé{{ $civiliteInterim === 'Madame' ? 'e' : '' }} de l'intérim,<br>
        <strong>{{ $interimNomComplet }}</strong>
    </p>

    <div class="signature">
        <div class="fonction">{{ $fonctionSignataire }}</div>
        <div class="nom">{{ $signataire->nom }} {{ $signataire->prenom }}</div>
    </div>

    <div class="footer">
        ANPTIC — 03 BP : 7108 Ouagadougou 03 – Tél. : (00226) 25 49 77 99 – 25 49 00 24 – Email : anptic@tic.gov.bf
    </div>

</body>
</html>