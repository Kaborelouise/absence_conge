<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        .entete { text-align: center; margin-bottom: 20px; }
        .entete h4 { margin: 2px 0; }
        .titre { text-align: center; font-weight: bold; text-decoration: underline; margin: 20px 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 4px 6px; font-size: 10px; text-align: left; }
        th { background-color: #e9e9e9; text-align: center; }
        .decide { font-weight: bold; text-decoration: underline; margin-top: 20px; }
        .signature { margin-top: 60px; text-align: right; margin-right: 40px; }
        .fait-a { margin-top: 40px; }
    </style>
</head>
<body>

    <div class="entete">
        <h4>BURKINA FASO</h4>
        <p style="margin:0; font-style: italic;">La Patrie ou la Mort, nous Vaincrons</p>
    </div>

    <div class="titre">
        DÉCISION N° {{ $session->annee }}-{{ str_pad($compilation->id, 5, '0', STR_PAD_LEFT) }}<br>
        ACCORDANT CONGÉ ADMINISTRATIF
    </div>

    <p>
        Un congé administratif de trente (30) jours calendaires à solde entière est accordé
        au titre de la session « {{ $session->libelle }} »
        ({{ $session->date_debut->format('d/m/Y') }} au {{ $session->date_fin->format('d/m/Y') }})
        aux agents dont les noms suivent, conformément au tableau ci-dessous :
    </p>

    <table>
        <thead>
            <tr>
                <th>N° ordre</th>
                <th>Matricule</th>
                <th>Nom et prénom(s)</th>
                <th>Emploi</th>
                <th>Direction</th>
                <th>Lieu(x) de jouissance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($demandes as $index => $demande)
                <tr>
                    <td style="text-align:center;">{{ $index + 1 }}</td>
                    <td>{{ $demande->user->matricule }}</td>
                    <td>{{ $demande->user->nom }} {{ $demande->user->prenom }}</td>
                    <td>{{ $demande->user->poste }}</td>
                    <td>{{ $demande->user->departement->direction->libelle_court ?? '—' }}</td>
                    <td>{{ implode(', ', $demande->lieu_jouissance ?? []) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 15px;">
        <strong>Article 2 :</strong> le fonctionnaire est libre de jouir de son congé dans les
        localités et pays de son choix. Toutefois, l'Administration peut être amenée à remettre
        en cause le choix du fonctionnaire, et dans ce cas, les motifs doivent être dûment
        portés à sa connaissance.
    </p>
    <p>
        <strong>Article 3 :</strong> compte tenu des nécessités de service, la période effective
        de jouissance du congé des intéressés est laissée à l'appréciation de leurs supérieurs
        hiérarchiques.
    </p>

    <p class="fait-a">
        Fait à Ouagadougou, le {{ $compilation->compiled_at->format('d/m/Y') }}
    </p>

    <div class="signature">
        _______________________________
    </div>

</body>
</html>