<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }

        /* En-tête */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        .ministere { text-align: center; font-weight: bold; font-size: 11px; width: 35%; }
        .ministere .separator { margin: 2px 0; }
        .logo-col { text-align: center; width: 30%; }
        .logo-col img { height: 70px; }
        .burkina { text-align: center; font-weight: bold; font-size: 11px; width: 35%; }
        .burkina .devise { font-style: italic; font-weight: normal; margin-top: 2px; }

        .sg-anptic { text-align: center; font-weight: bold; margin-top: 6px; }

        .decision-num { text-align: center; margin-top: 15px; }
        .decision-obj { text-align: center; margin-bottom: 10px; }

        .titre-fonction { text-align: center; font-weight: bold; margin: 15px 0; }

        /* Visas "Vu" */
        .vu-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .vu-table td { border: none; padding: 1px 0; font-size: 10.5px; vertical-align: top; }
        .vu-table td.vu-label { width: 25px; font-weight: bold; }

        .decide { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0 10px 0; font-size: 13px; }

        .article-titre { font-weight: bold; text-decoration: underline; }

        table.agents { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.agents th, table.agents td { border: 1px solid #000; padding: 3px 5px; font-size: 9px; text-align: left; }
        table.agents th { background-color: #e9e9e9; text-align: center; }
        table.agents td.center { text-align: center; }

        .fait-a { margin-top: 30px; }

        .signature { margin-top: 10px; text-align: right; margin-right: 60px; }
        .signature .nom { font-weight: bold; }
        .signature .titre-signataire { font-style: italic; font-size: 10px; }

        .ampliations { margin-top: 40px; font-size: 10px; }
        .ampliations .titre-amp { font-weight: bold; text-decoration: underline; }
        .ampliations ul { margin: 4px 0; padding-left: 20px; }

        .footer-adresse { text-align: center; font-size: 8px; margin-top: 20px; border-top: 1px solid #000; padding-top: 4px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="ministere">
                MINISTERE DE LA TRANSITION DIGITALE,<br>
                DES POSTES ET DES TELECOMMUNICATIONS<br>
                ELECTRONIQUES
                <div class="separator">-=-=-=-=-</div>
                SECRETARIAT GENERAL
                <div class="separator">-=-=-=-=-</div>
                AGENCE NATIONALE DE PROMOTION DES TIC
                <div class="separator">-=-=-=-=-</div>
            </td>
            <td class="logo-col">
                <img src="{{ public_path('images/logo_anptic.png') }}" alt="ANPTIC">
            </td>
            <td class="burkina">
                BURKINA FASO
                <div class="separator">-=-=-=-</div>
                <div class="devise">La Patrie ou la Mort, nous Vaincrons</div>
            </td>
        </tr>
    </table>

    <div class="decision-num">
        Décision n°2026-{{ str_pad($compilation->id, 3, '0', STR_PAD_LEFT) }}/MTDPCE/SG/ANPTIC/DG/SG/DRH
    </div>
    <div class="decision-obj">
        <strong>accordant un congé administratif aux agents de l'Agence Nationale de Promotion des TIC</strong>
    </div>

    <div class="titre-fonction">
        LE DIRECTEUR GENERAL DE L'AGENCE NATIONALE DE PROMOTION DES TECHNOLOGIES<br>
        DE L'INFORMATION ET DE LA COMMUNICATION
    </div>

    <table class="vu-table">
        <tr><td class="vu-label">Vu</td><td>la Constitution ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>la Charte de la Transition du 14 octobre 2022 et son modificatif du 25 mai 2024 ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2024-1565/PRES du 07 décembre 2024 portant nomination du Premier Ministre ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2026-0006/PF/PRIM du 02 janvier 2026 portant remaniement du Gouvernement ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2024-1022/PRES/PM du 12 septembre 2024 portant attributions des membres du Gouvernement ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2025-1545/PF/PRIM du 01 décembre 2025 portant organisation-type des départements ministériels ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2024-1770/PRES/PM/MTDPCE du 31 décembre 2024 portant organisation du Ministère de la Transition Digitale, des Postes et des Communications Electroniques ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>la loi n°010-2013/AN du 30 avril 2013 portant régime des catégories d'établissements publics de l'Etat ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2014-613/PRES/PM/MEF du 24 juillet 2014 portant statut général des Etablissements publics à caractère administratif ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2014-055/PRES/PM/MEF/MDENP/MFTPSS du 07 février 2014 portant création de l'Agence Nationale de Promotion des Technologies de l'Information et de la Communication (ANPTIC) ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2015-1652/PRES-TRANS/PM/MDENP/MEF du 28 décembre 2015 portant approbation des statuts particuliers de l'Agence Nationale de Promotion des Technologies de l'Information et de la Communication (ANPTIC) ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>la loi n°033-2008/AN du 22 mai 2008 portant régime juridique applicable aux emplois et aux agents des établissements publics de l'Etat ;</td></tr>
        <tr><td class="vu-label">Vu</td><td>le décret n°2023-0297/PRES-TRANS/PM/MTDPCE du 24 mars 2023 portant nomination d'un Directeur général ;</td></tr>
    </table>

    <div class="decide">DECIDE</div>

    <p>
        <span class="article-titre">Article 1 :</span> Un congé administratif de trente (30) jours calendaires à solde entière
        est accordé au titre de l'année 2026 aux agents de l'Agence Nationale de Promotion des TIC dont les noms
        suivent, conformément au tableau ci-dessous :
    </p>

    <table class="agents">
        <thead>
            <tr>
                <th>N°</th>
                <th>Nom</th>
                <th>Prénom(s)</th>
                <th>Matricule</th>
                <th>Emploi</th>
                <th>Direction</th>
                <th>Période de<br>travail</th>
                <th>Date<br>d'effet</th>
                <th>Lieu(x) de<br>jouissance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demandes as $index => $demande)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $demande->user->nom }}</td>
                    <td>{{ $demande->user->prenom }}</td>
                    <td>{{ $demande->user->matricule }}</td>
                    <td>{{ $demande->user->poste }}</td>
                    <td>{{ $demande->user->departement->direction->libelle_court ?? '—' }}</td>

                    <td class="center">{{ $date_debut }} au {{ $date_fin }}</td>
                    <td class="center">{{ $date_effet }}</td>
                    <td>{{ implode(', ', $demande->lieu_jouissance ?? []) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 12px;">
        <span class="article-titre">Article 2 :</span> Compte tenu des nécessités de service, la date effective
        de départ en congé des intéressés est laissée à l'appréciation de leur supérieur hiérarchique.
    </p>
    <p>
        <span class="article-titre">Article 3 :</span> La présente décision sera enregistrée, publiée
        et communiquée partout où besoin sera.
    </p>

    <p class="fait-a">
        Fait à Ouagadougou, le {{ $compilation->compiled_at->format('d/m/Y') }}
    </p>

    <div class="signature">
        <div class="nom">Oumarou SANOU</div>
        <div class="titre-signataire">Chevalier de l'Ordre du Mérite de l'Économie et des Finances</div>
    </div>

    <div class="ampliations">
        <span class="titre-amp">Ampliations :</span>
        <ul>
            <li>Chrono</li>
            <li>DFC</li>
            <li>DRH</li>
            <li>DI</li>
            <li>Intéressés</li>
        </ul>
    </div>

    <div class="footer-adresse">
        03 BP 7108 Ouagadougou 03 – Tél (00226) 25 49 77 99 – 25 49 00 24 –
        Email : anptic@bf.gov.bf / secretariat@anptic.gov.bf
    </div>

</body>
</html>