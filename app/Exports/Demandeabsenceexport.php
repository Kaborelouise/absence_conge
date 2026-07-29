<?php

namespace App\Exports;

use App\Models\DemandeAbsence;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DemandeAbsencesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DemandeAbsence::with('user.departement.direction')->get();
    }

    public function headings(): array
    {
        return ['N° demande', 'Agent', 'Direction', 'Début', 'Fin', 'Motif', 'Statut', 'Clôturée'];
    }

    public function map($demande): array
    {
        return [
            $demande->num_demande,
            ($demande->user->nom ?? '') . ' ' . ($demande->user->prenom ?? ''),
            $demande->user->departement->direction->libelle_court ?? '—',
            $demande->date_debut,
            $demande->date_fin,
            $demande->motif,
            $demande->statut,
            $demande->estCloturee() ? 'Oui' : 'Non',
        ];
    }
}