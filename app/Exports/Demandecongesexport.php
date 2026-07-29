<?php

namespace App\Exports;

use App\Models\DemandeConge;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DemandeCongesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return DemandeConge::with('user.departement.direction')->get();
    }

    public function headings(): array
    {
        return ['N° demande', 'Agent', 'Direction', 'Statut', 'Compilée', 'Date de création'];
    }

    public function map($demande): array
    {
        return [
            $demande->num_demande,
            ($demande->user->nom ?? '') . ' ' . ($demande->user->prenom ?? ''),
            $demande->user->departement->direction->libelle_court ?? '—',
            $demande->statut,
            $demande->estCompilee() ? 'Oui' : 'Non',
            $demande->created_at?->format('d/m/Y'),
        ];
    }
}