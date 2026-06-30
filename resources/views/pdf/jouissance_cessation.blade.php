<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #000; margin: 0; padding: 30px 40px; }
        .entete { width: 100%; margin-bottom: 10px; }
        .entete td { vertical-align: top; font-size: 11px; line-height: 1.7; }
        .entete-gauche { width: 55%; font-weight: bold; font-size: 11px; text-transform: uppercase; line-height: 1.6; }
        .entete-droite { width: 45%; text-align: right; font-size: 11px; }
        .pays { font-weight: bold; font-size: 12px; }
        .devise { font-style: italic; font-size: 11px; margin-bottom: 6px; }
        .reference { font-size: 11px; margin: 8px 0 4px; }
        .titre { text-align: center; font-size: 16px; font-weight: bold; font-style: italic; text-decoration: underline; margin: 20px 0 24px; }
        .corps { line-height: 2; text-align: justify; margin-bottom: 10px; }
        .signature-bloc { margin-top: 40px; text-align: right; font-style: italic; }
        .ampliations { margin-top: 30px; font-size: 10px; }
        .pied { margin-top: 30px; border-top: 1px solid #000; font-size: 10px; text-align: center; padding-top: 4px; }
    </style>
</head>
<body>
<table class="entete" cellpadding="0" cellspacing="0">
    <tr>
        <td class="entete-gauche">
            MINISTERE DE LA TRANSITION DIGITALE,<br>
            DES POSTES ET DES COMMUNICATIONS<br>
            ELECTRONIQUES<br>-=-=-=-=-=-<br>
            SECRETARIAT GENERAL<br>-=-=-=-=-=-<br>
            AGENCE NATIONALE DE PROMOTION DES TIC<br>-=-=-=-=-=-
        </td>
        <td class="entete-droite">
            <div class="pays">BURKINA FASO</div>
            <div class="devise">La Patrie ou la Mort, nous Vaincrons</div><br>
            Ouagadougou, le {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('D MMMM YYYY') }}
        </td>
    </tr>
</table>
<div class="reference">N°{{ date('Y') }}______/MTDPCE/SG/ANPTIC/DG/SG</div>
<div class="titre">Certificat de cessation de service</div>
<div class="corps">
    <p>
        Je soussigné, <strong>Secrétaire Général</strong>, certifie que
        <strong>{{ strtoupper($demande->user->nom) }} {{ $demande->user->prenom }}</strong>
        matricule <strong>{{ $demande->user->matricule }}</strong>,
        {{ $demande->user->poste }},
        bénéficiaire d'un congé administratif de {{ date('Y') }}
        pour la période du
        <strong>{{ \Carbon\Carbon::parse($demande->date_debut)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>
        au
        <strong>{{ \Carbon\Carbon::parse($demande->date_fin)->locale('fr')->isoFormat('dddd D MMMM YYYY') }} inclus</strong>,
        soit <strong>{{ $demande->nombre_jour }} jours</strong>,
        a cessé service le
        <strong>{{ \Carbon\Carbon::parse($demande->date_debut)->subDay()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>.
    </p>
    <p>
        L'intéressé(e) reprendra service le
        <strong>{{ \Carbon\Carbon::parse($demande->date_fin)->addDay()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</strong>.
    </p>
    <p>En foi de quoi, le présent certificat est établi pour servir et valoir ce que de droit.</p>
</div>
<div class="signature-bloc">
    Pour le Secrétaire Général,<br>
    Le Directeur des Ressources Humaines<br><br><br><br>
    ________________________________
</div>
<div class="ampliations">
    <strong>Ampliations :</strong>
    <ul style="margin:4px 0; padding-left:20px;">
        <li>Secrét. DG</li><li>Secrét. SG</li><li>Toutes directions</li>
        <li>Chrono (1)</li><li>Dossier individuel (1)</li>
    </ul>
</div>
<div class="pied">
    03 BP : 7108 Ouagadougou 03 – Tél. : (00226) 25 49 77 99 – 25 49 00 24 – Email : anptic@tic.gov.bf / secretariat@anptic.gov.bf
</div>
</body>
</html>